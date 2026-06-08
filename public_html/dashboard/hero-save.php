<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// فقط برای محیط توسعه (در هاست نهایی خاموش کن)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . "/../../config/database.php";
try {

    // بررسی اتصال دیتابیس
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('اتصال به دیتابیس برقرار نیست.');
    }

    // اطمینان از UTF8 برای فارسی
    $pdo->exec("SET NAMES utf8mb4");

    // فقط POST مجاز است
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('درخواست نامعتبر است.');
    }

    // گرفتن داده‌ها
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $button_link  = trim($_POST['link'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $publish_date = trim($_POST['publish_date'] ?? '');

    // اعتبارسنجی
    if ($title === '') {
        throw new Exception('عنوان هیرو الزامی است.');
    }

    // تبدیل datetime-local به datetime دیتابیس
    if (!empty($publish_date)) {
        $publish_date = str_replace('T', ' ', $publish_date);
        if (strlen($publish_date) === 16) {
            $publish_date .= ':00';
        }
    } else {
        $publish_date = null;
    }

    // مسیر آپلود
    $uploadDir = __DIR__ . '/../uploads/hero/';
    $dbImagePath = null;

    // اگر پوشه نبود بساز
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new Exception('ساخت پوشه آپلود انجام نشد.');
        }
    }

    // بررسی قابل نوشتن بودن پوشه
    if (!is_writable($uploadDir)) {
        throw new Exception('پوشه uploads/hero قابل نوشتن نیست.');
    }

    // آپلود تصویر
    if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName    = $_FILES['image']['name'];
        $fileSize    = $_FILES['image']['size'];
        $fileError   = $_FILES['image']['error'];

        if ($fileError !== UPLOAD_ERR_OK) {
            throw new Exception('خطا در آپلود تصویر.');
        }

        // محدودیت حجم (5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            throw new Exception('حجم تصویر نباید بیشتر از 5 مگابایت باشد.');
        }

        // بررسی پسوند
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($fileExt, $allowedExt)) {
            throw new Exception('فرمت تصویر مجاز نیست. فقط jpg, jpeg, png, webp, gif');
        }

        // ساخت نام یکتا
        $newFileName = 'hero_' . time() . '_' . random_int(1000,9999) . '.' . $fileExt;

        $destination = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmpPath, $destination)) {
            throw new Exception('انتقال تصویر به پوشه انجام نشد.');
        }

        // مسیر ذخیره در دیتابیس
        $dbImagePath = '/uploads/hero/' . $newFileName;
    }

    // ذخیره در دیتابیس
    $stmt = $pdo->prepare("
        INSERT INTO hero_slides 
        (title, description, image, button_link, category, publish_date, status, sort_order)
        VALUES 
        (:title, :description, :image, :button_link, :category, :publish_date, 1, 0)
    ");

    $stmt->execute([
        ':title'        => $title,
        ':description'  => $description,
        ':image'        => $dbImagePath,
        ':button_link'  => $button_link,
        ':category'     => $category,
        ':publish_date' => $publish_date
    ]);

    echo json_encode([
        'status'  => 'success',
        'message' => 'هیرو با موفقیت ذخیره شد ✅'
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {

    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}