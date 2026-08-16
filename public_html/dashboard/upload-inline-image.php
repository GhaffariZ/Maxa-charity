<?php
/**
 * ============================================================================
 * Enterprise Newsroom - Inline Media & Gallery Upload Handler
 * ============================================================================
 * Handles async file uploads from TinyMCE 7, rich media block inserters,
 * and gallery builders with security validation, MIME checks, and directory isolation.
 */

require_once __DIR__ . '/_guard.php';
dash_require('news');

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('متد درخواست نامعتبر است.');
    }

    // Accept standard TinyMCE key 'file', or custom key 'image' / 'media'
    $file = $_FILES['file'] ?? $_FILES['image'] ?? $_FILES['media'] ?? null;
    $base64_data = $_POST['base64_image'] ?? null;
    $news_code = isset($_POST['news_code']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', trim($_POST['news_code'])) : '';

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $allowed_mimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml'
    ];

    // Determine upload target folder
    if (!empty($news_code)) {
        $sub_dir = '/uploads/news/' . $news_code . '/';
    } else {
        $sub_dir = '/uploads/editor/' . date('Ym') . '/';
    }

    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . $sub_dir;
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0775, true) && !is_dir($upload_dir)) {
            throw new Exception('امکان ایجاد پوشه ذخیره‌سازی در سرور وجود ندارد.');
        }
    }

    $saved_filename = '';

    // Case 1: Base64 Upload
    if (!empty($base64_data)) {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_data, $type)) {
            $data = substr($base64_data, strpos($base64_data, ',') + 1);
            $ext = strtolower($type[1]);
            if ($ext === 'jpeg') $ext = 'jpg';

            if (!in_array($ext, $allowed_extensions, true)) {
                throw new Exception('فرمت تصویر نامعتبر است.');
            }

            $decoded = base64_decode($data);
            if ($decoded === false) {
                throw new Exception('داده‌های تصویر ارسالی مخدوش است.');
            }

            if (strlen($decoded) > 20 * 1024 * 1024) {
                throw new Exception('حجم تصویر بیشتر از حد مجاز (۲۰ مگابایت) است.');
            }

            $saved_filename = 'media_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target_file = $upload_dir . $saved_filename;

            if (file_put_contents($target_file, $decoded) === false) {
                throw new Exception('ذخیره فایل در سرور با خطا مواجه شد.');
            }
        } else {
            throw new Exception('فرمت داده‌های Base64 نامعتبر است.');
        }
    }
    // Case 2: Standard Multipart File Upload
    elseif ($file !== null) {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            $err_code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
            switch ($err_code) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $max = ini_get('upload_max_filesize');
                    throw new Exception("حجم فایل ارسالی بیشتر از حد مجاز سرور ($max) است.");
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception('آپلود فایل به صورت ناقص انجام شد.');
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception('هیچ فایلی برای آپلود انتخاب نشده است.');
                default:
                    throw new Exception('خطا در آپلود فایل رخ داده است.');
            }
        }

        // Validate File Size (Max 20MB)
        if ($file['size'] > 20 * 1024 * 1024) {
            throw new Exception('حجم فایل بیش از حد مجاز (حداکثر ۲۰ مگابایت) است.');
        }

        // Validate Extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_extensions, true)) {
            throw new Exception('فرمت فایل مجاز نیست. فرمت‌های مجاز: JPG, PNG, WEBP, GIF, SVG');
        }

        // Validate MIME type with finfo if available
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed_mimes, true) && $ext !== 'svg') {
                throw new Exception('نوع فایل با پسوند آن مطابقت ندارد.');
            }
        }

        $saved_filename = 'media_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target_file = $upload_dir . $saved_filename;

        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            throw new Exception('انتقال فایل به پوشه مقصد با خطا مواجه شد.');
        }
    } else {
        throw new Exception('هیچ فایلی دریافت نشد.');
    }

    $final_url = $sub_dir . $saved_filename;

    // Return standard TinyMCE format and extra metadata
    http_response_code(200);
    echo json_encode([
        'location' => $final_url,
        'url'      => $final_url,
        'filename' => $saved_filename,
        'success'  => true,
        'status'   => 'success'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'error'    => [
            'message' => $e->getMessage()
        ],
        'message'  => $e->getMessage(),
        'success'  => false,
        'status'   => 'error'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
