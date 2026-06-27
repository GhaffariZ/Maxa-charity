<?php
require_once __DIR__ . '/_guard.php';
dash_require('news');
// news-save.php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php"; 

try {
    // تنظیم حالت خطای PDO به استثنا (Exception)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("متد درخواست نامعتبر است.");
    }

    // اگر حجم کل درخواست از post_max_size بیشتر باشد، $_POST و $_FILES خالی می‌شوند
    // و خطای گمراه‌کننده «عنوان وارد نشده» نمایش داده می‌شود؛ این حالت را صریح مدیریت می‌کنیم.
    if (empty($_POST) && empty($_FILES) &&
        isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
        $post_max = ini_get('post_max_size');
        throw new Exception("حجم کل اطلاعات ارسالی (به‌خصوص تصویر) بیشتر از حد مجاز است (حداکثر $post_max). لطفاً تصویر کوچک‌تری انتخاب کنید.");
    }

    if (empty($_POST['title']) || empty($_POST['content'])) {
        throw new Exception("عنوان یا متن خبر وارد نشده است.");
    }

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['title']);
    
    // پاکسازی محتوا و اجازه دادن به تگ‌های ضروری ادیتور (فونت و اسپن اضافه شد)
    $raw_content = $_POST['content'];
    $allowed_tags = "<img><p><br><b><strong><i><u><a><h1><h2><h3><h4><h5><h6><ul><ol><li><div><font><span>";
    $content = strip_tags($raw_content, $allowed_tags);

    $plain_text = trim(preg_replace('/\s+/u', ' ', strip_tags($content)));
    $word_count = $plain_text === '' ? 0 : count(preg_split('/\s+/u', $plain_text));
    $read_time = max(1, (int)ceil($word_count / 200));

    $author = trim($_POST['author'] ?? '');
    $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $keywords = trim($_POST['keywords'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if ($category_id === null || $category_id <= 0) {
        throw new Exception("لطفاً دسته‌بندی خبر را انتخاب کنید.");
    }

    $cat_check_stmt = $pdo->prepare("SELECT id FROM news_categories WHERE id = ? AND is_active = 1 LIMIT 1");
    $cat_check_stmt->execute([$category_id]);
    if (!$cat_check_stmt->fetch()) {
        throw new Exception("دسته‌بندی انتخاب‌شده معتبر نیست.");
    }
    
    // مدیریت زمان
    $publish_date_raw = $_POST['publish_date'] ?? '';
    if (empty($publish_date_raw)) {
        $publish_date = date('Y-m-d H:i:s');
    } else {
        $publish_time = strtotime($publish_date_raw);
        $publish_date = date('Y-m-d H:i:s', $publish_time);
        
        // جلوگیری از ثبت تاریخ خیلی قدیمی برای خبر جدید (تولرانس 5 دقیقه)
        if ($id === 0 && $publish_time < (time() - 300)) {
            throw new Exception("برای خبر جدید، تاریخ و زمان انتشار نمی‌تواند در گذشته باشد.");
        }
    }

    $record_id = 0; 
    $news_code = '';

    // ایزولاسیون چندمستأجری
    $__branch = dash_active_branch_id();

    if ($id > 0) {
        // ======================== حالت ویرایش (UPDATE) ========================
        // IDOR: خبر باید متعلق به همین شعبه باشد
        $stmt_check = $pdo->prepare("SELECT news_code FROM news WHERE id = ? AND branch_id = ?");
        $stmt_check->execute([$id, $__branch]);
        $existing = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$existing) throw new Exception("خبر مورد نظر برای ویرایش یافت نشد.");
        $news_code = $existing['news_code'];

        // قید branch_id در WHERE برای ایمنی مضاعف
        $sql = "UPDATE news SET
                title = ?, content = ?, author = ?, publish_date = ?,
                category_id = ?, keywords = ?, tags = ?, read_time = ? WHERE id = ? AND branch_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $content, $author, $publish_date, $category_id, $keywords, $tags, $read_time, $id, $__branch]);
        
        $message = "تغییرات با موفقیت به‌روزرسانی شد.";
        $record_id = $id;

    } else {
        // ======================== حالت ایجاد جدید (INSERT) ========================
        // تولید news_code یکتا (با بررسی برخورد). طول نهایی ۱۸ کاراکتر است و
        // باید در ستون varchar(30) جا شود؛ در غیر این صورت بریده شدن باعث
        // می‌شود چند خبر کد یکسان و در نتیجه تصویر شاخصِ مشترک بگیرند.
        $news_code = '';
        $code_check = $pdo->prepare("SELECT 1 FROM news WHERE news_code = ? LIMIT 1");
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = 'NEWS-' . date('Ymd') . '-' . str_pad((string)mt_rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $code_check->execute([$candidate]);
            if (!$code_check->fetch()) {
                $news_code = $candidate;
                break;
            }
        }
        if ($news_code === '') {
            // پشتیبان: در حالت بسیار نادرِ برخوردِ پیاپی، از uniqid استفاده کن (۱۹ کاراکتر، در varchar(30) جا می‌شود)
            $news_code = 'NEWS-' . date('Ymd') . '-' . substr(uniqid(), -5);
        }

        $sql = "INSERT INTO news
                (news_code, title, content, author, publish_date, category_id, keywords, tags, status, viewed, read_time, branch_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', 0, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$news_code, $title, $content, $author, $publish_date, $category_id, $keywords, $tags, $read_time, $__branch]);
        
        $record_id = (int)$pdo->lastInsertId();
        $message = "خبر جدید با موفقیت ثبت شد.";
    }

    // ======================== مدیریت تصاویر ========================

    // بررسی حذف تصویر شاخص
    if (isset($_POST['remove_featured_flag']) && $_POST['remove_featured_flag'] === '1' && $record_id > 0) {
        $old_stmt = $pdo->prepare("SELECT featured_image FROM news WHERE id = ?");
        $old_stmt->execute([$record_id]);
        $old = $old_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($old && !empty($old['featured_image'])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/news/$news_code";
            $old_file = "$uploadDir/" . $old['featured_image'];
            if (file_exists($old_file)) {
                unlink($old_file);
            }
            $stmt_remove = $pdo->prepare("UPDATE news SET featured_image = NULL WHERE id = ?");
            $stmt_remove->execute([$record_id]);
        }
    }

    // آپلود تصویر شاخص جدید
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // مدیریت صریح خطاهای آپلود (به جای رد شدن بی‌صدا)
        $up_err = $_FILES['featured_image']['error'];
        if ($up_err !== UPLOAD_ERR_OK) {
            switch ($up_err) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $max = ini_get('upload_max_filesize');
                    throw new Exception("حجم تصویر شاخص بیشتر از حد مجاز است (حداکثر $max). لطفاً تصویر کوچک‌تری انتخاب کنید.");
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception("آپلود تصویر شاخص ناقص انجام شد. لطفاً دوباره تلاش کنید.");
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                    throw new Exception("امکان ذخیره موقت تصویر روی سرور وجود ندارد. لطفاً با مدیر سیستم تماس بگیرید.");
                default:
                    throw new Exception("آپلود تصویر شاخص با خطا مواجه شد (کد $up_err).");
            }
        }

        if ($record_id === 0) {
            throw new Exception("شناسه خبر نامعتبر است. رکورد ثبت نشد.");
        }
        
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/news/$news_code";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true); 
        }

        $file = $_FILES['featured_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($ext, $allowed_ext)) {
            throw new Exception("فرمت تصویر نامعتبر است. فرمت‌های مجاز: jpg, png, webp");
        }

        // حذف تصاویر شاخص قبلی (جلوگیری از انباشت فایل)
        foreach ($allowed_ext as $old_ext) {
            $old_file = "$uploadDir/featured.$old_ext";
            if (file_exists($old_file)) unlink($old_file);
        }
        
        $destination = "$uploadDir/featured.$ext";
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $stmt_img = $pdo->prepare("UPDATE news SET featured_image = ? WHERE id = ?");
            $stmt_img->execute(["featured.$ext", $record_id]);
        } else {
            throw new Exception("آپلود تصویر انجام نشد، لطفاً دسترسی پوشه uploads را بررسی کنید.");
        }
    }

    // خروجی نهایی موفقیت‌آمیز
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => $message,
        "news_code" => $news_code
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // خروجی در صورت بروز خطا
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
