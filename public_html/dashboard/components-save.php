<?php
require_once __DIR__ . '/_guard.php';
dash_require('pages');

$name = trim($_POST['component_name'] ?? '');
$tag  = trim($_POST['component_tag'] ?? '');
$code = $_POST['component_code'] ?? '';

/* ── SECURITY: Validate component name ────────────────────────────────────── */
// Only allow alphanumeric, hyphens, underscores. Block path traversal.
if ($name === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
    die("Invalid component name. Only letters, numbers, hyphens and underscores are allowed.");
}

$dir = __DIR__ . "/components/" . $name;

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

if (!is_dir($dir . "/images")) {
    mkdir($dir . "/images", 0755, true);
}

/* ── SECURITY: Store component content as JSON data, NOT as executable PHP ── */
// The component content is treated as DATA (HTML/markup), never as executable code.
// This closes the critical RCE vector where users could write arbitrary PHP into component.php.
$componentData = [
    'version' => 2,
    'content' => $code,
    'tag'     => $tag,
];

file_put_contents(
    $dir . "/data.json",
    json_encode($componentData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

/* ── SECURITY: Also write meta.json for backward compatibility ────────────── */
file_put_contents(
    $dir . "/meta.json",
    json_encode(["tag" => $tag], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);

/* ── SECURITY: Secure image upload handling ────────────────────────────────── */

// Comprehensive extension blocklist for uploads
function _comp_is_forbidden_extension(string $ext): bool
{
    $forbidden = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps',
        'phtml', 'pht', 'phar', 'php-s', 'php2', 'inc', 'hphp', 'ctp',
        'cgi', 'perl', 'pl', 'py', 'pyc', 'pyo', 'rb', 'gem',
        'jsp', 'jspx', 'asp', 'aspx', 'ascx', 'ashx', 'asmx',
        'cfm', 'pm', 'tcl',
        'htaccess', 'htpasswd', 'ini', 'env', 'config',
        'exe', 'msi', 'bat', 'cmd', 'com', 'scr', 'pif',
        'sh', 'bash', 'csh', 'ksh', 'zsh',
        'shtml', 'stm', 'ssi',
    ];
    return in_array(strtolower($ext), $forbidden, true);
}

if (isset($_FILES['component_images'])) {
    $total = count($_FILES['component_images']['name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $safeExtMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $counter = 1;

    for ($i = 0; $i < $total; $i++) {
        $tmp = $_FILES['component_images']['tmp_name'][$i];
        if (!$tmp) continue;

        // Check upload error
        if ($_FILES['component_images']['error'][$i] !== UPLOAD_ERR_OK) continue;

        // Check file size (max 2 MB per image)
        if ($_FILES['component_images']['size'][$i] > 2 * 1024 * 1024) continue;
        if ($_FILES['component_images']['size'][$i] === 0) continue;

        // Validate extension
        $origName = $_FILES['component_images']['name'][$i];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (_comp_is_forbidden_extension($ext)) continue;

        // Validate MIME type using finfo (not client-provided type)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmp);
        if ($mimeType === false || !in_array($mimeType, $allowedMimes, true)) continue;

        // Validate with GD
        $image = @imagecreatefromstring(file_get_contents($tmp));
        if (!$image) continue;
        imagedestroy($image);

        // Re-encode through GD to strip payloads
        $image = @imagecreatefromstring(file_get_contents($tmp));
        if (!$image) continue;
        $safeExt = $safeExtMap[$mimeType] ?? 'jpg';
        $randomName = bin2hex(random_bytes(8)) . '.' . $safeExt;
        $target = $dir . "/images/" . $randomName;
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

        $counter++;
    }
}

header("Location: component-create.php");
exit;
