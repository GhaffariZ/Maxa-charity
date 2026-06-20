<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns');
header('Content-Type: application/json; charset=utf-8');
// استفاده از همان مسیری که در فایل news-save جواب داده است
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

try {
    $pdo->exec("SET NAMES utf8mb4");

    // بررسی فیلدهای ارسالی از campaign-create.php
    if (empty($_POST['title']) || empty($_POST['target_amount'])) {
        throw new Exception("پر کردن فیلدهای ستاره‌دار الزامی است.");
    }

    $title = $_POST['title'];
    $description = isset($_POST['description']) ? strip_tags($_POST['description'], "<img><p><br><b><strong><i><u><a><h1><h2><h3>") : '';
    $target_amount = (float)$_POST['target_amount'];

    // دسته‌بندی کمپین (باید با گزینه‌های فیلتر در صفحه کاربر یکی باشد)
    $allowed_categories = ['food', 'drug', 'education'];
    $category = isset($_POST['category']) ? $_POST['category'] : 'food';
    if (!in_array($category, $allowed_categories, true)) {
        $category = 'food';
    }

    // تولید کد یکتا مشابه الگوی قبلی پروژه شما
    $campaign_code = 'CAMP-' . date('Ymd') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

    // حذف status از کوئری چون در دیتابیس ندارید
    // دقت کنید: اگر فیلد collected_amount در دیتابیس هست، آن را 0 قرار می‌دهیم
    // ایزولاسیون: کمپین در شعبه‌ی فعال ساخته می‌شود
    $__branch = dash_active_branch_id();
    $sql = "INSERT INTO campaigns (campaign_code, title, description, category, target_amount, collected_amount, branch_id) VALUES (?, ?, ?, ?, ?, 0, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$campaign_code, $title, $description, $category, $target_amount, $__branch]);
    $id = $pdo->lastInsertId();

    // مدیریت آپلود تصویر شاخص
    if (!empty($_FILES['featured_image']['name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/campaigns/$campaign_code";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
        $filename = "thumb.$ext";
        $destination = "$uploadDir/$filename";

        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $destination)) {
            $image_path = "/uploads/campaigns/$campaign_code/$filename";
            // ستون ذخیره عکس را مطابق فایل status شما image_url در نظر گرفتم
            $pdo->prepare("UPDATE campaigns SET image_url = ? WHERE id = ?")->execute([$image_path, $id]);
        }
    }

    echo json_encode(["status" => "success", "message" => "کمپین با موفقیت ایجاد شد", "id" => $id], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // نمایش خطای دقیق برای اینکه بفهمیم مشکل از کجاست
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}