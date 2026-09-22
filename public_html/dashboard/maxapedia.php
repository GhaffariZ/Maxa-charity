<?php
/* ============================================================================
 *  مدیریت مکساپدیا — پنل مکسا
 *  محتوای هر یک از شش بخشِ مکساپدیا (ویدیوها، بروشورها، کتاب‌ها، پادکست‌ها،
 *  کلیپ‌ها، گالری) را مدیریت می‌کند. این صفحه هم نمایش و هم افزودن/حذف را
 *  در همین فایل انجام می‌دهد (الگوی POST→Redirect→GET). داده از maxapedia-db.php
 *
 *  دسترسی: فقط «ستاد مرکزی» (HQ). شعبه‌ها به مکساپدیا دسترسی ندارند.
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';
// فقط «ستاد مرکزی» و فقط مدیر مرکزی (یا کاربری که صریحاً دسترسیِ maxapedia دارد).
if (!dash_can_maxapedia()) {
    http_response_code(403);
    exit('۴۰۳ | دسترسی به مکساپدیا فقط برای مدیر مرکزی (یا کاربرِ دارای دسترسی) از «ستاد مرکزی» مجاز است.');
}
require __DIR__ . '/maxapedia-db.php';

/* بخشِ جاری (پیش‌فرض: اولین بخش) */
$sectionKeys = array_keys(MAXAPEDIA_SECTIONS);
$section = $_GET['section'] ?? ($_POST['section'] ?? $sectionKeys[0]);
if (!maxapedia_is_section($section)) { $section = $sectionKeys[0]; }

$notice      = $_GET['msg'] ?? '';
$errorNotice = $_GET['err'] ?? '';

/* بررسی سرریز شدن حجم کل ارسالی از post_max_size */
if (empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
  $post_max = ini_get('post_max_size');
  header('Location: maxapedia.php?section=' . urlencode($section) . '&msg=error&err=' . urlencode("حجم کل فایل‌های ارسالی بیشتر از سقف مجاز سرور است (حداکثر $post_max)."));
  exit;
}

/* توابع کمکی آپلود فایل کتاب و تصویر جلد */
if (!function_exists('maxapedia_upload_book_file')) {
  function maxapedia_upload_book_file(array $file): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
      switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          $max = ini_get('upload_max_filesize');
          throw new Exception("حجم فایل کتاب بیشتر از سقف مجاز سرور است (حداکثر $max).");
        case UPLOAD_ERR_PARTIAL:
          throw new Exception("آپلود فایل کتاب ناقص ماند. لطفاً دوباره تلاش کنید.");
        default:
          throw new Exception("خطا در آپلود فایل کتاب (کد خطا: {$file['error']}).");
      }
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'epub', 'mobi', 'doc', 'docx', 'zip', 'rar'];
    if (!in_array($ext, $allowed, true)) {
      throw new Exception("فرمت فایل کتاب نامعتبر است. فرمت‌های مجاز: " . implode(', ', $allowed));
    }

    $uploadDir = dirname(__DIR__) . '/uploads/books';
    if (!is_dir($uploadDir)) {
      @mkdir($uploadDir, 0775, true);
    }

    $safeName = 'book_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
      throw new Exception("امکان ذخیره فایل کتاب در پوشه uploads وجود ندارد. لطفاً دسترسی پوشه را بررسی کنید.");
    }

    return '/uploads/books/' . $safeName;
  }
}

if (!function_exists('maxapedia_upload_cover_file')) {
  function maxapedia_upload_cover_file(array $file): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
      return '';
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed, true)) {
      throw new Exception("فرمت تصویر جلد نامعتبر است. فرمت‌های مجاز: jpg, png, webp");
    }

    $uploadDir = dirname(__DIR__) . '/uploads/books/covers';
    if (!is_dir($uploadDir)) {
      @mkdir($uploadDir, 0775, true);
    }

    $safeName = 'cover_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . '/' . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
      throw new Exception("امکان ذخیره تصویر جلد کتاب وجود ندارد.");
    }

    return '/uploads/books/covers/' . $safeName;
  }
}

