<?php
declare(strict_types=1);
require_once __DIR__ . '/_guard.php';
dash_require('hero');

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../config/database.php";

/**
 * تبدیل ورودیِ تاریخِ فرم به فرمت دیتابیس.
 * - datetime-local (مثل 2026-06-28T13:51) → 2026-06-28 13:51:00
 * - date (مثل 2026-06-28) همان‌طور می‌ماند
 * - خالی → null
 */
function hero_norm_date(?string $raw): ?string {
    $raw = trim((string)$raw);
    if ($raw === '') return null;
    $raw = str_replace('T', ' ', $raw);
    if (strlen($raw) === 16) $raw .= ':00';   // افزودن ثانیه به datetime-local
    return $raw;
}

/**
 * آپلودِ یک تصویرِ هیرو از یک ساختارِ فایلِ تکی (name/tmp_name/size/error).
 * در صورت موفقیت مسیرِ قابلِ ذخیره در دیتابیس را برمی‌گرداند، وگرنه null
 * (وقتی فایلی انتخاب نشده). در خطاهای واقعی Exception پرتاب می‌کند.
 */
function hero_save_image(array $file, string $uploadDir): ?string {
    // فایلی انتخاب نشده
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new Exception('خطا در آپلود تصویر.');
    }
    // محدودیت حجم (5MB)
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new Exception('حجم تصویر نباید بیشتر از 5 مگابایت باشد.');
    }
    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed, true)) {
        throw new Exception('فرمت تصویر مجاز نیست. فقط jpg, jpeg, png, webp, gif');
    }
    $newName = 'hero_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
    $dest = $uploadDir . $newName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new Exception('انتقال تصویر به پوشه انجام نشد.');
    }
    return '/uploads/hero/' . $newName;
}

try {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new Exception('اتصال به دیتابیس برقرار نیست.');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('درخواست نامعتبر است.');
    }

    // محافظت در برابر CSRF (هم‌سان با endpointهای خبر)
    csrf_check();

    // پوشه‌ی آپلود
    $uploadDir = __DIR__ . '/../uploads/hero/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new Exception('ساخت پوشه آپلود انجام نشد.');
        }
    }
    if (!is_writable($uploadDir)) {
        throw new Exception('پوشه uploads/hero قابل نوشتن نیست.');
    }

    $__branch = dash_active_branch_id();

    /* ====================================================================
     * حالت آرایه‌ای (صفحه‌ی «مدیریت هیروها»): ویرایش/افزودنِ گروهیِ اسلایدها
     * ================================================================== */
    if (isset($_POST['title']) && is_array($_POST['title'])) {

        $ids          = $_POST['id']           ?? [];
        $titles       = $_POST['title']        ?? [];
        $descriptions = $_POST['description']  ?? [];
        $buttonLinks  = $_POST['button_link']  ?? [];
        $publishDates = $_POST['publish_date'] ?? [];
        $sortOrders   = $_POST['sort_order']   ?? [];

        $count = count($titles);

        $insStmt = $pdo->prepare("
            INSERT INTO hero_slides
                (title, description, image, button_link, publish_date, status, sort_order, branch_id)
            VALUES
                (:title, :description, :image, :button_link, :publish_date, 1, :sort_order, :branch_id)
        ");
        // IDOR: فقط اسلایدِ متعلق به همین شعبه به‌روزرسانی می‌شود
        $ownStmt = $pdo->prepare("SELECT id FROM hero_slides WHERE id = ? AND branch_id = ?");

        for ($i = 0; $i < $count; $i++) {
            $title = trim((string)($titles[$i] ?? ''));
            $desc  = trim((string)($descriptions[$i] ?? ''));
            $link  = trim((string)($buttonLinks[$i] ?? ''));
            $pdate = hero_norm_date($publishDates[$i] ?? null);
            $sort  = (int)($sortOrders[$i] ?? ($i + 1));
            $id    = (int)($ids[$i] ?? 0);

            // ردیفِ کاملاً خالی (بدون عنوان، بدون id، بدون تصویر) را نادیده بگیر
            $thisFile = isset($_FILES['image']) ? [
                'name'     => $_FILES['image']['name'][$i]     ?? '',
                'type'     => $_FILES['image']['type'][$i]     ?? '',
                'tmp_name' => $_FILES['image']['tmp_name'][$i] ?? '',
                'error'    => $_FILES['image']['error'][$i]    ?? UPLOAD_ERR_NO_FILE,
                'size'     => $_FILES['image']['size'][$i]     ?? 0,
            ] : ['name' => '', 'error' => UPLOAD_ERR_NO_FILE];

            $hasNewImage = !empty($thisFile['name']);

            if ($id <= 0 && $title === '' && !$hasNewImage) {
                continue;
            }

            $newImagePath = $hasNewImage ? hero_save_image($thisFile, $uploadDir) : null;

            if ($id > 0) {
                // ویرایشِ اسلایدِ موجود — با قیدِ شعبه (IDOR)
                $ownStmt->execute([$id, $__branch]);
                if (!$ownStmt->fetch()) {
                    continue; // متعلق به این شعبه نیست؛ بی‌صدا رد شو
                }
                if ($newImagePath !== null) {
                    $sql = "UPDATE hero_slides
                               SET title = ?, description = ?, button_link = ?, publish_date = ?, sort_order = ?, image = ?
                             WHERE id = ? AND branch_id = ?";
                    $pdo->prepare($sql)->execute([$title, $desc, $link, $pdate, $sort, $newImagePath, $id, $__branch]);
                } else {
                    $sql = "UPDATE hero_slides
                               SET title = ?, description = ?, button_link = ?, publish_date = ?, sort_order = ?
                             WHERE id = ? AND branch_id = ?";
                    $pdo->prepare($sql)->execute([$title, $desc, $link, $pdate, $sort, $id, $__branch]);
                }
            } else {
                // اسلایدِ جدید
                $insStmt->execute([
                    ':title'        => $title,
                    ':description'  => $desc,
                    ':image'        => $newImagePath,
                    ':button_link'  => $link,
                    ':publish_date' => $pdate,
                    ':sort_order'   => $sort,
                    ':branch_id'    => $__branch,
                ]);
            }
        }

        echo json_encode([
            'status'  => 'success',
            'message' => 'تغییرات هیروها با موفقیت ذخیره شد ✅'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ====================================================================
     * حالت تکی (فرمِ «ساخت هیرو جدید»): یک اسلاید جدید
     * ================================================================== */
    $title        = trim($_POST['title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $button_link  = trim($_POST['link'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $publish_date = hero_norm_date($_POST['publish_date'] ?? '');

    if ($title === '') {
        throw new Exception('عنوان هیرو الزامی است.');
    }

    $dbImagePath = isset($_FILES['image'])
        ? hero_save_image($_FILES['image'], $uploadDir)
        : null;

    $stmt = $pdo->prepare("
        INSERT INTO hero_slides
            (title, description, image, button_link, category, publish_date, status, sort_order, branch_id)
        VALUES
            (:title, :description, :image, :button_link, :category, :publish_date, 1, 0, :branch_id)
    ");
    $stmt->execute([
        ':title'        => $title,
        ':description'  => $description,
        ':image'        => $dbImagePath,
        ':button_link'  => $button_link,
        ':category'     => $category,
        ':publish_date' => $publish_date,
        ':branch_id'    => $__branch,
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
