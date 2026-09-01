<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns');
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/html-sanitizer.php';

/* ── SECURITY: Secure file upload helper functions ────────────────────────── */

function _campaign_is_forbidden_extension(string $ext): bool
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
        'jar', 'war', 'ear',
        'shtml', 'stm', 'ssi',
    ];
    return in_array(strtolower($ext), $forbidden, true);
}

function _campaign_detect_file_type(string $tmpPath): ?string
{
    $handle = @fopen($tmpPath, 'rb');
    if (!$handle) return null;
    $header = fread($handle, 32);
    fclose($handle);
    if ($header === false || strlen($header) < 8) return null;

    if (substr($header, 0, 8) === "\x89PNG\r\n\x1a\n") return 'png';
    if (substr($header, 0, 2) === "\xFF\xD8") return 'jpeg';
    if (substr($header, 0, 6) === 'GIF87a' || substr($header, 0, 6) === 'GIF89a') return 'gif';
    if (substr($header, 0, 4) === 'RIFF' && substr($header, 8, 4) === 'WEBP') return 'webp';
    if (substr($header, 0, 2) === 'BM') return 'bmp';
    if (substr($header, 0, 2) === 'II' || substr($header, 0, 2) === 'MM') return 'tiff';

    $lower = strtolower($header);
    if (preg_match('/<\?php|<\?=|<\?[\s]/i', $lower)) return 'php_payload';
    return null;
}

function _campaign_validate_image(string $tmpPath): array
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpPath);
    if ($mimeType === false) {
        return ['ok' => false, 'error' => 'Unable to read file type.'];
    }

    $allowedMimes = [
        'image/jpeg' => 'jpeg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($allowedMimes[$mimeType])) {
        return ['ok' => false, 'error' => "Uploaded file is not a permitted image (detected: {$mimeType})."];
    }

    $detectedType = _campaign_detect_file_type($tmpPath);
    if ($detectedType === 'php_payload') {
        return ['ok' => false, 'error' => 'File contains malicious code and was rejected.'];
    }
    if ($detectedType === null) {
        return ['ok' => false, 'error' => 'File format could not be identified.'];
    }

    $typeToMime = [
        'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
        'webp' => 'image/webp', 'bmp' => 'image/bmp', 'tiff' => 'image/tiff',
    ];
    if (isset($typeToMime[$detectedType]) && !isset($allowedMimes[$typeToMime[$detectedType]])) {
        return ['ok' => false, 'error' => "Detected format ({$detectedType}) is not permitted."];
    }

    $image = @imagecreatefromstring(file_get_contents($tmpPath));
    if (!$image) {
        return ['ok' => false, 'error' => 'Uploaded file is not a valid image.'];
    }
    imagedestroy($image);

    return ['ok' => true, 'type' => $allowedMimes[$mimeType]];
}

function _campaign_reencode_image(string $tmpPath, string $mimeType): string|false
{
    $image = @imagecreatefromstring(file_get_contents($tmpPath));
    if (!$image) return false;
    $newTmp = tempnam(sys_get_temp_dir(), 'campaign_img_');
    switch ($mimeType) {
        case 'image/jpeg': $result = @imagejpeg($image, $newTmp, 85); break;
        case 'image/png':  $result = @imagepng($image, $newTmp, 6); break;
        case 'image/gif':  $result = @imagegif($image, $newTmp); break;
        case 'image/webp': $result = @imagewebp($image, $newTmp, 85); break;
        default: imagedestroy($image); @unlink($newTmp); return false;
    }
    imagedestroy($image);
    if (!$result || !is_file($newTmp) || filesize($newTmp) === 0) { @unlink($newTmp); return false; }
    return $newTmp;
}

/* ── Constants ────────────────────────────────────────────────────────────── */
define('CAMPAIGN_MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

/* ── Main logic ───────────────────────────────────────────────────────────── */
try {
    $pdo->exec("SET NAMES utf8mb4");
    if (empty($_POST['title']) || empty($_POST['target_amount'])) {
        throw new Exception("Title and target amount are required.");
    }
    $title = $_POST['title'];
    $description = isset($_POST['description']) ? HtmlSanitizer::sanitize($_POST['description']) : '';
    $target_amount = (float)$_POST['target_amount'];
    $allowed_categories = ['food', 'drug', 'education'];
    $category = isset($_POST['category']) ? $_POST['category'] : 'food';
    if (!in_array($category, $allowed_categories, true)) $category = 'food';

    $campaign_code = 'CAMP-' . date('Ymd') . '-' . str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
    $__branch = dash_active_branch_id();
    $sql = "INSERT INTO campaigns (campaign_code, title, description, category, target_amount, collected_amount, branch_id) VALUES (?, ?, ?, ?, ?, 0, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$campaign_code, $title, $description, $category, $target_amount, $__branch]);
    $id = $pdo->lastInsertId();

    if (!empty($_FILES['featured_image']['name'])) {
        $file = $_FILES['featured_image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error.',
                UPLOAD_ERR_CANT_WRITE => 'Server write error.',
            ];
            throw new Exception($errors[$file['error']] ?? 'Unknown upload error.');
        }
        if ($file['size'] > CAMPAIGN_MAX_UPLOAD_SIZE) throw new Exception('File size exceeds 5 MB limit.');
        if ($file['size'] === 0) throw new Exception('Uploaded file is empty.');

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (_campaign_is_forbidden_extension($ext)) throw new Exception('File extension is not permitted.');

        $validation = _campaign_validate_image($file['tmp_name']);
        if (!$validation['ok']) throw new Exception($validation['error']);

        $reencoded = _campaign_reencode_image($file['tmp_name'], $validation['type']);
        if ($reencoded === false) throw new Exception('Image processing failed.');

        $safeExtMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $safeExt = $safeExtMap[$validation['type']] ?? 'jpg';
        $randomName = bin2hex(random_bytes(16)) . '.' . $safeExt;
        $safeCampaignCode = preg_replace('/[^A-Za-z0-9_-]/', '', $campaign_code);
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/campaigns/" . $safeCampaignCode;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (!is_file($reencoded) || filesize($reencoded) === 0) { @unlink($reencoded); throw new Exception('Image processing failed.'); }
        $destination = $uploadDir . '/' . $randomName;
        if (!rename($reencoded, $destination)) { @unlink($reencoded); throw new Exception('Failed to save image.'); }
        $finalCheck = @getimagesize($destination);
        if ($finalCheck === false) { @unlink($destination); throw new Exception('Saved file is not a valid image.'); }

        $image_path = "/uploads/campaigns/" . $safeCampaignCode . "/" . $randomName;
        $pdo->prepare("UPDATE campaigns SET image_url = ? WHERE id = ?")->execute([$image_path, $id]);
    }
    echo json_encode(["status" => "success", "message" => "Campaign created successfully", "id" => $id], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