/* ---------- پردازش POST (افزودن / ویرایش / حذف) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
  $action   = $_POST['action'] ?? '';
  $errorMsg = null;
  $msg      = '';

  try {
    if ($action === 'create') {
      $url       = trim((string)($_POST['url'] ?? ''));
      $thumbnail = trim((string)($_POST['thumbnail'] ?? ''));

      // ۱. آپلود فایل کتاب (ویژه بخش کتاب‌ها)
      if ($section === 'books' && isset($_FILES['book_file']) && $_FILES['book_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $url = maxapedia_upload_book_file($_FILES['book_file']);
      } else {
        $url = maxapedia_extract_url($url);
      }

      // ۲. آپلود تصویر جلد (اختیاری)
      if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadedCover = maxapedia_upload_cover_file($_FILES['thumbnail_file']);
        if ($uploadedCover !== '') {
          $thumbnail = $uploadedCover;
        }
      }

      $title = trim((string)($_POST['title'] ?? ''));
      if ($title === '') {
        throw new Exception("عنوان محتوا الزامی است.");
      }

      maxapedia_create($pdo, [
        'section'     => $section,
        'category'    => trim((string)($_POST['category'] ?? '')),
        'title'       => $title,
        'description' => trim((string)($_POST['description'] ?? '')),
        'url'         => $url,
        'thumbnail'   => $thumbnail,
        'status'      => $_POST['status'] ?? 'published',
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
      ]);
      $msg = 'created';

    } elseif ($action === 'update') {
      $id = (int)($_POST['id'] ?? 0);
      $existing = maxapedia_find($pdo, $id);
      if (!$existing) {
        throw new Exception("محتوای مورد نظر برای ویرایش یافت نشد.");
      }

      $url       = $existing['url'];
      $thumbnail = $existing['thumbnail'];

      // آیا فایل کتاب جدیدی انتخاب شده؟
      if ($section === 'books' && isset($_FILES['book_file']) && $_FILES['book_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $newBookUrl = maxapedia_upload_book_file($_FILES['book_file']);
        // حذف فایل قدیمی در صورت وجود
        if (!empty($existing['url']) && strpos($existing['url'], '/uploads/books/') === 0) {
          $oldF = dirname(__DIR__) . $existing['url'];
          if (is_file($oldF)) { @unlink($oldF); }
        }
        $url = $newBookUrl;
      } elseif (isset($_POST['url']) && trim($_POST['url']) !== '') {
        $url = maxapedia_extract_url((string)$_POST['url']);
      }

      // آیا تصویر جلد جدیدی آپلود شده؟
      if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $newCoverUrl = maxapedia_upload_cover_file($_FILES['thumbnail_file']);
        if ($newCoverUrl !== '') {
          if (!empty($existing['thumbnail']) && strpos($existing['thumbnail'], '/uploads/books/covers/') === 0) {
            $oldC = dirname(__DIR__) . $existing['thumbnail'];
            if (is_file($oldC)) { @unlink($oldC); }
          }
          $thumbnail = $newCoverUrl;
        }
      } elseif (isset($_POST['thumbnail']) && trim($_POST['thumbnail']) !== '') {
        $thumbnail = trim($_POST['thumbnail']);
      }

      $title = trim((string)($_POST['title'] ?? ''));
      if ($title === '') {
        throw new Exception("عنوان محتوا الزامی است.");
      }

      maxapedia_update($pdo, $id, [
        'category'    => trim((string)($_POST['category'] ?? '')),
        'title'       => $title,
        'description' => trim((string)($_POST['description'] ?? '')),
        'url'         => $url,
        'thumbnail'   => $thumbnail,
        'status'      => $_POST['status'] ?? 'published',
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
      ]);
      $msg = 'updated';

    } elseif ($action === 'delete') {
      $delId = (int)($_POST['id'] ?? 0);
      $existing = maxapedia_find($pdo, $delId);
      if ($existing) {
        if (!empty($existing['url']) && strpos($existing['url'], '/uploads/books/') === 0) {
          $oldF = dirname(__DIR__) . $existing['url'];
          if (is_file($oldF)) { @unlink($oldF); }
        }
        if (!empty($existing['thumbnail']) && strpos($existing['thumbnail'], '/uploads/books/covers/') === 0) {
          $oldC = dirname(__DIR__) . $existing['thumbnail'];
          if (is_file($oldC)) { @unlink($oldC); }
        }
      }
      maxapedia_delete($pdo, $delId);
      $msg = 'deleted';
    }
  } catch (Throwable $e) {
    $errorMsg = $e->getMessage();
    $msg = 'error';
  }

  // PRG: جلوگیری از ارسال مجدد فرم
  $redir = 'maxapedia.php?section=' . urlencode($section) . ($msg ? '&msg=' . $msg : '') . ($errorMsg ? '&err=' . urlencode($errorMsg) : '');
  header('Location: ' . $redir);
  exit;
}

/* ---------- فیلترها (جستجو + دسته‌بندی) ---------- */
$q         = trim((string)($_GET['q'] ?? ''));
$catFilter = trim((string)($_GET['cat'] ?? ''));

/* ---------- بررسی حالت ویرایش ---------- */
$editId    = (int)($_GET['edit'] ?? 0);
$editItem  = ($editId > 0 && $pdo) ? maxapedia_find($pdo, $editId) : null;
$isEditing = ($editItem !== null && $editItem['section'] === $section);
if ($editId > 0 && !$isEditing) {
  $editItem = null;
  $editId   = 0;
}

