<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: component-create.php");
    exit;
}

if (!isset($_POST['component_name'], $_POST['component_tag'])) {
    header("Location: component-create.php?error=missing_data");
    exit;
}

$component_name = trim($_POST['component_name']);

// SECURITY: Validate component name — only alphanumeric, hyphens, underscores
if ($component_name === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $component_name)) {
    die("Invalid component name. Only letters, numbers, hyphens and underscores are allowed.");
}

$tag = trim($_POST['component_tag']);

$path = __DIR__ . "/components/" . $component_name . "/";

if (!is_dir($path)) {
    mkdir($path, 0755, true);
}

/* ── SECURITY: Store component content as JSON data, NOT as executable PHP ── */
if (isset($_POST['component_code'])) {
    $code = $_POST['component_code'];

    $componentData = [
        'version' => 2,
        'content' => $code,
        'tag'     => $tag,
    ];

    file_put_contents(
        $path . "data.json",
        json_encode($componentData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    );

    // SECURITY: Also update meta.json for backward compatibility
    file_put_contents(
        $path . "meta.json",
        json_encode(['tag' => $tag], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/* ── SECURITY: meta.json always gets the tag update ───────────────────────── */
if (!isset($_POST['component_code'])) {
    file_put_contents(
        $path . "meta.json",
        json_encode(['tag' => $tag], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/* ── SECURITY: Validate component name for path traversal (images section) ── */
$images_path = $path . "images/";

if (!is_dir($images_path)) {
    mkdir($images_path, 0755, true);
}

/* ── SECURITY: Safe image deletion ────────────────────────────────────────── */
if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {
    foreach ($_POST['delete_images'] as $image) {
        // SECURITY: Use basename() to strip any path components
        $image = basename($image);
        // SECURITY: Only allow safe image extensions for deletion targets
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) continue;
        // SECURITY: Validate the filename contains only safe characters
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $image)) continue;

        $image_file = $images_path . $image;
        if (is_file($image_file)) {
            unlink($image_file);
        }
    }
}

/* ── SECURITY: Secure image upload ────────────────────────────────────────── */
if (
    isset($_FILES['new_component_images']) &&
    isset($_FILES['new_component_images']['name']) &&
    is_array($_FILES['new_component_images']['name'])
) {
    // Extension blocklist
    $forbidden_exts = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps',
        'phtml', 'pht', 'phar', 'php-s', 'php2', 'inc', 'hphp', 'ctp',
        'cgi', 'perl', 'pl', 'py', 'pyc', 'pyo', 'rb', 'gem',
        'jsp', 'jspx', 'asp', 'aspx', 'ascx', 'ashx', 'asmx',
        'htaccess', 'htpasswd', 'ini', 'env', 'config',
        'exe', 'msi', 'bat', 'cmd', 'sh', 'bash',
    ];
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $safeExtMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

    foreach ($_FILES['new_component_images']['name'] as $index => $original_name) {
        if (empty($original_name)) continue;
        if ($_FILES['new_component_images']['error'][$index] !== UPLOAD_ERR_OK) continue;

        $tmp_name = $_FILES['new_component_images']['tmp_name'][$index];
        $fileSize = $_FILES['new_component_images']['size'][$index];

        // Check file size (max 2 MB per image)
        if ($fileSize > 2 * 1024 * 1024) continue;
        if ($fileSize === 0) continue;

        // Validate extension
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        if (in_array($extension, $forbidden_exts, true)) continue;

        // Validate MIME type using finfo (not client-provided type)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmp_name);
        if ($mimeType === false || !in_array($mimeType, $allowedMimes, true)) continue;

        // Validate with GD that it is a real image
        $image = @imagecreatefromstring(file_get_contents($tmp_name));
        if (!$image) continue;

        // Re-encode through GD to strip embedded payloads and malicious metadata
        $safeExt = $safeExtMap[$mimeType] ?? 'jpg';
        $temp_image_name = "__new_" . bin2hex(random_bytes(8)) . "." . $safeExt;
        $target = $images_path . $temp_image_name;

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
            continue;
        }
    }
}

/* ── Renumber images ──────────────────────────────────────────────────────── */
$image_files = [];
if (is_dir($images_path)) {
    $files = scandir($images_path);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $file_path = $images_path . $file;
        if (!is_file($file_path)) continue;
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $image_files[] = $file;
        }
    }
}
natsort($image_files);
$image_files = array_values($image_files);

// Phase 1: Rename to temporary names to avoid conflicts
$temp_files = [];
foreach ($image_files as $file) {
    $old_path = $images_path . $file;
    if (!is_file($old_path)) continue;
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $temporary_name = "__tmp_" . bin2hex(random_bytes(8)) . "." . $extension;
    $temporary_path = $images_path . $temporary_name;
    if (rename($old_path, $temporary_path)) {
        $temp_files[] = $temporary_name;
    }
}

// Phase 2: Renumber to final names
$counter = 1;
foreach ($temp_files as $file) {
    $old_path = $images_path . $file;
    if (!is_file($old_path)) continue;
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $new_name = $counter . "." . $extension;
    $new_path = $images_path . $new_name;
    rename($old_path, $new_path);
    $counter++;
}

header("Location: component-create.php?updated=1");
exit;
