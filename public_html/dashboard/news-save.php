<?php
/**
 * ============================================================================
 * Enterprise Newsroom - Backend News Save & Media Processing Endpoint
 * ============================================================================
 * Handles create/update operations for news articles, sanitizes rich journalistic
 * content, processes cropped/optimized featured images, enforces multi-tenancy,
 * and maintains editorial workflow states.
 */

require_once __DIR__ . '/_guard.php';
dash_require('news');

header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

try {
    // Configure PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("متد درخواست نامعتبر است.");
    }

    // Handle post_max_size overflow
    if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
        $post_max = ini_get('post_max_size');
        throw new Exception("حجم کل اطلاعات ارسالی از حد مجاز سرور ($post_max) بیشتر است. لطفاً تصاویر کوچک‌تری انتخاب کنید.");
    }

    // Basic Validation
    if (empty($_POST['title']) || trim($_POST['title']) === '') {
        throw new Exception("عنوان اصلی خبر (تیتر) الزامی است.");
    }

    if (!isset($_POST['content']) || trim($_POST['content']) === '') {
        throw new Exception("متن و بدنه خبر نمی‌تواند خالی باشد.");
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['title']);
    $subtitle = isset($_POST['subtitle']) ? trim($_POST['subtitle']) : '';
    $kicker = isset($_POST['kicker']) ? trim($_POST['kicker']) : '';
    $lead = isset($_POST['lead']) ? trim($_POST['lead']) : '';

    // If kicker or lead are provided and subtitle is empty, intelligently manage them
    // Or if content does not have the lead styled, we preserve the rich HTML content.
    $raw_content = $_POST['content'];

    // Whitelist rich journalistic HTML elements and attributes
    $allowed_tags = "<img><figure><figcaption><p><br><b><strong><i><u><s><em><a><h1><h2><h3><h4><h5><h6>" .
                   "<ul><ol><li><div><font><span><table><thead><tbody><tfoot><tr><th><td><iframe><blockquote>" .
                   "<code><pre><sub><sup><hr><video><audio><source><track><mark><small><section><article><aside><time><svg><path>";
    $content = strip_tags($raw_content, $allowed_tags);

    // Compute Reading Time & Word Count
    $plain_text = trim(preg_replace('/\s+/u', ' ', strip_tags($content)));
    $word_count = $plain_text === '' ? 0 : count(preg_split('/\s+/u', $plain_text, -1, PREG_SPLIT_NO_EMPTY));
    $read_time = max(1, (int)ceil($word_count / 200));

    // Author / Bylines / Credits
    $author = trim($_POST['author'] ?? '');
    $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $keywords = trim($_POST['keywords'] ?? '');
    $tags_raw = trim($_POST['tags'] ?? '');
    $status_input = trim($_POST['status'] ?? 'draft');
    $allowed_statuses = ['draft', 'review', 'published', 'rejected'];
    $status = in_array($status_input, $allowed_statuses, true) ? $status_input : 'draft';

    if ($category_id === null || $category_id <= 0) {
        throw new Exception("لطفاً دسته‌بندی موضوعی خبر را مشخص کنید.");
    }

    $cat_check_stmt = $pdo->prepare("SELECT id FROM news_categories WHERE id = ? AND is_active = 1 LIMIT 1");
    $cat_check_stmt->execute([$category_id]);
    if (!$cat_check_stmt->fetch()) {
        throw new Exception("دسته‌بندی انتخاب‌شده معتبر یا فعال نیست.");
    }

    // Publish Date Processing
    $publish_date_raw = trim($_POST['publish_date'] ?? '');
    if (empty($publish_date_raw)) {
        $publish_date = date('Y-m-d H:i:s');
    } else {
        $publish_time = strtotime($publish_date_raw);
        if ($publish_time === false || $publish_time <= 0) {
            $publish_date = date('Y-m-d H:i:s');
        } else {
            $publish_date = date('Y-m-d H:i:s', $publish_time);
        }
    }

    // Multi-tenant Tenant Isolation
    $__branch = dash_active_branch_id();

    // Check available columns in `news` table to guarantee dynamic compatibility
    $columns_stmt = $pdo->query("SHOW COLUMNS FROM news");
    $existing_cols = [];
    while ($col = $columns_stmt->fetch(PDO::FETCH_ASSOC)) {
        $existing_cols[strtolower($col['Field'])] = true;
    }

    $record_id = 0;
    $news_code = '';

    if ($id > 0) {
        // ====================================================================
        // UPDATE Mode (ویرایش خبر موجود)
        // ====================================================================
        $stmt_check = $pdo->prepare("SELECT id, news_code FROM news WHERE id = ? AND branch_id = ? LIMIT 1");
        $stmt_check->execute([$id, $__branch]);
        $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            throw new Exception("خبر مورد نظر برای ویرایش یافت نشد یا شما به آن دسترسی ندارید.");
        }
        $news_code = $existing['news_code'];
        $record_id = $id;

        // Build dynamic UPDATE SQL based on available columns
        $update_fields = [
            'title = ?',
            'content = ?',
            'author = ?',
            'publish_date = ?',
            'category_id = ?',
            'keywords = ?',
            'read_time = ?',
            'status = ?'
        ];
        $update_values = [
            $title,
            $content,
            $author,
            $publish_date,
            $category_id,
            $keywords,
            $read_time,
            $status
        ];

        if (isset($existing_cols['subtitle'])) {
            $update_fields[] = 'subtitle = ?';
            $update_values[] = $subtitle;
        }
        if (isset($existing_cols['tags'])) {
            $update_fields[] = 'tags = ?';
            $update_values[] = $tags_raw;
        }

        $update_values[] = $id;
        $update_values[] = $__branch;

        $sql = "UPDATE news SET " . implode(', ', $update_fields) . " WHERE id = ? AND branch_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($update_values);

        $message = "تغییرات خبر با موفقیت ذخیره و به‌روزرسانی شد.";

    } else {
        // ====================================================================
        // INSERT Mode (ایجاد خبر جدید)
        // ====================================================================
        // Generate Unique Collision-Proof news_code
        $code_check = $pdo->prepare("SELECT 1 FROM news WHERE news_code = ? LIMIT 1");
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $candidate = 'NEWS-' . date('Ymd') . '-' . str_pad((string)mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $code_check->execute([$candidate]);
            if (!$code_check->fetch()) {
                $news_code = $candidate;
                break;
            }
        }
        if (empty($news_code)) {
            $news_code = 'NEWS-' . date('Ymd') . '-' . substr(uniqid('', true), -5);
        }

        $insert_cols = ['news_code', 'title', 'content', 'author', 'publish_date', 'category_id', 'keywords', 'status', 'viewed', 'read_time', 'branch_id'];
        $insert_placeholders = ['?', '?', '?', '?', '?', '?', '?', '?', '0', '?', '?'];
        $insert_values = [$news_code, $title, $content, $author, $publish_date, $category_id, $keywords, $status, $read_time, $__branch];

        if (isset($existing_cols['subtitle'])) {
            $insert_cols[] = 'subtitle';
            $insert_placeholders[] = '?';
            $insert_values[] = $subtitle;
        }
        if (isset($existing_cols['tags'])) {
            $insert_cols[] = 'tags';
            $insert_placeholders[] = '?';
            $insert_values[] = $tags_raw;
        }

        $sql = "INSERT INTO news (" . implode(', ', $insert_cols) . ") VALUES (" . implode(', ', $insert_placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($insert_values);

        $record_id = (int)$pdo->lastInsertId();
        $message = "خبر جدید با موفقیت ثبت شد.";
    }

    // ====================================================================
    // Featured Image Studio Processing (Cropper + Compressor + Fallback)
    // ====================================================================
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/news/$news_code";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }

    $allowed_image_exts = ["webp", "jpg", "jpeg", "png"];

    // 1. Remove Featured Image Flag
    if (isset($_POST['remove_featured_flag']) && $_POST['remove_featured_flag'] === '1' && $record_id > 0) {
        $old_stmt = $pdo->prepare("SELECT featured_image FROM news WHERE id = ?");
        $old_stmt->execute([$record_id]);
        $old = $old_stmt->fetch(PDO::FETCH_ASSOC);

        if ($old && !empty($old['featured_image'])) {
            $old_file = "$upload_dir/" . $old['featured_image'];
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
            $stmt_remove = $pdo->prepare("UPDATE news SET featured_image = NULL WHERE id = ?");
            $stmt_remove->execute([$record_id]);
        }
    }

    // 2. Base64 Cropped Image from Cropper.js Studio
    $base64_featured = $_POST['featured_image_base64'] ?? '';
    if (!empty($base64_featured) && preg_match('/^data:image\/(\w+);base64,/', $base64_featured, $type_matches)) {
        $raw_base64 = substr($base64_featured, strpos($base64_featured, ',') + 1);
        $ext = strtolower($type_matches[1]);
        if ($ext === 'jpeg') $ext = 'jpg';

        if (!in_array($ext, $allowed_image_exts, true)) {
            $ext = 'webp';
        }

        $decoded_image = base64_decode($raw_base64);
        if ($decoded_image !== false && strlen($decoded_image) > 0) {
            // Delete previous featured files
            foreach ($allowed_image_exts as $old_ext) {
                $old_file = "$upload_dir/featured.$old_ext";
                if (file_exists($old_file)) @unlink($old_file);
            }

            $featured_filename = "featured." . $ext;
            $destination = "$upload_dir/$featured_filename";

            if (file_put_contents($destination, $decoded_image) !== false) {
                $stmt_img = $pdo->prepare("UPDATE news SET featured_image = ? WHERE id = ?");
                $stmt_img->execute([$featured_filename, $record_id]);
            }
        }
    }
    // 3. Fallback: Standard Multipart File Upload
    elseif (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $up_err = $_FILES['featured_image']['error'];
        if ($up_err === UPLOAD_ERR_OK) {
            $file = $_FILES['featured_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_image_exts, true)) {
                throw new Exception("فرمت تصویر شاخص نامعتبر است. فرمت‌های مجاز: WEBP, JPG, PNG");
            }

            // Remove old featured files
            foreach ($allowed_image_exts as $old_ext) {
                $old_file = "$upload_dir/featured.$old_ext";
                if (file_exists($old_file)) @unlink($old_file);
            }

            $featured_filename = "featured." . $ext;
            $destination = "$upload_dir/$featured_filename";

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt_img = $pdo->prepare("UPDATE news SET featured_image = ? WHERE id = ?");
                $stmt_img->execute([$featured_filename, $record_id]);
            }
        }
    }

    // Success JSON response
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => $message,
        "news_code" => $news_code,
        "id" => $record_id
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