/* ---------- داده‌ها برای نمایش ---------- */
$allCategories = ($pdo) ? maxapedia_categories($pdo, $section) : [];
/* فهرستِ انتخابِ دسته در فرمِ افزودن = پیش‌فرض‌ها + دسته‌های ساخته‌شده (یکتا) */
$catOptions = array_values(array_unique(array_merge(MAXAPEDIA_DEFAULT_CATEGORIES, $allCategories)));
$items  = ($pdo) ? maxapedia_items($pdo, $section, false, ['category' => $catFilter, 'q' => $q]) : [];
$counts = ($pdo) ? maxapedia_counts($pdo) : [];
$meta   = maxapedia_section_meta($section);
$isFiltered = ($q !== '' || $catFilter !== '');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>مکساپدیا | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --success-12:rgba(22,163,122,.14); --danger-12:rgba(224,85,107,.12);
  --radius-sm:12px; --radius:18px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16);
  --success-12:rgba(22,163,122,.18); --danger-12:rgba(224,85,107,.16);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}
*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.20);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);background-clip:padding-box}

.mx-wrap{max-width:1040px;margin:0 auto}

.mx-head{display:flex;align-items:center;gap:16px;margin-bottom:22px}
.mx-head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;font-size:26px;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.mx-head h1{font-size:22px;font-weight:800;letter-spacing:-.01em}
.mx-head p{font-size:13px;color:var(--color-muted);margin-top:3px}

/* تب‌های بخش (کارت‌های مکساپدیا) */
.mx-tabs{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:24px}
@media(max-width:880px){.mx-tabs{grid-template-columns:repeat(3,1fr)}}
@media(max-width:520px){.mx-tabs{grid-template-columns:repeat(2,1fr)}}
.mx-tab{font-family:inherit;text-align:center;cursor:pointer;text-decoration:none;color:inherit;background:var(--color-surface);
  border:1.5px solid var(--color-border);border-radius:16px;padding:14px 10px;box-shadow:var(--shadow-sm);
  display:flex;flex-direction:column;align-items:center;gap:6px;transition:border-color .2s,box-shadow .25s,transform .15s var(--ease)}
.mx-tab:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.mx-tab.active{border-color:var(--color-primary);background:var(--primary-08)}
.mx-tab .em{font-size:24px;line-height:1;display:flex;align-items:center;justify-content:center}
.mx-tab .em .iconoir-icon{width:26px;height:26px;stroke-width:1.6;color:var(--color-primary)}
.mx-tab .lb{font-size:12.5px;font-weight:700}
.mx-tab .cnt{font-size:11px;color:var(--color-muted)}

.mx-grid{display:grid;grid-template-columns:380px 1fr;gap:22px;align-items:start}
@media(max-width:860px){.mx-grid{grid-template-columns:1fr}}

