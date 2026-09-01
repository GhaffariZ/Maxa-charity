<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');
header('Content-Type: application/json');

$dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/editor/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

// SECURITY: Validate upload error
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["error" => "Upload failed."]);
    exit;
}

$file = $_FILES['image'];

// SECURITY: Enforce file size limit (2 MB for inline images)
if ($file['size'] > 2 * 1024 * 1024 || $file['size'] === 0) {
    http_response_code(400);
    echo json_encode(["error" => "File size must be between 1 byte and 2 MB."]);
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
