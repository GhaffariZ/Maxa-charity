<?php
require_once __DIR__ . '/_guard.php';
dash_require('campaigns');
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/html-sanitizer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/campaign-service.php';

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

    return ['ok' => true, 'type' => $mimeType];
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
    if (empty($_POST['title']) || empty($_POST['target_amount'])) {
        throw new Exception("Title and target amount are required.");
    }
    csrf_check();
    content_prepare($pdo,'campaigns');
    $title = trim((string)$_POST['title']);
    $description = isset($_POST['description']) ? HtmlSanitizer::sanitize($_POST['description']) : '';
    $targetAmountToman = filter_var($_POST['target_amount'], FILTER_VALIDATE_INT);
    if ($targetAmountToman === false || $targetAmountToman < 1 || $targetAmountToman > intdiv(PHP_INT_MAX, 10)) {
        throw new Exception('مبلغ هدف نامعتبر است.');
    }
    $allowed_categories = ['food', 'drug', 'education'];
    $category = isset($_POST['category']) ? $_POST['category'] : 'food';
    if (!in_array($category, $allowed_categories, true)) $category = 'food';

    $branch = content_campaign_branch($pdo,(string)($_POST['branch_slug']??''));
    if (!$branch) {
        throw new Exception('مرکز فعال یافت نشد.');
    }
    $client = new CampaignServiceClient((string)$branch['slug']);
    $editId=trim((string)($_POST['id']??''));
    $ownedCampaign=[];
    if($editId!==''){
        $owned=false;foreach($client->managementCampaigns() as $item)if((string)($item['id']??'')===$editId){$owned=true;$ownedCampaign=$item;break;}
        if(!$owned)throw new RuntimeException('کمپین در این مرکز یافت نشد.');
    }
    $imageFileId = null;

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
        if (!is_uploaded_file($file['tmp_name'])) throw new Exception('Invalid upload source.');
        $actualSize = filesize($file['tmp_name']);
        if ($actualSize === false || $actualSize <= 0 || $actualSize > CAMPAIGN_MAX_UPLOAD_SIZE) {
            throw new Exception('Uploaded file size is invalid.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (_campaign_is_forbidden_extension($ext)) throw new Exception('File extension is not permitted.');

        $validation = _campaign_validate_image($file['tmp_name']);
        if (!$validation['ok']) throw new Exception($validation['error']);

        $dimensions = @getimagesize($file['tmp_name']);
        if ($dimensions === false || $dimensions[0] <= 0 || $dimensions[1] <= 0
            || $dimensions[0] > 10000 || $dimensions[1] > 10000
            || ($dimensions[0] * $dimensions[1]) > 40000000) {
            throw new Exception('Image dimensions are invalid or too large.');
        }

        $reencoded = _campaign_reencode_image($file['tmp_name'], $validation['type']);
        if ($reencoded === false) throw new Exception('Image processing failed.');

        if (!is_file($reencoded) || filesize($reencoded) === 0) { @unlink($reencoded); throw new Exception('Image processing failed.'); }
        try {
            $uploaded = $client->uploadImage(
                $reencoded,
                basename((string)$file['name']),
                (string)$validation['type']
            );
            $imageFileId = (string)($uploaded['id'] ?? '');
            if ($imageFileId === '') {
                throw new Exception('شناسه تصویر از کمپین‌ساز دریافت نشد.');
            }
        } finally {
            @unlink($reencoded);
        }
    }

    $categoryId = $client->ensureCategory(campaign_service_category_name($category));
    $payload = [
        'participation_type' => 'FINANCIAL',
        'category_id' => $categoryId,
        'title' => $title,
        'description' => $description,
        'starts_at' => gmdate('Y-m-d\TH:i:s\Z'),
        'ends_at' => '2099-12-31T23:59:59Z',
        'status' => 'ACTIVE',
        'goal_amount' => $targetAmountToman * 10,
        'manual_raised_amount' => 0,
    ];
    if ($imageFileId !== null) {
        $payload['image_file_id'] = $imageFileId;
    }
    if($editId!==''){
        foreach(['starts_at','ends_at','status','manual_raised_amount','participation_type'] as $key)unset($payload[$key]);
        $created=$client->updateCampaign($editId,$payload);$created['id']=$editId;
    }else $created = $client->createCampaign($payload);
    $source=campaign_service_present(array_merge($ownedCampaign,['title'=>$title,'description'=>$description],$created),$branch);
    if(empty($source['image_url']))$source['image_url']=$imageFileId!==null?($uploaded['url']??$uploaded['public_url']??''):($ownedCampaign['image_url']??'');
    content_save($pdo,'campaigns',(string)$created['id'],null,$source);
    dash_audit($editId!==''?'campaign_updated':'campaign_created', [
        'campaign_id' => $created['id'] ?? null,
        'center' => $branch['slug'],
    ]);
    echo json_encode(["status" => "success", "message" => "محتوای کمپین و زبان‌های انتشار ذخیره شد.", "id" => $created['id'] ?? null], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code($e instanceof CampaignServiceException && $e->status >= 400 && $e->status < 500 ? 422 : 502);
    echo json_encode(["status" => "error", "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
