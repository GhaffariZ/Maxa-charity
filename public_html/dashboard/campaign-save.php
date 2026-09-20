<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns');
header('Content-Type: application/json; charset=utf-8');

if (!isset($pdo) || !($pdo instanceof PDO)) {
    $pdo = dash_pdo();
}
require_once __DIR__ . '/../core/html-sanitizer.php';

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
        return ['ok' => false, 'error' => 'قالب فایل قابل خواندن نیست.'];
    }

    $allowedMimes = [
        'image/jpeg' => 'jpeg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($allowedMimes[$mimeType])) {
        return ['ok' => false, 'error' => "فرمت فایل مجاز نیست (فرمت شناسایی شده: {$mimeType})."];
    }

    $detectedType = _campaign_detect_file_type($tmpPath);
    if ($detectedType === 'php_payload') {
        return ['ok' => false, 'error' => 'فایل حاوی کدهای غیرمجاز است و رد شد.'];
    }
    if ($detectedType === null) {
        return ['ok' => false, 'error' => 'فرمت تصویر معتبر نیست.'];
    }

    $typeToMime = [
        'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
        'webp' => 'image/webp', 'bmp' => 'image/bmp', 'tiff' => 'image/tiff',
    ];
    if (isset($typeToMime[$detectedType]) && !isset($allowedMimes[$typeToMime[$detectedType]])) {
        return ['ok' => false, 'error' => "فرمت تصویر ({$detectedType}) مجاز نیست."];
    }

    $image = @imagecreatefromstring(file_get_contents($tmpPath));
    if (!$image) {
        return ['ok' => false, 'error' => 'فایل بارگذاری شده تصویر معتبری نیست.'];
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

    $title = isset($_POST['title']) ? trim((string)$_POST['title']) : '';
    $target_amount = isset($_POST['target_amount']) ? (float)$_POST['target_amount'] : 0.0;

    if ($title === '' || $target_amount <= 0) {
        throw new Exception("عنوان کمپین و مبلغ هدف معتبر الزامی هستند.");
    }

    $description = isset($_POST['description']) ? HtmlSanitizer::sanitize($_POST['description']) : '';
    $allowed_categories = ['food', 'drug', 'education'];
    $category = isset($_POST['category']) ? trim((string)$_POST['category']) : 'food';
    if (!in_array($category, $allowed_categories, true)) {
        $category = 'food';
    }

    $__branch = dash_active_branch_id();
    $is_hq = dash_is_hq_view();

    $edit_id = 0;
    if (!empty($_POST['id'])) {
        $edit_id = (int)$_POST['id'];
    } elseif (!empty($_POST['campaign_id'])) {
        $edit_id = (int)$_POST['campaign_id'];
    }

    $campaign_code = '';

    if ($edit_id > 0) {
        // ── حالت ویرایش کمپین موجود (UPDATE) ──
        if ($is_hq) {
            $checkStmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? LIMIT 1");
            $checkStmt->execute([$edit_id]);
        } else {
            $checkStmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = ? AND branch_id = ? LIMIT 1");
            $checkStmt->execute([$edit_id, $__branch]);
        }
        $existing = $checkStmt->fetch();
        if (!$existing) {
            throw new Exception("کمپین مورد نظر یافت نشد یا شما دسترسی ویرایش آن را ندارید.");
        }

        $campaign_code = !empty($existing['campaign_code']) ? $existing['campaign_code'] : ('CAMP-' . date('Ymd') . '-' . str_pad((string)$edit_id, 4, '0', STR_PAD_LEFT));

        $updateSql = "UPDATE campaigns SET title = ?, description = ?, category = ?, target_amount = ?, campaign_code = ? WHERE id = ?";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([$title, $description, $category, $target_amount, $campaign_code, $edit_id]);

        $campaignId = $edit_id;
        $successMessage = "کمپین با موفقیت ویرایش و ذخیره شد.";
    } else {
        // ── حالت ایجاد کمپین جدید (INSERT) ──
        $campaign_code = 'CAMP-' . date('Ymd') . '-' . str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $sql = "INSERT INTO campaigns (campaign_code, title, description, category, target_amount, collected_amount, branch_id) VALUES (?, ?, ?, ?, ?, 0, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$campaign_code, $title, $description, $category, $target_amount, $__branch]);
        $campaignId = (int)$pdo->lastInsertId();
        $successMessage = "کمپین با موفقیت ایجاد و منتشر شد.";
    }

    // ── مدیریت آپلود تصویر شاخص (در ساخت یا ویرایش) ──
    if (!empty($_FILES['featured_image']['name'])) {
        $file = $_FILES['featured_image'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'حجم فایل از حد مجاز سرور بیشتر است.',
                UPLOAD_ERR_FORM_SIZE => 'حجم فایل از حد مجاز فرم بیشتر است.',
                UPLOAD_ERR_PARTIAL => 'فایل به‌صورت ناقص آپلود شد.',
                UPLOAD_ERR_NO_FILE => 'فایلی انتخاب نشده است.',
                UPLOAD_ERR_NO_TMP_DIR => 'پوشه موقت سرور یافت نشد.',
                UPLOAD_ERR_CANT_WRITE => 'خطا در ذخیره فایل روی سرور.',
            ];
            throw new Exception($errors[$file['error']] ?? 'خطای ناشناخته در بارگذاری تصویر.');
        }
        if ($file['size'] > CAMPAIGN_MAX_UPLOAD_SIZE) {
            throw new Exception('حجم فایل بیشتر از حد مجاز ۵ مگابایت است.');
        }
        if ($file['size'] === 0) {
            throw new Exception('فایل بارگذاری شده خالی است.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (_campaign_is_forbidden_extension($ext)) {
            throw new Exception('پسوند این فایل مجاز نیست.');
        }

        $validation = _campaign_validate_image($file['tmp_name']);
        if (!$validation['ok']) {
            throw new Exception($validation['error']);
        }

        $reencoded = _campaign_reencode_image($file['tmp_name'], $validation['type']);
        if ($reencoded === false) {
            throw new Exception('خطا در پردازش تصویر.');
        }

        $safeExtMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $safeExt = $safeExtMap[$validation['type']] ?? 'jpg';
        $randomName = bin2hex(random_bytes(16)) . '.' . $safeExt;
        $safeCampaignCode = preg_replace('/[^A-Za-z0-9_-]/', '', $campaign_code);
        if ($safeCampaignCode === '') {
            $safeCampaignCode = 'camp_' . $campaignId;
        }

        $uploadDir = __DIR__ . '/../uploads/campaigns/' . $safeCampaignCode;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!is_file($reencoded) || filesize($reencoded) === 0) {
            @unlink($reencoded);
            throw new Exception('خطا در پردازش تصویر.');
        }
        $destination = $uploadDir . '/' . $randomName;
        if (!rename($reencoded, $destination)) {
            @unlink($reencoded);
            throw new Exception('خطا در ذخیره‌سازی تصویر نهایی.');
        }
        $finalCheck = @getimagesize($destination);
        if ($finalCheck === false) {
            @unlink($destination);
            throw new Exception('فایل ذخیره‌شده تصویر معتبری نیست.');
        }

        $image_path = "/uploads/campaigns/" . $safeCampaignCode . "/" . $randomName;
        $pdo->prepare("UPDATE campaigns SET image_url = ? WHERE id = ?")->execute([$image_path, $campaignId]);
    }

    echo json_encode([
        "status" => "success",
        "message" => $successMessage,
        "id" => $campaignId
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
