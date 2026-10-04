<?php
require_once __DIR__ . '/_guard.php';
if (!dash_can('news') && !dash_can('pages') && !dash_can('events')) {
    http_response_code(403);
    echo json_encode(['error' => '۴۰۳ | دسترسی غیرمجاز'], JSON_UNESCAPED_UNICODE);
    exit;
}
header('Content-Type: application/json; charset=utf-8');

$dir = __DIR__ . '/../uploads/editor/';
if ((!is_dir($dir) && !@mkdir($dir, 0755, true)) || !is_writable($dir)) {
    http_response_code(500);
    echo json_encode(['error' => 'پوشه آپلود تصاویر خبر قابل نوشتن نیست. دسترسی پوشه public_html/uploads/editor را بررسی کنید.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// SECURITY: Validate upload error
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $uploadError = $_FILES['image']['error'] ?? null;
    $message = match ($uploadError) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'حجم تصویر از محدودیت PHP هاست بیشتر است. upload_max_filesize و post_max_size را بررسی کنید.',
        UPLOAD_ERR_PARTIAL => 'آپلود تصویر ناقص انجام شد. دوباره تلاش کنید.',
        UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'هاست نتوانست فایل موقت تصویر را ذخیره کند. فضای دیسک و دسترسی پوشه موقت را بررسی کنید.',
        default => 'فایل تصویر به سرور نرسید. نشست مدیریت یا محدودیت حجم درخواست را بررسی کنید.',
    };
    http_response_code(400);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$file = $_FILES['image'];

// Align inline/gallery images with the dashboard's 8 MB upload limit.
if ($file['size'] > 8 * 1024 * 1024 || $file['size'] === 0) {
    http_response_code(400);
    echo json_encode(["error" => "حجم هر تصویر باید بین ۱ بایت و ۸ مگابایت باشد."], JSON_UNESCAPED_UNICODE);
    exit;
}

// SECURITY: Validate extension against blocklist
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$forbidden = [
    'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps',
    'phtml', 'pht', 'phar', 'php-s', 'php2', 'inc',
    'cgi', 'pl', 'py', 'rb', 'jsp', 'jspx', 'asp', 'aspx',
    'htaccess', 'htpasswd', 'ini', 'env', 'config',
    'sh', 'bash', 'exe', 'bat', 'cmd',
];
if (in_array($ext, $forbidden, true)) {
    http_response_code(400);
    echo json_encode(["error" => "File extension is not permitted."]);
    exit;
}

// SECURITY: Validate MIME type using finfo (not client-provided type)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if ($mimeType === false || !in_array($mimeType, $allowedMimes, true)) {
    http_response_code(400);
    echo json_encode(["error" => "Only JPEG, PNG, GIF, and WebP images are allowed."]);
    exit;
}

if (!function_exists('imagecreatefromstring')) {
    http_response_code(500);
    echo json_encode(['error' => 'افزونه GD در PHP هاست فعال نیست.'], JSON_UNESCAPED_UNICODE);
    exit;
}

// SECURITY: Validate with GD that it is a real image
$image = @imagecreatefromstring(file_get_contents($file['tmp_name']));
if (!$image) {
    http_response_code(400);
    echo json_encode(["error" => "Uploaded file is not a valid image."]);
    exit;
}

// SECURITY: Re-encode through GD to strip embedded payloads and malicious metadata
$safeExtMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$safeExt = $safeExtMap[$mimeType] ?? 'jpg';
$name = bin2hex(random_bytes(16)) . '.' . $safeExt;
$target = $dir . $name;

switch ($mimeType) {
    case 'image/jpeg': $result = @imagejpeg($image, $target, 85); break;
    case 'image/png':  $result = @imagepng($image, $target, 6); break;
    case 'image/gif':  $result = @imagegif($image, $target); break;
    case 'image/webp': $result = @imagewebp($image, $target, 85); break;
    default: $result = false;
}
imagedestroy($image);

if (!$result || !is_file($target) || filesize($target) === 0) {
    @unlink($target);
    http_response_code(500);
    echo json_encode(["error" => "Failed to process image."]);
    exit;
}

echo json_encode([
    "url" => "/uploads/editor/" . $name
]);
