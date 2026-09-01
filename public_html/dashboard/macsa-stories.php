<?php
require_once __DIR__ . '/_guard.php';

// Access guard: Only Central Headquarters (HQ) or Super Admin can access
$isHq = dash_is_hq_view() || !empty($DASH_USER['is_super']);
if (!$isHq) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title>عدم دسترسی | پنل مکسا</title>
        <script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700;900&display=swap" rel="stylesheet">
        <style>
            :root{--color-primary:#007b7a;--color-text:#2f3437;--color-bg:#f8f9fa;--color-surface:#ffffff;--color-border:#e6e8ea;}
            :root[data-theme="dark"]{--color-text:#e7ecee;--color-bg:#0f1518;--color-surface:#19232a;--color-border:#2a343a;}
            body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;text-align:center;}
            .box{background:var(--color-surface);border:1px solid var(--color-border);padding:40px;border-radius:24px;max-width:480px;box-shadow:0 10px 30px rgba(0,0,0,.08);}
            h1{color:#e0556b;font-size:22px;margin-bottom:12px;}
            p{color:var(--color-text);opacity:.8;font-size:14px;line-height:1.8;margin-bottom:24px;}
            a{display:inline-block;padding:10px 24px;border-radius:12px;background:var(--color-primary);color:#fff;text-decoration:none;font-weight:700;}
        </style>
    </head>
    <body>
        <div class="box">
            <h1>دسترسی محدود است</h1>
            <p>مدیریت «بانک روایات امید مکسا» یک امکان متمرکز است و تنها از طریق <strong>دفتر مرکزی (ستاد)</strong> قابل دسترسی می‌باشد.</p>
            <a href="index.php">بازگشت به داشبورد</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Auto-create table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS `macsa_stories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `narrator_name` VARCHAR(255) NOT NULL,
    `narrator_role` VARCHAR(255) NULL,
    `tag` VARCHAR(100) NOT NULL DEFAULT 'staff',
    `excerpt` TEXT NULL,
    `content` LONGTEXT NOT NULL,
    `image` VARCHAR(500) NULL,
    `read_time` VARCHAR(50) NULL DEFAULT '۴ دقیقه مطالعه',
    `status` ENUM('published', 'draft') DEFAULT 'published',
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// Seed initial default stories if empty
$count = (int)$pdo->query("SELECT COUNT(*) FROM `macsa_stories`")->fetchColumn();
if ($count === 0) {
    $seedStmt = $pdo->prepare("INSERT INTO `macsa_stories` (`title`, `narrator_name`, `narrator_role`, `tag`, `excerpt`, `content`, `read_time`, `status`, `sort_order`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $seedStmt->execute([
        'زندگی تا آخرین لحظه',
        'دکتر زهرا جعفری',
        'روانشناس مراقبت درمنزل شعبه تهران',
        'روایت کادر درمان',
        'یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود. همسر ایشان ایرانی‌الاصل بود، اما در آمریکا بزرگ شده بود ...',
        'یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود. همسر ایشان ایرانی‌الاصل بود، اما در آمریکا بزرگ شده بود. در جریان درمان و همراهی با این خانواده، شاهد پیوند عمیق عاطفی و امید بی‌پایان به زندگی بودیم که تا آخرین لحظات نیز جریان داشت.',
        '۴ دقیقه مطالعه',
        'published',
        1
    ]);
    $seedStmt->execute([
        'وقتی نوشتن، درمان است',
        'آقای جواد چنگی',
        'روانشناس مراقبت در منزل شعبه تهران',
        'روایت کادر درمان',
        'در تابستان امسال، خانمی در دهه پنجم زندگی‌اش با تشخیص سرطان پستان متاستاز داده به مکسا ارجاع داده شد و....',
        'در تابستان امسال، خانمی در دهه پنجم زندگی‌اش با تشخیص سرطان پستان متاستاز داده به مکسا ارجاع داده شد. با شروع جلسات روان‌شناختی و ترغیب ایشان به ثبت احساسات و نوشتن خاطرات، شاهد تحولی شگرف در پذیرش بیماری و آرامش روانی بیمار و اعضای خانواده‌اش بودیم.',
        '۴ دقیقه مطالعه',
        'published',
        2
    ]);
    $seedStmt->execute([
        'از بحران تا ثبات؛ تجربه‌ای از همراهی مستمر اجتماعی',
        'آقای علی یزدانی',
        'مددکار اجتماعی مراقبت در منزل شعبه تهران',
        'روایت کادر درمان',
        'سرپرست یک خانواده به دلیل درگیری مغزی ناشی از سرطان، بستری شده بود و به قول معروف وابسته به تخت بود و متاسفانه دچار زخم بستر هم شده بود. توان انجام هیچ فعالیتی را نداشت و....',
        'سرپرست یک خانواده به دلیل درگیری مغزی ناشی از سرطان، بستری شده بود و به قول معروف وابسته به تخت بود و متاسفانه دچار زخم بستر هم شده بود. تیم مددکاری مکسا با حضور در منزل و تأمین تجهیزات و حمایت‌های معیشتی و دارویی، باری سنگین را از دوش این خانواده برداشت.',
        '۵ دقیقه مطالعه',
        'published',
        3
    ]);
}

// Handle Form Submissions (Add, Edit, Delete, Toggle Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $title        = trim($_POST['title'] ?? '');
        $narratorName = trim($_POST['narrator_name'] ?? '');
        $narratorRole = trim($_POST['narrator_role'] ?? '');
        $tag          = trim($_POST['tag'] ?? 'روایت کادر درمان');
        $customTag    = trim($_POST['custom_tag'] ?? '');
        if ($tag === 'custom' && $customTag !== '') {
            $tag = $customTag;
        }
        $excerpt      = trim($_POST['excerpt'] ?? '');
        $content      = trim($_POST['content'] ?? '');
        $readTime     = trim($_POST['read_time'] ?? '۴ دقیقه مطالعه');
        $status       = in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'published';
        $sortOrder    = (int)($_POST['sort_order'] ?? 0);
        $existingImg  = trim($_POST['existing_image'] ?? '');

        // Generate excerpt automatically if empty
        if ($excerpt === '' && $content !== '') {
            $excerpt = mb_substr(strip_tags($content), 0, 180, 'UTF-8') . '...';
        }

        // Handle Image Upload
        $imagePath = $existingImg;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['image']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true)) {
                $uploadDir = __DIR__ . '/../uploads/stories/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = 'story_' . time() . '_' . random_int(100, 999) . '.' . $ext;
                if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                    $imagePath = '/uploads/stories/' . $filename;
                }
            }
        } elseif (!empty($_POST['image_url'])) {
            $imagePath = trim($_POST['image_url']);
        }

        if ($title !== '' && $narratorName !== '' && $content !== '') {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO `macsa_stories` (`title`, `narrator_name`, `narrator_role`, `tag`, `excerpt`, `content`, `image`, `read_time`, `status`, `sort_order`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $narratorName, $narratorRole, $tag, $excerpt, $content, $imagePath, $readTime, $status, $sortOrder]);
                $_SESSION['stories_flash'] = ['type' => 'ok', 'text' => 'روایت جدید با موفقیت ثبت و به صفحه اصلی متصل شد.'];
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $pdo->prepare("UPDATE `macsa_stories` SET `title` = ?, `narrator_name` = ?, `narrator_role` = ?, `tag` = ?, `excerpt` = ?, `content` = ?, `image` = ?, `read_time` = ?, `status` = ?, `sort_order` = ? WHERE `id` = ?");
                $stmt->execute([$title, $narratorName, $narratorRole, $tag, $excerpt, $content, $imagePath, $readTime, $status, $sortOrder, $id]);
                $_SESSION['stories_flash'] = ['type' => 'ok', 'text' => 'روایت مورد نظر با موفقیت به‌روزرسانی شد.'];
            }
            header("Location: macsa-stories.php");
            exit;
        } else {
            $_SESSION['stories_flash'] = ['type' => 'err', 'text' => 'لطفاً عنوان، نام راوی و متن کامل روایت را وارد نمایید.'];
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM `macsa_stories` WHERE `id` = ?")->execute([$id]);
            $_SESSION['stories_flash'] = ['type' => 'ok', 'text' => 'روایت مورد نظر با موفقیت حذف شد.'];
        }
        header("Location: macsa-stories.php");
        exit;
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $current = $pdo->prepare("SELECT `status` FROM `macsa_stories` WHERE `id` = ?");
            $current->execute([$id]);
            $st = $current->fetchColumn();
            $newStatus = ($st === 'published') ? 'draft' : 'published';
            $pdo->prepare("UPDATE `macsa_stories` SET `status` = ? WHERE `id` = ?")->execute([$newStatus, $id]);
            $_SESSION['stories_flash'] = ['type' => 'ok', 'text' => 'وضعیت انتشار روایت تغییر یافت.'];
        }
        header("Location: macsa-stories.php");
        exit;
    }
}

// Fetch all stories
$stmt = $pdo->query("SELECT * FROM `macsa_stories` ORDER BY `sort_order` ASC, `id` DESC");
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalCount     = count($stories);
$publishedCount = 0;
$staffCount     = 0;
$patientCount   = 0;
foreach ($stories as $s) {
    if ($s['status'] === 'published') $publishedCount++;
    if (str_contains($s['tag'], 'کادر درمان') || $s['tag'] === 'staff') $staffCount++;
    else $patientCount++;
}

$FLASH = $_SESSION['stories_flash'] ?? null;
unset($_SESSION['stories_flash']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>بانک روایات امید مکسا | پنل مدیریت</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-text:#2f3437; --color-muted:#8a9499;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --accent-gold:#f5a623; --accent-gold-bg:rgba(245,166,35,0.12);
  --success:#16a37a; --danger:#e0556b;
  --success-12:rgba(22,163,122,.14); --danger-12:rgba(224,85,107,.12);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 3px rgba(16,40,40,.04),0 2px 6px rgba(16,40,40,.05);
  --shadow-md:0 6px 18px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --success-12:rgba(22,163,122,.18); --danger-12:rgba(224,85,107,.16);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}

.wrap{max-width:1160px;margin:0 auto}

.nav-back{display:inline-flex;align-items:center;gap:6px;color:var(--color-muted);text-decoration:none;font-weight:600;margin-bottom:14px;transition:color .2s;font-size:13px}
.nav-back:hover{color:var(--color-primary)}
.nav-back svg{width:16px;height:16px}

/* Head Bar */
.head-bar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:24px}
.head-info{display:flex;align-items:center;gap:16px}
.head-ic{width:56px;height:56px;border-radius:18px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,#0899A9,var(--color-primary));box-shadow:0 12px 24px -10px rgba(8,153,169,.7)}
.head-ic svg{width:28px;height:28px}
.head-actions{display:flex;gap:10px}

/* Buttons */
.btn{font-family:inherit;font-weight:700;border:none;cursor:pointer;border-radius:12px;display:inline-flex;align-items:center;gap:7px;
  transition:all .2s var(--ease); text-decoration:none;font-size:13.5px;padding:10px 18px}
.btn svg{width:17px;height:17px}
.btn-primary{background:linear-gradient(135deg,#0899A9,#007b7a);color:#fff;box-shadow:0 8px 18px -6px rgba(8,153,169,.6)}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 24px -6px rgba(8,153,169,.8);color:#fff}
.btn-ghost{background:var(--color-surface);color:var(--color-text);border:1px solid var(--color-border)}
.btn-ghost:hover{border-color:var(--color-primary);color:var(--color-primary)}
.btn-sm{padding:6px 12px;font-size:12px;border-radius:8px}

/* Flash Messages */
.flash{border-radius:14px;padding:14px 18px;font-size:13.5px;font-weight:700;margin-bottom:22px;display:flex;align-items:center;gap:10px}
.flash.ok{background:var(--success-12);color:var(--success);border:1px solid rgba(22,163,122,.25)}
.flash.err{background:var(--danger-12);color:var(--danger);border:1px solid rgba(224,85,107,.25)}

/* Stat Cards */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:24px}
.stat-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:18px 20px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:14px}
.stat-ic{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;flex-shrink:0}
.stat-ic.teal{background:rgba(8,153,169,0.12);color:#0899A9}
.stat-ic.green{background:var(--success-12);color:var(--success)}
.stat-ic.gold{background:rgba(245,166,35,0.14);color:#d97706}
.stat-ic.purple{background:rgba(124,77,219,0.14);color:#7c4ddb}
.stat-title{font-size:12px;color:var(--color-muted);margin-bottom:2px}
.stat-val{font-size:22px;font-weight:900;color:var(--color-text)}

/* Filter Bar */
.filter-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;box-shadow:var(--shadow-sm)}
.search-input{background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:9px 14px;color:var(--color-text);font-family:inherit;font-size:13px;width:260px;max-width:100%;outline:none;transition:border-color .2s}
.search-input:focus{border-color:var(--color-primary)}
.tag-filters{display:flex;gap:8px;flex-wrap:wrap}
.filter-btn{background:var(--color-bg);border:1px solid var(--color-border);color:var(--color-muted);padding:6px 14px;border-radius:20px;font-size:12.5px;font-weight:600;cursor:pointer;transition:all .2s;font-family:inherit}
.filter-btn.active, .filter-btn:hover{background:rgba(8,153,169,.12);border-color:#0899A9;color:#0899A9}

/* Stories Grid */
.stories-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:20px}
.story-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);padding:22px;box-shadow:var(--shadow-sm);display:flex;flex-direction:column;justify-content:space-between;gap:16px;transition:all .25s var(--ease);position:relative}
.story-card:hover{border-color:var(--color-primary-light);transform:translateY(-3px);box-shadow:var(--shadow-md)}

.story-header{display:flex;justify-content:space-between;align-items:flex-start;gap:10px}
.story-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;background:rgba(8,153,169,0.1);color:#0899A9;font-size:11.5px;font-weight:700}
.story-status{font-size:11.5px;padding:3px 9px;border-radius:12px;font-weight:700}
.story-status.published{background:var(--success-12);color:var(--success)}
.story-status.draft{background:rgba(0,0,0,.08);color:var(--color-muted)}
:root[data-theme="dark"] .story-status.draft{background:rgba(255,255,255,.08)}

.story-title{font-size:16px;font-weight:800;color:var(--color-text);margin-top:8px;line-height:1.5}
.story-author-box{display:flex;align-items:center;gap:10px;margin-top:6px}
.story-avatar{width:38px;height:38px;border-radius:50%;background:rgba(8,153,169,.15);display:grid;place-items:center;font-weight:800;color:#0899A9;font-size:14px;flex-shrink:0;overflow:hidden}
.story-avatar img{width:100%;height:100%;object-fit:cover}
.story-author-name{font-weight:700;font-size:13.5px;color:var(--color-text)}
.story-author-role{font-size:11.5px;color:var(--color-muted)}

.story-excerpt{font-size:13px;color:var(--color-text);opacity:.85;line-height:1.8;background:var(--color-bg);padding:12px 14px;border-radius:12px;border:1px solid var(--color-border);font-style:italic}

.story-footer{display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--color-border);padding-top:14px;margin-top:4px}
.story-time{font-size:12px;color:var(--color-muted);display:flex;align-items:center;gap:4px}
.story-actions{display:flex;gap:6px}
.action-btn{width:32px;height:32px;border-radius:8px;display:grid;place-items:center;border:1px solid var(--color-border);background:var(--color-surface);color:var(--color-text);cursor:pointer;transition:all .15s}
.action-btn:hover{border-color:var(--color-primary);color:var(--color-primary);background:var(--color-bg)}
.action-btn.delete:hover{border-color:var(--danger);color:var(--danger)}
.action-btn svg{width:15px;height:15px}

/* Modal Overlay & Card */
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(6px);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px}
.modal-backdrop.open{display:flex;animation:fadeIn .2s ease forwards}
.modal-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius-lg);padding:28px;max-width:680px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,.3);position:relative}
.modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid var(--color-border);padding-bottom:14px}
.modal-head h3{font-size:18px;font-weight:800;display:flex;align-items:center;gap:8px}
.modal-close{background:none;border:none;cursor:pointer;color:var(--color-muted);padding:4px;border-radius:8px;display:grid;place-items:center}
.modal-close:hover{color:var(--danger)}

/* Form Elements */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1 / -1}
.form-group label{font-size:12.5px;font-weight:700;color:var(--color-text)}
.form-control{background:var(--color-bg);border:1px solid var(--color-border);border-radius:10px;padding:10px 14px;color:var(--color-text);font-family:inherit;font-size:13.5px;outline:none;transition:border-color .2s}
.form-control:focus{border-color:var(--color-primary)}
textarea.form-control{min-height:100px;resize:vertical;line-height:1.7}

.empty-state{text-align:center;padding:70px 20px;color:var(--color-muted);background:var(--color-surface);border-radius:var(--radius);border:1px solid var(--color-border)}
.empty-state svg{width:56px;height:56px;opacity:.3;margin-bottom:14px}

@keyframes fadeIn{from{opacity:0}to{opacity:1}}
</style>
</head>
<body>

<div class="wrap">
  <a href="index.php" class="nav-back">
    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    بازگشت به داشبورد
  </a>

  <!-- Head -->
  <div class="head-bar">
    <div class="head-info">
      <div class="head-ic">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
        </svg>
      </div>
      <div>
        <h1 style="font-size:24px;font-weight:900">بانک روایت‌های امید مکسا</h1>
        <p style="color:var(--color-muted);font-size:13px">مدیریت و انتشار داستان‌های ایستادگی، تجارب کادر درمان و همراهان بیماران (صفحه اصلی)</p>
      </div>
    </div>
    <div class="head-actions">
      <a href="/home" target="_blank" class="btn btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        مشاهده در سایت
      </a>
      <button type="button" class="btn btn-primary" onclick="openCreateModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        افزودن روایت جدید
      </button>
    </div>
  </div>

  <?php if ($FLASH): ?>
    <div class="flash <?= $FLASH['type'] === 'ok' ? 'ok' : 'err' ?>">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($FLASH['text']) ?>
    </div>
  <?php endif; ?>

  <!-- Stats Grid -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-ic teal">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
      </div>
      <div>
        <div class="stat-title">کل روایات ثبت‌شده</div>
        <div class="stat-val"><?= $totalCount ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-ic green">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div>
        <div class="stat-title">روایات فعال در صفحه اصلی</div>
        <div class="stat-val"><?= $publishedCount ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-ic gold">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 4H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
      </div>
      <div>
        <div class="stat-title">روایات کادر درمان</div>
        <div class="stat-val"><?= $staffCount ?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-ic purple">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div>
        <div class="stat-title">تجارب بیماران و همراهان</div>
        <div class="stat-val"><?= $patientCount ?></div>
      </div>
    </div>
  </div>

  <!-- Filter & Search Bar -->
  <div class="filter-card">
    <input type="text" id="searchInput" class="search-input" placeholder="جستجو بر اساس عنوان، راوی یا متن...">
    <div class="tag-filters">
      <button type="button" class="filter-btn active" data-filter="all">همه دسته‌ها</button>
      <button type="button" class="filter-btn" data-filter="کادر درمان">کادر درمان</button>
      <button type="button" class="filter-btn" data-filter="بهبودی">پس از بهبودی</button>
      <button type="button" class="filter-btn" data-filter="همراهان">همراهان</button>
      <button type="button" class="filter-btn" data-filter="درمان">مسیر درمان</button>
    </div>
  </div>

  <!-- Stories Grid -->
  <?php if (empty($stories)): ?>
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
      <h3>هیچ روایتی ثبت نشده است</h3>
      <p style="font-size:13px;margin-top:6px">برای نمایش روایات در صفحه اصلی، اولین روایت را ثبت نمایید.</p>
    </div>
  <?php else: ?>
    <div class="stories-grid" id="storiesGrid">
      <?php foreach ($stories as $story): ?>
        <div class="story-card" data-tag="<?= htmlspecialchars($story['tag']) ?>" data-search="<?= htmlspecialchars($story['title'] . ' ' . $story['narrator_name'] . ' ' . $story['excerpt']) ?>">
          <div>
            <div class="story-header">
              <span class="story-tag">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <?= htmlspecialchars($story['tag']) ?>
              </span>
              <span class="story-status <?= $story['status'] ?>">
                <?= $story['status'] === 'published' ? 'منتشر شده' : 'پیش‌نویس' ?>
              </span>
            </div>

            <h3 class="story-title"><?= htmlspecialchars($story['title']) ?></h3>

            <div class="story-author-box">
              <div class="story-avatar">
                <?php if (!empty($story['image'])): ?>
                  <img src="<?= htmlspecialchars($story['image']) ?>" alt="<?= htmlspecialchars($story['narrator_name']) ?>">
                <?php else: ?>
                  <?= mb_substr($story['narrator_name'], 0, 1, 'UTF-8') ?>
                <?php endif; ?>
              </div>
              <div>
                <div class="story-author-name"><?= htmlspecialchars($story['narrator_name']) ?></div>
                <div class="story-author-role"><?= htmlspecialchars($story['narrator_role'] ?: 'همراه مکسا') ?></div>
              </div>
            </div>

            <div class="story-excerpt" style="margin-top:14px">
              "<?= htmlspecialchars($story['excerpt']) ?>"
            </div>
          </div>

          <div class="story-footer">
            <div class="story-time">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= htmlspecialchars($story['read_time'] ?: '۴ دقیقه مطالعه') ?>
            </div>

            <div class="story-actions">
              <!-- View Full Content -->
              <button type="button" class="action-btn" title="مشاهده متن کامل" onclick='viewFullStory(<?= json_encode($story, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) ?>)'>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>

              <!-- Edit Story -->
              <button type="button" class="action-btn" title="ویرایش" onclick='openEditModal(<?= json_encode($story, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) ?>)'>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>

              <!-- Toggle Status Form -->
              <form method="POST" style="display:inline" onsubmit="return confirm('آیا از تغییر وضعیت انتشار این روایت اطمینان دارید؟');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="id" value="<?= $story['id'] ?>">
                <button type="submit" class="action-btn" title="<?= $story['status'] === 'published' ? 'تغییر به پیش‌نویس' : 'انتشار در سایت' ?>">
                  <?php if ($story['status'] === 'published'): ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--success)"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                  <?php else: ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  <?php endif; ?>
                </button>
              </form>

              <!-- Delete Story Form -->
              <form method="POST" style="display:inline" onsubmit="return confirm('آیا از حذف این روایت اطمینان دارید؟ این عملیات قابل بازگشت نیست.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $story['id'] ?>">
                <button type="submit" class="action-btn delete" title="حذف روایت">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Modal for Create / Edit Story -->
<div class="modal-backdrop" id="storyModal">
  <div class="modal-card">
    <div class="modal-head">
      <h3 id="modalTitle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px;height:20px;color:var(--color-primary)"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        افزودن روایت جدید
      </h3>
      <button type="button" class="modal-close" onclick="closeModal('storyModal')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <form method="POST" enctype="multipart/form-data" id="storyForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="formAction" value="create">
      <input type="hidden" name="id" id="storyId" value="">
      <input type="hidden" name="existing_image" id="existingImage" value="">

      <div class="form-grid">
        <div class="form-group full">
          <label>عنوان روایت (تیتر جذاب) <span style="color:var(--danger)">*</span></label>
          <input type="text" name="title" id="fTitle" class="form-control" placeholder="مثال: زندگی تا آخرین لحظه" required>
        </div>

        <div class="form-group">
          <label>نام شخص تجربه‌گر یا راوی <span style="color:var(--danger)">*</span></label>
          <input type="text" name="narrator_name" id="fNarrator" class="form-control" placeholder="مثال: دکتر زهرا جعفری یا مرجان" required>
        </div>

        <div class="form-group">
          <label>سمت / نقش / ارتباط با مکسا</label>
          <input type="text" name="narrator_role" id="fRole" class="form-control" placeholder="مثال: روانشناس مراقبت در منزل یا بهبودیافته">
        </div>

        <div class="form-group">
          <label>تگ و دسته‌بندی موضوعی <span style="color:var(--danger)">*</span></label>
          <select name="tag" id="fTag" class="form-control" onchange="toggleCustomTag(this.value)">
            <option value="روایت کادر درمان">روایت کادر درمان</option>
            <option value="زندگی پس از بهبودی">زندگی پس از بهبودی</option>
            <option value="زندگی پس از تشخیص بیماری">زندگی پس از تشخیص بیماری</option>
            <option value="مسیر درمان">مسیر درمان</option>
            <option value="تجربه همراهان">تجربه همراهان</option>
            <option value="امید به زندگی">امید به زندگی</option>
            <option value="custom">دسته‌بندی سفارشی...</option>
          </select>
        </div>

        <div class="form-group" id="customTagGroup" style="display:none">
          <label>عنوان دسته‌بندی سفارشی</label>
          <input type="text" name="custom_tag" id="fCustomTag" class="form-control" placeholder="نام دسته جدید">
        </div>

        <div class="form-group">
          <label>مدت زمان مطالعه تخمینی</label>
          <input type="text" name="read_time" id="fReadTime" class="form-control" placeholder="مثال: ۴ دقیقه مطالعه" value="۴ دقیقه مطالعه">
        </div>

        <div class="form-group">
          <label>وضعیت انتشار</label>
          <select name="status" id="fStatus" class="form-control">
            <option value="published">منتشر شده (نمایش در صفحه اصلی)</option>
            <option value="draft">پیش‌نویس (عدم نمایش)</option>
          </select>
        </div>

        <div class="form-group">
          <label>ترتیب نمایش (اولویت)</label>
          <input type="number" name="sort_order" id="fSortOrder" class="form-control" value="0">
        </div>

        <div class="form-group full">
          <label>تصویر یا آواتار راوی (اختیاری)</label>
          <input type="file" name="image" class="form-control" accept="image/*">
          <small style="color:var(--color-muted);font-size:11.5px;margin-top:2px">می‌توانید تصویر پرتره راوی یا تصویر متناسب با روایت را بارگذاری کنید.</small>
        </div>

        <div class="form-group full">
          <label>خلاصه کوتاه روایت (جهت نمایش در اسلایدر صفحه اصلی)</label>
          <textarea name="excerpt" id="fExcerpt" class="form-control" placeholder="خلاصه ۲ الی ۳ خطی که در کارت‌های متحرک صفحه اصلی نمایش داده می‌شود..."></textarea>
        </div>

        <div class="form-group full">
          <label>متن کامل روایت <span style="color:var(--danger)">*</span></label>
          <textarea name="content" id="fContent" class="form-control" style="min-height:140px" placeholder="متن کامل قصه ایستادگی و تجربه راوی..." required></textarea>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px;border-top:1px solid var(--color-border);padding-top:16px">
        <button type="button" class="btn btn-ghost" onclick="closeModal('storyModal')">انصراف</button>
        <button type="submit" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          ذخیره و ثبت روایت
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal for Viewing Full Story -->
<div class="modal-backdrop" id="viewModal">
  <div class="modal-card">
    <div class="modal-head">
      <h3 id="vTitle">عنوان روایت</h3>
      <button type="button" class="modal-close" onclick="closeModal('viewModal')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between">
      <div style="display:flex;align-items:center;gap:10px">
        <span class="story-tag" id="vTag">تگ</span>
        <span style="font-size:13px;color:var(--color-muted)" id="vReadTime">۴ دقیقه</span>
      </div>
      <div style="font-weight:700;color:var(--color-primary)" id="vNarrator">نام راوی</div>
    </div>

    <div style="background:var(--color-bg);padding:18px;border-radius:14px;border:1px solid var(--color-border);font-size:14px;line-height:2;color:var(--color-text);white-space:pre-wrap;" id="vContent">
    </div>

    <div style="display:flex;justify-content:flex-end;margin-top:20px">
      <button type="button" class="btn btn-ghost" onclick="closeModal('viewModal')">بستن</button>
    </div>
  </div>
</div>

<script>
function openCreateModal() {
  document.getElementById('formAction').value = 'create';
  document.getElementById('modalTitle').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px;height:20px;color:var(--color-primary)"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> افزودن روایت جدید';
  document.getElementById('storyId').value = '';
  document.getElementById('existingImage').value = '';
  document.getElementById('storyForm').reset();
  toggleCustomTag('روایت کادر درمان');
  document.getElementById('storyModal').classList.add('open');
}

function openEditModal(story) {
  document.getElementById('formAction').value = 'update';
  document.getElementById('modalTitle').innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:20px;height:20px;color:var(--color-primary)"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> ویرایش روایت';
  document.getElementById('storyId').value = story.id;
  document.getElementById('existingImage').value = story.image || '';
  document.getElementById('fTitle').value = story.title || '';
  document.getElementById('fNarrator').value = story.narrator_name || '';
  document.getElementById('fRole').value = story.narrator_role || '';
  
  const tagSelect = document.getElementById('fTag');
  let found = false;
  for (let i = 0; i < tagSelect.options.length; i++) {
    if (tagSelect.options[i].value === story.tag) {
      tagSelect.selectedIndex = i;
      found = true;
      break;
    }
  }
  if (!found) {
    tagSelect.value = 'custom';
    document.getElementById('fCustomTag').value = story.tag;
    toggleCustomTag('custom');
  } else {
    toggleCustomTag(story.tag);
  }

  document.getElementById('fReadTime').value = story.read_time || '۴ دقیقه مطالعه';
  document.getElementById('fStatus').value = story.status || 'published';
  document.getElementById('fSortOrder').value = story.sort_order || 0;
  document.getElementById('fExcerpt').value = story.excerpt || '';
  document.getElementById('fContent').value = story.content || '';

  document.getElementById('storyModal').classList.add('open');
}

function viewFullStory(story) {
  document.getElementById('vTitle').textContent = story.title;
  document.getElementById('vTag').textContent = story.tag;
  document.getElementById('vReadTime').textContent = story.read_time || '';
  document.getElementById('vNarrator').textContent = story.narrator_name + (story.narrator_role ? ' (' + story.narrator_role + ')' : '');
  document.getElementById('vContent').textContent = story.content;
  document.getElementById('viewModal').classList.add('open');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

function toggleCustomTag(val) {
  document.getElementById('customTagGroup').style.display = (val === 'custom') ? 'flex' : 'none';
}

// Search and Filter Logic
const searchInput = document.getElementById('searchInput');
const filterBtns = document.querySelectorAll('.filter-btn');
const cards = document.querySelectorAll('.story-card');

function filterStories() {
  const query = (searchInput.value || '').trim().toLowerCase();
  const activeBtn = document.querySelector('.filter-btn.active');
  const activeTag = activeBtn ? activeBtn.dataset.filter : 'all';

  cards.forEach(card => {
    const cardTag = card.dataset.tag || '';
    const cardSearch = (card.dataset.search || '').toLowerCase();

    const matchesTag = (activeTag === 'all') || cardTag.includes(activeTag);
    const matchesSearch = query === '' || cardSearch.includes(query);

    if (matchesTag && matchesSearch) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}

if (searchInput) {
  searchInput.addEventListener('input', filterStories);
}

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterStories();
  });
});

// Theme Synchronization with Admin Dashboard (maxa-theme key)
function applyMaxaTheme(){
  var d = false;
  try { d = localStorage.getItem('maxa-theme') === 'dark'; } catch(e){}
  if (d) {
    document.documentElement.setAttribute('data-theme', 'dark');
  } else {
    document.documentElement.removeAttribute('data-theme');
  }
}
applyMaxaTheme();
window.addEventListener('storage', function(e){
  if (!e || e.key === 'maxa-theme' || e.key === null) {
    applyMaxaTheme();
  }
});
</script>

</body>
</html>
