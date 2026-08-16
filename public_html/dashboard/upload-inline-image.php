<?php
require_once __DIR__ . '/_guard.php';
dash_require('news');

header('Content-Type: application/json; charset=utf-8');

try {
    // پذیرش هم کلید file (استاندارد TinyMCE) و هم کلید image (سفارشی)
    $file = $_FILES['file'] ?? $_FILES['image'] ?? null;

    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('هیچ فایلی آپلود نشده است یا خطایی رخ داده است.');
    }

    // اعتبارسنجی پسوند فایل
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_extensions)) {
        throw new Exception('فرمت فایل مجاز نیست. پسوندهای مجاز: JPG, PNG, WEBP, GIF, SVG');
    }

    // اعتبارسنجی حجم (حداکثر ۱۰ مگابایت)
    if ($file['size'] > 10 * 1024 * 1024) {
        throw new Exception('حجم تصویر بیش از حد مجاز است (حداکثر ۱۰ مگابایت).');
    }

    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/editor/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target_path = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        throw new Exception('ذخیره‌سازی فایل در سرور با خطا مواجه شد.');
    }

    $url = '/uploads/editor/' . $filename;

    // خروجی موکد استاندارد TinyMCE (location) + پشتیبانی از url
    echo json_encode([
        'location' => $url,
        'url'      => $url,
        'success'  => true
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error'   => ['message' => $e->getMessage()],
        'message' => $e->getMessage(),
        'success' => false
    ]);
}