/* فرم افزودن */
.mx-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:18px;box-shadow:var(--shadow-sm);padding:22px}
.mx-card h2{font-size:15px;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.mx-field{margin-bottom:14px}
.mx-field label{display:block;font-size:12.5px;font-weight:700;margin-bottom:6px;color:var(--color-text)}
.mx-field input,.mx-field textarea,.mx-field select{width:100%;font-family:inherit;font-size:13.5px;color:var(--color-text);
  background:var(--color-bg);border:1.5px solid var(--color-border);border-radius:12px;padding:10px 12px;transition:border-color .2s}
.mx-field input:focus,.mx-field textarea:focus,.mx-field select:focus{outline:none;border-color:var(--color-primary)}
.mx-field textarea{resize:vertical;min-height:80px}
.mx-btn{width:100%;font-family:inherit;font-size:14px;font-weight:700;cursor:pointer;border:none;border-radius:12px;padding:12px;
  color:#fff;background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 10px 20px -10px rgba(0,123,122,.6);transition:transform .15s,box-shadow .2s}
.mx-btn:hover{transform:translateY(-2px)}

/* لیست محتوا */
.mx-list-head{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.mx-list-head h2{font-size:15px;font-weight:800}
.mx-count-pill{font-size:11.5px;font-weight:700;color:var(--color-primary);background:var(--primary-12);padding:3px 10px;border-radius:99px}
.mx-empty{background:var(--color-surface);border:1px dashed var(--color-border);border-radius:18px;padding:48px 24px;text-align:center;color:var(--color-muted)}

/* نوار ابزار جستجو/فیلتر */
.mx-toolbar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:16px}
.mx-toolbar input[type="search"]{flex:1;min-width:160px;font-family:inherit;font-size:13px;color:var(--color-text);
  background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:10px;padding:9px 12px}
.mx-toolbar select{font-family:inherit;font-size:13px;color:var(--color-text);
  background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:10px;padding:9px 12px;cursor:pointer}
.mx-toolbar input:focus,.mx-toolbar select:focus{outline:none;border-color:var(--color-primary)}
.mx-toolbar-btn{font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;border:none;border-radius:10px;padding:9px 16px;
  color:#fff;background:var(--color-primary)}
.mx-toolbar-clear{font-size:12.5px;font-weight:700;color:var(--danger);text-decoration:none;padding:0 6px}
.mx-toolbar-clear:hover{text-decoration:underline}

/* ردیف‌های فشرده‌ی فهرست (به‌جای کارت‌های بزرگ) */
.mx-list{background:var(--color-surface);border:1px solid var(--color-border);border-radius:14px;box-shadow:var(--shadow-sm);overflow:hidden}
.mx-row{display:flex;align-items:center;gap:12px;padding:10px 14px;border-bottom:1px solid var(--color-border)}
.mx-row:last-child{border-bottom:none}
.mx-row:hover{background:var(--primary-08)}
.mx-row-thumb{width:72px;height:42px;border-radius:8px;flex-shrink:0;object-fit:cover;background:var(--primary-08)}
.mx-row-thumb--icon{display:grid;place-items:center;font-size:20px}
.mx-row-main{flex:1;min-width:0}
.mx-row-title{font-size:13.5px;font-weight:700;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mx-row-tags{display:flex;flex-wrap:wrap;gap:6px;align-items:center;font-size:11px}
.mx-chip{padding:2px 8px;border-radius:99px;font-weight:700;background:var(--color-bg);border:1px solid var(--color-border);color:var(--color-text)}
.mx-row-date{color:var(--color-muted)}
.mx-row-actions{display:flex;align-items:center;gap:8px;flex-shrink:0}
.mx-row-actions .mx-item-link{display:grid;place-items:center;width:30px;height:30px;border-radius:8px;background:var(--primary-08);font-size:15px}
.mx-item{background:var(--color-surface);border:1px solid var(--color-border);border-radius:16px;box-shadow:var(--shadow-sm);
  padding:16px 18px;margin-bottom:12px;display:flex;gap:14px;align-items:flex-start}
.mx-item-thumb{width:64px;height:64px;border-radius:12px;flex-shrink:0;object-fit:cover;background:var(--primary-08);display:grid;place-items:center;font-size:26px}
.mx-item-body{flex:1;min-width:0}
.mx-item-title{font-size:14.5px;font-weight:800;margin-bottom:4px}
.mx-item-desc{font-size:12.5px;color:var(--color-muted);line-height:1.8;margin-bottom:6px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.mx-item-meta{display:flex;flex-wrap:wrap;gap:8px;align-items:center;font-size:11.5px}
.mx-badge{padding:3px 9px;border-radius:99px;font-weight:700}
.mx-badge.pub{background:var(--success-12);color:var(--success)}
.mx-badge.draft{background:var(--primary-12);color:var(--color-primary)}
.mx-item-link{color:var(--color-primary);text-decoration:none;font-weight:700}
.mx-item-link:hover{text-decoration:underline}
.mx-del{font-family:inherit;font-size:12.5px;font-weight:700;cursor:pointer;border:1.5px solid var(--color-border);background:transparent;
  color:var(--danger);border-radius:10px;padding:7px 12px;transition:background .2s,border-color .2s}
.mx-del:hover{background:var(--danger-12);border-color:var(--danger)}

.mx-hint{font-size:11.5px;color:var(--color-muted);margin-top:6px;line-height:1.7}
.mx-embed-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;font-weight:700;background:var(--primary-12);color:var(--color-primary)}

/* پیش‌نمایش امبد در داشبورد (هم‌کلاس با صفحه‌ی عمومی) */
.mx-embed{margin-top:12px;border-radius:12px;overflow:hidden;border:1px solid var(--color-border)}
.mxp-embed{width:100%;background:#000}
.mxp-embed--video{position:relative;width:100%;max-width:360px;aspect-ratio:16/9}
.mxp-embed--video>iframe,.mxp-embed--video>video{position:absolute;inset:0;width:100%;height:100%;border:0;display:block}
.mxp-embed--audio{background:var(--color-bg);padding:12px 14px}
.mxp-embed--audio>audio{width:100%;display:block}
.mxp-embed--audio>iframe{width:100%;height:160px;border:0;display:block}

.mx-notice{border-radius:12px;padding:11px 16px;margin-bottom:18px;font-size:13px;font-weight:700}
.mx-notice.ok{background:var(--success-12);color:var(--success)}
.mx-notice.err{background:var(--danger-12);color:var(--danger)}
.mx-err{background:var(--danger-12);color:var(--danger);border-radius:12px;padding:12px 16px;margin-bottom:18px;font-size:13px}

/* وضعیت در حال ویرایش */
.mx-card--editing {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 2px var(--primary-12), var(--shadow-md);
}

/* آپلود فایل و دراپ‌زون کتاب */
.mx-dropzone {
  border: 2px dashed var(--color-border);
  border-radius: 14px;
  padding: 22px 16px;
  text-align: center;
  background: var(--color-bg);
  cursor: pointer;
  transition: border-color .2s, background .2s;
  position: relative;
}
.mx-dropzone:hover, .mx-dropzone.dragover {
  border-color: var(--color-primary);
  background: var(--primary-08);
}
.mx-dropzone input[type="file"] {
  position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;
}
.mx-dropzone-icon { font-size: 32px; margin-bottom: 6px; }
.mx-dropzone-title { font-size: 13px; font-weight: 700; color: var(--color-text); }
.mx-dropzone-hint { font-size: 11.5px; color: var(--color-muted); margin-top: 4px; }
.mx-file-info {
  display: none; align-items: center; justify-content: space-between; gap: 10px;
  margin-top: 10px; padding: 10px 14px; background: var(--primary-12);
  border: 1px solid var(--color-primary-light); border-radius: 10px; font-size: 12.5px;
}
.mx-file-info.active { display: flex; }
.mx-file-name { font-weight: 700; color: var(--color-primary-dark); word-break: break-all; }
.mx-file-size { font-size: 11.5px; color: var(--color-muted); margin-right: 6px; }
.mx-file-clear {
  background: transparent; border: none; color: var(--danger); font-size: 18px;
  font-weight: 700; cursor: pointer; padding: 0 6px; line-height: 1;
}

/* جلد کتاب (آپلود عکس یا لینک) */
.mx-cover-preview {
  display: none; margin-top: 10px; width: 90px; height: 120px; border-radius: 8px;
  object-fit: cover; border: 1.5px solid var(--color-border); box-shadow: var(--shadow-sm);
}
.mx-cover-preview.active { display: block; }
.mx-current-file {
  display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 12px;
  background: var(--primary-08); border: 1px solid var(--primary-12);
  padding: 8px 12px; border-radius: 10px; margin-top: 8px; color: var(--color-primary);
}
.mx-current-file a { color: inherit; font-weight: 700; text-decoration: underline; }

/* دکمه‌های فرم در حالت ویرایش */
.mx-btn-group { display: flex; gap: 10px; align-items: center; }
.mx-btn-cancel {
  display: inline-flex; align-items: center; justify-content: center;
  font-family: inherit; font-size: 13px; font-weight: 700; text-decoration: none;
  padding: 12px 18px; border-radius: 12px; color: var(--color-text);
  background: var(--color-bg); border: 1.5px solid var(--color-border);
  transition: background .2s, border-color .2s;
}
.mx-btn-cancel:hover { background: var(--color-border); }
.mx-badge-format {
  font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 6px;
  background: rgba(0, 123, 122, 0.15); color: var(--color-primary-dark);
}
.mx-action-edit {
  display: grid; place-items: center; width: 30px; height: 30px; border-radius: 8px;
  background: var(--color-bg); border: 1px solid var(--color-border);
  color: var(--color-text); text-decoration: none; font-size: 13px; transition: border-color .2s;
}
.mx-action-edit:hover { border-color: var(--color-primary); color: var(--color-primary); }
.mx-row-thumb--book {
  width: 46px;
  height: 60px;
  border-radius: 6px;
  object-fit: cover;
}
</style>
</head>
<body>
<div class="mx-wrap">

  <div class="mx-head">
    <div class="mx-head-ic"><?= iconoir('book', '', 26) ?></div>
    <div>
      <h1>مکساپدیا</h1>
      <p>محتوای آموزشی هر بخش از مکساپدیا را اینجا اضافه و مدیریت کنید.</p>
    </div>
  </div>

  <?php if (!empty($dbError)): ?>
    <div class="mx-err">خطا در اتصال به دیتابیس: <?= e($dbError) ?></div>
  <?php endif; ?>

  <?php if ($notice === 'created'): ?>
    <div class="mx-notice ok">محتوای جدید با موفقیت افزوده شد.</div>
  <?php elseif ($notice === 'updated'): ?>
    <div class="mx-notice ok">تغییرات با موفقیت ذخیره شد.</div>
  <?php elseif ($notice === 'deleted'): ?>
    <div class="mx-notice ok">محتوا حذف شد.</div>
  <?php elseif ($notice === 'error'): ?>
    <div class="mx-notice err"><?= !empty($errorNotice) ? e($errorNotice) : 'خطا در انجام عملیات.' ?></div>
  <?php endif; ?>

  <!-- تب‌های شش‌گانه -->
  <div class="mx-tabs">
    <?php foreach (MAXAPEDIA_SECTIONS as $key => $m): ?>
      <a class="mx-tab <?= $key === $section ? 'active' : '' ?>" href="maxapedia.php?section=<?= e($key) ?>">
        <span class="em"><?= $m['icon'] ?></span>
        <span class="lb"><?= e($m['title']) ?></span>
        <span class="cnt"><?= fa_digits($counts[$key] ?? 0) ?> مورد</span>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="mx-grid">

    <!-- فرم افزودن / ویرایش محتوا -->
    <form class="mx-card <?= $isEditing ? 'mx-card--editing' : '' ?>" method="post" action="maxapedia.php?section=<?= e($section) ?>" enctype="multipart/form-data">
      <h2><?= $meta['icon'] ?> <?= $isEditing ? 'ویرایش «' . e($editItem['title']) . '»' : 'افزودن به «' . e($meta['title']) . '»' ?></h2>
      <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'create' ?>">
      <input type="hidden" name="section" value="<?= e($section) ?>">
      <?php if ($isEditing): ?>
        <input type="hidden" name="id" value="<?= (int)$editItem['id'] ?>">
      <?php endif; ?>

      <div class="mx-field">
        <label>عنوان <span style="color:var(--danger)">*</span></label>
        <input type="text" name="title" maxlength="255" required value="<?= e($isEditing ? ($editItem['title'] ?? '') : '') ?>" placeholder="مثلاً: آشنایی با بیماری‌های نادر">
      </div>

      <div class="mx-field">
        <label>دسته‌بندی</label>
        <input type="text" name="category" maxlength="120" list="mx-cat-list"
               value="<?= e($isEditing ? ($editItem['category'] ?? '') : $catFilter) ?>" placeholder="انتخاب از فهرست یا تعریف دسته‌ی جدید…">
        <datalist id="mx-cat-list">
          <?php foreach ($catOptions as $c): ?>
            <option value="<?= e($c) ?>"></option>
          <?php endforeach; ?>
        </datalist>
        <p class="mx-hint">یک دسته‌ی موجود را انتخاب کنید یا برای ساخت دسته‌ی جدید، نام آن را تایپ کنید.</p>
      </div>

      <div class="mx-field">
        <label>توضیح کوتاه</label>
        <textarea name="description" maxlength="2000" placeholder="توضیح مختصر درباره این محتوا…"><?= e($isEditing ? ($editItem['description'] ?? '') : '') ?></textarea>
      </div>

      <?php if ($section === 'books'): ?>
        <!-- بخش اختصاصی آپلود کتاب -->
        <div class="mx-field">
          <label>آپلود فایل کتاب (PDF / EPUB / MOBI / ZIP و...)</label>
          <div class="mx-dropzone" id="bookDropzone">
            <input type="file" name="book_file" id="bookFileInput" accept=".pdf,.epub,.mobi,.doc,.docx,.zip,.rar">
            <div class="mx-dropzone-icon"><?= iconoir('download', '', 36) ?></div>
            <div class="mx-dropzone-title">فایل کتاب را بکشید و اینجا رها کنید یا برای انتخاب کلیک کنید</div>
            <div class="mx-dropzone-hint">فرمت‌های مجاز: PDF, EPUB, MOBI, DOC, DOCX, ZIP, RAR (حداکثر <?= ini_get('upload_max_filesize') ?>)</div>
          </div>
          <div class="mx-file-info" id="bookFileInfo">
            <div>
              <span class="mx-file-name" id="bookFileName"></span>
              <span class="mx-file-size" id="bookFileSize"></span>
            </div>
            <button type="button" class="mx-file-clear" id="bookFileClear" title="حذف انتخاب">&times;</button>
          </div>
          <?php if ($isEditing && !empty($editItem['url'])): ?>
            <div class="mx-current-file">
              <span><?= iconoir('notes', '', 15) ?> فایل/لینک فعلی:</span>
              <a href="<?= e($editItem['url']) ?>" target="_blank" rel="noopener">مشاهده / دانلود کتاب</a>
              <?php if (strpos($editItem['url'], '/uploads/books/') === 0): ?>
                <span class="mx-badge-format">فایل ذخیره‌شده در سرور</span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="mx-field">
          <label>یا لینکِ مطالعه / دانلود مستقیم کتاب (اختیاری در صورت آپلود فایل)</label>
          <input type="text" name="url" value="<?= e($isEditing ? ($editItem['url'] ?? '') : '') ?>" placeholder="https://… (اگر فایل کتاب را آپلود کردید، این فیلد را خالی بگذارید)">
          <p class="mx-hint">اگر فایل کتاب را در کادر بالا آپلود کنید، این آدرس به‌طور خودکار تنظیم می‌شود. در صورت تمایل به قرار دادن لینک خارجی، آن را اینجا بنویسید.</p>
        </div>

        <div class="mx-field">
          <label>تصویر جلد کتاب</label>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <input type="file" name="thumbnail_file" id="coverFileInput" accept="image/jpeg,image/png,image/webp,image/gif">
            <input type="url" name="thumbnail" id="coverUrlInput" value="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" placeholder="یا آدرس اینترنتی تصویر جلد (https://…)">
          </div>
          <img id="coverPreview" class="mx-cover-preview <?= ($isEditing && !empty($editItem['thumbnail'])) ? 'active' : '' ?>" src="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" alt="پیش‌نمایش جلد">
          <p class="mx-hint">می‌توانید تصویر جلد را مستقیماً آپلود کنید یا آدرس اینترنتی آن را وارد کنید (پیشنهاد: پرتره عمودی با نسبت ۳:۴).</p>
        </div>

      <?php else: ?>
        <!-- بخش‌های غیرکتابی (ویدیو، پادکست، بروشور، کلیپ، گالری) -->
        <div class="mx-field">
          <label>لینک یا کدِ امبدِ محتوا (ویدیو / صوت / فایل)</label>
          <textarea name="url" maxlength="6000" rows="3" placeholder="https://…  یا کلِ کدِ امبد (مثلاً &lt;iframe …&gt;…&lt;/iframe&gt;)"><?= e($isEditing ? ($editItem['url'] ?? '') : '') ?></textarea>
          <p class="mx-hint">می‌توانید لینکِ معمولیِ یوتیوب، آپارات، نماشا، کست‌باکس، اسپاتیفای، ساندکلاد یا فایل مستقیم (mp4/mp3) را بگذارید — یا کلِ کدِ امبدی که این سرویس‌ها می‌دهند (iframe یا اسکریپتِ آپارات) را همین‌جا بچسبانید؛ آدرسِ پخش به‌صورت خودکار استخراج می‌شود.</p>
        </div>

        <div class="mx-field">
          <label>تصویر شاخص (لینک)</label>
          <input type="url" name="thumbnail" maxlength="1024" value="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" placeholder="https://… (اختیاری)">
        </div>
      <?php endif; ?>

      <div class="mx-field">
        <label>وضعیت</label>
        <select name="status">
          <option value="published" <?= ($isEditing && ($editItem['status'] ?? '') === 'published') ? 'selected' : '' ?>>منتشرشده</option>
          <option value="draft" <?= ($isEditing && ($editItem['status'] ?? '') === 'draft') ? 'selected' : '' ?>>پیش‌نویس</option>
        </select>
      </div>

      <div class="mx-field">
        <label>ترتیب نمایش</label>
        <input type="number" name="sort_order" value="<?= (int)($isEditing ? ($editItem['sort_order'] ?? 0) : 0) ?>">
      </div>

      <?php if ($isEditing): ?>
        <div class="mx-btn-group">
          <button type="submit" class="mx-btn" style="flex:1">ذخیره تغییرات</button>
          <a href="maxapedia.php?section=<?= e($section) ?>" class="mx-btn-cancel">انصراف</a>
        </div>
      <?php else: ?>
        <button type="submit" class="mx-btn">افزودن محتوا</button>
      <?php endif; ?>
    </form>

    <!-- لیست محتوای بخش -->
    <div>
      <div class="mx-list-head">
        <h2>محتوای «<?= e($meta['title']) ?>»</h2>
        <span class="mx-count-pill"><?= fa_digits(count($items)) ?> مورد</span>
      </div>

      <!-- نوار ابزار: جستجو + فیلتر دسته‌بندی -->
      <form class="mx-toolbar" method="get" action="maxapedia.php">
        <input type="hidden" name="section" value="<?= e($section) ?>">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="جستجو در عنوان، توضیح یا دسته…">
        <select name="cat" onchange="this.form.submit()">
          <option value="">همه‌ی دسته‌ها</option>
          <?php foreach ($allCategories as $c): ?>
            <option value="<?= e($c) ?>"<?= $c === $catFilter ? ' selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="mx-toolbar-btn">جستجو</button>
        <?php if ($isFiltered): ?>
          <a class="mx-toolbar-clear" href="maxapedia.php?section=<?= e($section) ?>">پاک کردن</a>
        <?php endif; ?>
      </form>

      <?php if (empty($items)): ?>
        <div class="mx-empty">
          <?php if ($isFiltered): ?>
            موردی با این فیلتر/جستجو پیدا نشد.
          <?php else: ?>
            هنوز محتوایی برای این بخش ثبت نشده است. از فرمِ کنار صفحه اولین مورد را اضافه کنید.
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="mx-list">
          <?php foreach ($items as $it):
            $emInfo = maxapedia_embed((string)($it['url'] ?? ''));
          ?>
            <div class="mx-row">
              <?php if (!empty($it['thumbnail'])): ?>
                <img class="mx-row-thumb <?= $section === 'books' ? 'mx-row-thumb--book' : '' ?>" src="<?= e($it['thumbnail']) ?>" alt="">
              <?php elseif ($emInfo && !empty($emInfo['poster'])): ?>
                <img class="mx-row-thumb" src="<?= e($emInfo['poster']) ?>" alt="">
              <?php else: ?>
                <div class="mx-row-thumb <?= $section === 'books' ? 'mx-row-thumb--book' : '' ?> mx-row-thumb--icon"><?= $meta['icon'] ?></div>
              <?php endif; ?>

              <div class="mx-row-main">
                <div class="mx-row-title"><?= e($it['title']) ?></div>
                <div class="mx-row-tags">
                  <?php if (!empty($it['category'])): ?>
                    <span class="mx-chip"><?= iconoir('label', '', 13) ?> <?= e($it['category']) ?></span>
                  <?php endif; ?>
                  <?php if ($emInfo): ?>
                    <span class="mx-embed-badge">▶ <?= e($emInfo['provider']) ?></span>
                  <?php endif; ?>
                  <?php
                  if ($section === 'books' && !empty($it['url'])) {
                    $ext = strtolower(pathinfo(parse_url($it['url'], PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                    if ($ext) {
                      echo '<span class="mx-badge-format">' . iconoir('notes', '', 13) . ' ' . strtoupper(e($ext)) . '</span>';
                    } elseif (strpos($it['url'], 'drive.google.com') !== false) {
                      echo '<span class="mx-badge-format">Google Drive</span>';
                    }
                  }
                  ?>
                  <span class="mx-badge <?= $it['status']==='published'?'pub':'draft' ?>">
                    <?= $it['status']==='published'?'منتشرشده':'پیش‌نویس' ?>
                  </span>
                  <span class="mx-row-date"><?= jalali_date($it['created_at']) ?></span>
                </div>
              </div>

              <div class="mx-row-actions">
                <a class="mx-action-edit" href="maxapedia.php?section=<?= e($section) ?>&edit=<?= (int)$it['id'] ?>" title="ویرایش"><?= iconoir('edit-pencil', '', 15) ?></a>
                <?php if (!empty($it['url'])): ?>
                  <a class="mx-item-link" href="<?= e($it['url']) ?>" target="_blank" rel="noopener" title="<?= $section === 'books' ? 'دانلود یا مطالعه کتاب' : 'باز کردن لینک' ?>">
                    <?= $section === 'books' ? iconoir('download', '', 15) : iconoir('arrow-up-right', '', 15) ?>
                  </a>
                <?php endif; ?>
                <form method="post" action="maxapedia.php?section=<?= e($section) ?>" onsubmit="return confirm('این محتوا حذف شود؟');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="section" value="<?= e($section) ?>">
                  <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                  <button type="submit" class="mx-del">حذف</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
/* همگام‌سازی تم تیره با بقیه‌ی پنل */
window.addEventListener('storage',function(e){
  if(e.key==='maxa-theme'){
    document.documentElement.setAttribute('data-theme', e.newValue==='dark'?'dark':'light');
  }
});

/* دراگ و دراپ و پیش‌نمایش فایل برای بخش کتاب‌ها */
(function() {
  var dropzone  = document.getElementById('bookDropzone');
  var fileInput = document.getElementById('bookFileInput');
  var fileInfo  = document.getElementById('bookFileInfo');
  var fileName  = document.getElementById('bookFileName');
  var fileSize  = document.getElementById('bookFileSize');
  var fileClear = document.getElementById('bookFileClear');

  if (dropzone && fileInput) {
    ['dragenter', 'dragover'].forEach(function(evt) {
      dropzone.addEventListener(evt, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add('dragover');
      });
    });

    ['dragleave', 'drop'].forEach(function(evt) {
      dropzone.addEventListener(evt, function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove('dragover');
      });
    });

    dropzone.addEventListener('drop', function(e) {
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFileChange();
      }
    });

    fileInput.addEventListener('change', handleFileChange);

    function formatBytes(bytes) {
      if (!bytes || bytes === 0) return '0 Bytes';
      var k = 1024;
      var sizes = ['Bytes', 'KB', 'MB', 'GB'];
      var i = Math.floor(Math.log(bytes) / Math.log(k));
      return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
    }

    function handleFileChange() {
      if (fileInput.files && fileInput.files[0]) {
        var f = fileInput.files[0];
        if (fileName) fileName.textContent = f.name;
        if (fileSize) fileSize.textContent = '(' + formatBytes(f.size) + ')';
        if (fileInfo) fileInfo.classList.add('active');
      } else {
        if (fileInfo) fileInfo.classList.remove('active');
      }
    }

    if (fileClear) {
      fileClear.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        if (fileInfo) fileInfo.classList.remove('active');
      });
    }
  }

  // پیش‌نمایش تصویر جلد
  var coverInput = document.getElementById('coverFileInput');
  var coverUrl   = document.getElementById('coverUrlInput');
  var coverPrev  = document.getElementById('coverPreview');

  if (coverInput && coverPrev) {
    coverInput.addEventListener('change', function() {
      if (coverInput.files && coverInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          coverPrev.src = e.target.result;
          coverPrev.classList.add('active');
        };
        reader.readAsDataURL(coverInput.files[0]);
      }
    });
  }

  if (coverUrl && coverPrev) {
    coverUrl.addEventListener('input', function() {
      var val = coverUrl.value.trim();
      if (val && (val.indexOf('http://') === 0 || val.indexOf('https://') === 0 || val.indexOf('/') === 0)) {
        coverPrev.src = val;
        coverPrev.classList.add('active');
      }
    });
  }
})();
</script>
</body>
</html>
