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

/* توابع کمکی آپلود و حذف فایل محتوا و تصویر شاخص/جلد */
if (!function_exists('maxapedia_delete_local_file')) {
  function maxapedia_delete_local_file(?string $path): void {
    if (!$path || strpos($path, '/uploads/') !== 0) return;
    $baseDir = realpath(dirname(__DIR__) . '/uploads');
    $fullPath = realpath(dirname(__DIR__) . $path);
    if ($fullPath && $baseDir && strpos($fullPath, $baseDir) === 0 && is_file($fullPath)) {
      @unlink($fullPath);
    }
  }
}

if (!function_exists('maxapedia_upload_file')) {
  function maxapedia_upload_file(array $file, string $section = 'general'): string {
    $subDir = in_array($section, ['books', 'brochures', 'gallery'], true) ? $section : 'maxapedia';
    return maxa_upload_and_optimize($file, $subDir, $section . '_', 82, 1920);
  }
}

if (!function_exists('maxapedia_upload_book_file')) {
  function maxapedia_upload_book_file(array $file): string {
    return maxapedia_upload_file($file, 'books');
  }
}

if (!function_exists('maxapedia_upload_brochure_file')) {
  function maxapedia_upload_brochure_file(array $file): string {
    return maxapedia_upload_file($file, 'brochures');
  }
}

if (!function_exists('maxapedia_upload_cover_file')) {
  function maxapedia_upload_cover_file(array $file, string $section = 'general'): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return '';
    }
    $subDir = in_array($section, ['books', 'brochures', 'gallery'], true) ? ($section . '/covers') : 'maxapedia/covers';
    return maxa_upload_and_optimize($file, $subDir, 'cover_' . $section . '_', 82, 1920);
  }
}

/* ---------- پردازش POST (افزودن / ویرایش / حذف) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
  $action   = $_POST['action'] ?? '';
  $errorMsg = null;
  $msg      = '';

  try {
    csrf_check();
    if(in_array($action,['create','update'],true)){content_prepare($pdo,'maxapedia_items');$pdo->beginTransaction();}
    if ($action === 'create') {
      $url       = trim((string)($_POST['url'] ?? ''));
      $thumbnail = trim((string)($_POST['thumbnail'] ?? ''));

      // ۱. آپلود فایل محتوا (PDF، کتاب، بروشور یا سند ضمیمه)
      $uploadedFile = $_FILES['file'] ?? $_FILES['book_file'] ?? $_FILES['brochure_file'] ?? null;
      if ($uploadedFile && $uploadedFile['error'] !== UPLOAD_ERR_NO_FILE) {
        $url = maxapedia_upload_file($uploadedFile, $section);
      } else {
        $url = maxapedia_extract_url($url);
      }

      // ۲. آپلود تصویر جلد / شاخص (اختیاری)
      $uploadedCover = $_FILES['thumbnail_file'] ?? null;
      if ($uploadedCover && $uploadedCover['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadedCoverUrl = maxapedia_upload_cover_file($uploadedCover, $section);
        if ($uploadedCoverUrl !== '') {
          $thumbnail = $uploadedCoverUrl;
        }
      }

      $title = trim((string)($_POST['title'] ?? ''));
      if ($title === '') {
        throw new Exception("عنوان محتوا الزامی است.");
      }

      $newId = maxapedia_create($pdo, [
        'section'     => $section,
        'category'    => trim((string)($_POST['category'] ?? '')),
        'title'       => $title,
        'description' => trim((string)($_POST['description'] ?? '')),
        'url'         => $url,
        'thumbnail'   => $thumbnail,
        'status'      => $_POST['status'] ?? 'published',
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
      ]);

      content_save($pdo,'maxapedia_items',(string)$newId);$pdo->commit();
      if ($section === 'gallery') {
        header('Location: maxapedia.php?section=gallery&album_id=' . $newId . '&msg=album_created');
        exit;
      }
      $msg = 'created';

    } elseif ($action === 'update') {
      $id = (int)($_POST['id'] ?? 0);
      $existing = maxapedia_find($pdo, $id);
      if (!$existing) {
        throw new Exception("محتوای مورد نظر برای ویرایش یافت نشد.");
      }

      $url       = $existing['url'];
      $thumbnail = $existing['thumbnail'];

      // آیا فایل جدیدی انتخاب شده؟
      $uploadedFile = $_FILES['file'] ?? $_FILES['book_file'] ?? $_FILES['brochure_file'] ?? null;
      if ($uploadedFile && $uploadedFile['error'] !== UPLOAD_ERR_NO_FILE) {
        $newUrl = maxapedia_upload_file($uploadedFile, $section);
        maxapedia_delete_local_file($existing['url']);
        $url = $newUrl;
      } elseif (isset($_POST['url']) && trim($_POST['url']) !== '') {
        $url = maxapedia_extract_url((string)$_POST['url']);
      }

      // آیا تصویر جلد/شاخص جدیدی آپلود شده؟
      $uploadedCover = $_FILES['thumbnail_file'] ?? null;
      if ($uploadedCover && $uploadedCover['error'] !== UPLOAD_ERR_NO_FILE) {
        $newCoverUrl = maxapedia_upload_cover_file($uploadedCover, $section);
        if ($newCoverUrl !== '') {
          maxapedia_delete_local_file($existing['thumbnail']);
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
      content_save($pdo,'maxapedia_items',(string)$id);$pdo->commit();
      $msg = 'updated';

    } elseif ($action === 'delete') {
      $delId = (int)($_POST['id'] ?? 0);
      $existing = maxapedia_find($pdo, $delId);
      if ($existing) {
        maxapedia_delete_local_file($existing['url']);
        maxapedia_delete_local_file($existing['thumbnail']);
      }
      maxapedia_delete($pdo, $delId);
      $msg = 'deleted';

    } elseif ($action === 'gallery_upload_photos') {
      $albumId = (int)($_POST['album_id'] ?? 0);
      $album = maxapedia_find($pdo, $albumId);
      if (!$album || $album['section'] !== 'gallery') {
        throw new Exception("آلبوم مورد نظر یافت نشد.");
      }

      $files = $_FILES['gallery_photos'] ?? null;
      if (!$files || empty($files['name']) || (is_array($files['name']) && empty($files['name'][0]))) {
        throw new Exception("هیچ تصویری برای آپلود انتخاب نشده است.");
      }

      $uploadedCount = 0;
      $firstPhotoUrl = null;

      // بررسی و آپلود تک‌تک تصاویر ارسالی
      $names    = (array)$files['name'];
      $tmpNames = (array)$files['tmp_name'];
      $errors   = (array)$files['error'];
      $sizes    = (array)$files['size'];
      $types    = (array)($files['type'] ?? []);

      $currentPhotosCount = maxapedia_gallery_photo_count($pdo, $albumId);

      foreach ($names as $idx => $origName) {
        if ($errors[$idx] === UPLOAD_ERR_NO_FILE) continue;
        $singleFile = [
          'name'     => $origName,
          'type'     => $types[$idx] ?? '',
          'tmp_name' => $tmpNames[$idx],
          'error'    => $errors[$idx],
          'size'     => $sizes[$idx] ?? 0,
        ];

        try {
          $photoUrl = maxapedia_upload_file($singleFile, 'gallery');
          $currentPhotosCount++;
          maxapedia_gallery_add_photo($pdo, $albumId, $photoUrl, '', 'normal', $currentPhotosCount);
          $uploadedCount++;
          if (!$firstPhotoUrl) {
            $firstPhotoUrl = $photoUrl;
          }
        } catch (Throwable $e) {
          // در صورت بروز خطا در یک تصویر، از بقیه عبور می‌کنیم
        }
      }

      if ($uploadedCount === 0) {
        throw new Exception("هیچ فایلی با موفقیت ذخیره نشد. لطفاً فرمت و حجم تصاویر را بررسی کنید.");
      }

      // اگر آلبوم هنوز تصویر کاور ندارد، اولین عکس آپلودشده به عنوان کاور آلبوم انتخاب می‌شود
      if (empty($album['thumbnail']) && $firstPhotoUrl) {
        maxapedia_update($pdo, $albumId, [
          'category'    => $album['category'],
          'title'       => $album['title'],
          'description' => $album['description'],
          'url'         => $album['url'],
          'thumbnail'   => $firstPhotoUrl,
          'status'      => $album['status'],
          'sort_order'  => (int)$album['sort_order'],
        ]);
      }

      header('Location: maxapedia.php?section=gallery&album_id=' . $albumId . '&msg=photos_uploaded');
      exit;

    } elseif ($action === 'gallery_save_photos') {
      $albumId = (int)($_POST['album_id'] ?? 0);
      $album = maxapedia_find($pdo, $albumId);
      if (!$album || $album['section'] !== 'gallery') {
        throw new Exception("آلبوم مورد نظر یافت نشد.");
      }

      $captions    = (array)($_POST['caption'] ?? []);
      $layoutSpans = (array)($_POST['layout_span'] ?? []);
      $sortOrders  = (array)($_POST['sort_order'] ?? []);

      foreach ($captions as $photoId => $capText) {
        $pId = (int)$photoId;
        if ($pId <= 0) continue;
        $span = (string)($layoutSpans[$photoId] ?? 'normal');
        $sort = (int)($sortOrders[$photoId] ?? 0);
        maxapedia_gallery_update_photo($pdo, $pId, [
          'caption'     => trim((string)$capText),
          'layout_span' => $span,
          'sort_order'  => $sort,
        ]);
      }

      header('Location: maxapedia.php?section=gallery&album_id=' . $albumId . '&msg=photos_updated');
      exit;

    } elseif ($action === 'gallery_delete_photo') {
      $photoId = (int)($_POST['photo_id'] ?? 0);
      $albumId = (int)($_POST['album_id'] ?? 0);
      if ($photoId > 0) {
        $photoUrl = maxapedia_gallery_delete_photo($pdo, $photoId);
        if ($photoUrl) {
          maxapedia_delete_local_file($photoUrl);
        }
      }
      header('Location: maxapedia.php?section=gallery&album_id=' . $albumId . '&msg=photo_deleted');
      exit;

    } elseif ($action === 'gallery_batch_delete_photos') {
      $albumId = (int)($_POST['album_id'] ?? 0);
      $selectedIds = (array)($_POST['selected_photo_ids'] ?? []);
      if (!empty($selectedIds)) {
        $deletedUrls = maxapedia_gallery_delete_photos_batch($pdo, $selectedIds);
        foreach ($deletedUrls as $u) {
          maxapedia_delete_local_file($u);
        }
      }
      header('Location: maxapedia.php?section=gallery&album_id=' . $albumId . '&msg=photos_batch_deleted');
      exit;
    }
  } catch (Throwable $e) {
    if($pdo->inTransaction())$pdo->rollBack();
    $errorMsg = $e->getMessage();
    $msg = 'error';
  }

  // PRG: جلوگیری از ارسال مجدد فرم
  $extraParams = '';
  if ($section === 'gallery' && !empty($_POST['album_id'])) {
    $extraParams = '&album_id=' . (int)$_POST['album_id'];
  }
  $redir = 'maxapedia.php?section=' . urlencode($section) . $extraParams . ($msg ? '&msg=' . $msg : '') . ($errorMsg ? '&err=' . urlencode($errorMsg) : '');
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

/* ---------- گالری: وضعیت آلبوم فعال و تعداد عکس‌ها ---------- */
$activeAlbumId = ($section === 'gallery') ? (int)($_GET['album_id'] ?? 0) : 0;
$activeAlbum   = ($activeAlbumId > 0 && $pdo) ? maxapedia_find($pdo, $activeAlbumId) : null;
if ($activeAlbum && $activeAlbum['section'] !== 'gallery') {
  $activeAlbum   = null;
  $activeAlbumId = 0;
}
$activeAlbumPhotos = ($activeAlbum && $pdo) ? maxapedia_gallery_photos($pdo, $activeAlbumId) : [];
$galleryCounts     = ($section === 'gallery' && $pdo) ? maxapedia_gallery_counts_map($pdo) : [];
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
  --color-primary:#008f8a; --color-primary-dark:#00736f; --color-primary-light:#4fb2b0;
  --color-secondary:#faa61a; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b;
  --primary-08:rgba(0, 143, 138,.08); --primary-12:rgba(0, 143, 138,.12);
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
*::-webkit-scrollbar-thumb{background:rgba(0, 143, 138,.20);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);background-clip:padding-box}

.mx-wrap{max-width:1040px;margin:0 auto}

.mx-head{display:flex;align-items:center;gap:16px;margin-bottom:22px}
.mx-head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;font-size:26px;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0, 143, 138,.6)}
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
  color:#fff;background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 10px 20px -10px rgba(0, 143, 138,.6);transition:transform .15s,box-shadow .2s}
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
  background: rgba(0, 143, 138, 0.15); color: var(--color-primary-dark);
}
.mx-action-edit {
  display: grid; place-items: center; width: 30px; height: 30px; border-radius: 8px;
  background: var(--color-bg); border: 1px solid var(--color-border);
  color: var(--color-text); text-decoration: none; font-size: 13px; transition: border-color .2s;
}
.mx-action-edit:hover { border-color: var(--color-primary); color: var(--color-primary); }
.mx-row-thumb--book, .mx-row-thumb--doc {
  width: 46px;
  height: 60px;
  border-radius: 6px;
  object-fit: cover;
}

/* ===== استایل‌های اختصاصی مدیریت گالری و عکس‌های آلبوم ===== */
.mx-album-header-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 18px;
  padding: 22px 24px;
  margin-bottom: 24px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  box-shadow: var(--shadow-sm);
}
.mx-album-header-title {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}
.mx-album-back-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 10px;
  background: var(--color-bg);
  border: 1.5px solid var(--color-border);
  color: var(--color-text);
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: all .2s;
}
.mx-album-back-btn:hover {
  background: var(--primary-08);
  border-color: var(--color-primary);
  color: var(--color-primary);
}
.mx-album-header-info h2 {
  font-size: 19px;
  font-weight: 900;
  color: var(--color-text);
  margin: 0 0 6px;
}
.mx-album-header-meta {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  color: var(--color-muted);
}
.mx-album-actions-top {
  display: flex;
  align-items: center;
  gap: 10px;
}
.mx-site-view-btn {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--color-primary);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--primary-08);
  padding: 8px 14px;
  border-radius: 10px;
  border: 1px solid var(--primary-12);
  transition: background .2s;
}
.mx-site-view-btn:hover { background: var(--primary-12); }

/* باکس آپلود گروهی چند عکس */
.mx-multi-uploader-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 18px;
  padding: 24px;
  margin-bottom: 28px;
  box-shadow: var(--shadow-sm);
}
.mx-multi-dropzone {
  border: 2px dashed var(--color-primary);
  border-radius: 16px;
  padding: 30px 20px;
  text-align: center;
  background: var(--primary-08);
  cursor: pointer;
  position: relative;
  transition: background .2s, border-color .2s;
}
.mx-multi-dropzone:hover, .mx-multi-dropzone.dragover {
  background: rgba(0, 143, 138, 0.14);
}
.mx-multi-dropzone input[type="file"] {
  position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;
}
.mx-multi-dropzone-icon {
  font-size: 38px;
  color: var(--color-primary);
  margin-bottom: 10px;
}
.mx-multi-dropzone-title {
  font-size: 15px;
  font-weight: 800;
  color: var(--color-text);
  margin-bottom: 6px;
}
.mx-multi-dropzone-hint {
  font-size: 12.5px;
  color: var(--color-muted);
}
.mx-selected-files-bar {
  display: none;
  align-items: center;
  justify-content: space-between;
  margin-top: 16px;
  padding: 12px 18px;
  background: var(--color-bg);
  border-radius: 12px;
  border: 1px solid var(--color-border);
}
.mx-selected-files-bar.active { display: flex; }
.mx-selected-summary { font-size: 13.5px; font-weight: 700; color: var(--color-text); }
.mx-btn-upload-submit {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: inherit;
  font-size: 13.5px;
  font-weight: 800;
  color: #fff;
  background: var(--color-primary);
  border: none;
  border-radius: 10px;
  padding: 10px 20px;
  cursor: pointer;
  transition: background .2s, transform .15s;
}
.mx-btn-upload-submit:hover { background: var(--color-primary-dark); transform: translateY(-1px); }

/* گرید مدیریت تصاویر آلبوم */
.mx-photos-container {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 18px;
  padding: 24px;
  box-shadow: var(--shadow-sm);
}
.mx-photos-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--color-border);
}
.mx-photos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}
.mx-photo-item {
  background: var(--color-bg);
  border: 1px solid var(--color-border);
  border-radius: 14px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: border-color .2s, box-shadow .2s;
}
.mx-photo-item:hover {
  border-color: var(--color-primary-light);
  box-shadow: var(--shadow-sm);
}
.mx-photo-img-wrap {
  position: relative;
  width: 100%;
  aspect-ratio: 4 / 3;
  background: #000;
  overflow: hidden;
}
.mx-photo-img-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.mx-photo-preview-link {
  position: absolute;
  top: 8px;
  left: 8px;
  background: rgba(0,0,0,0.6);
  color: #fff;
  border-radius: 6px;
  padding: 4px 8px;
  font-size: 11px;
  text-decoration: none;
  backdrop-filter: blur(4px);
}
.mx-photo-body {
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
}
.mx-photo-field label {
  font-size: 11.5px;
  font-weight: 700;
  color: var(--color-muted);
  margin-bottom: 4px;
  display: block;
}
.mx-photo-field input[type="text"],
.mx-photo-field select,
.mx-photo-field input[type="number"] {
  width: 100%;
  padding: 7px 10px;
  font-size: 12.5px;
  font-family: inherit;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  background: var(--color-surface);
  color: var(--color-text);
  outline: none;
}
.mx-photo-field input[type="text"]:focus,
.mx-photo-field select:focus,
.mx-photo-field input[type="number"]:focus {
  border-color: var(--color-primary);
}
.mx-photo-footer {
  margin-top: auto;
  padding-top: 10px;
  border-top: 1px dashed var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.mx-photo-del-btn {
  font-family: inherit;
  font-size: 12px;
  font-weight: 700;
  color: var(--danger);
  background: transparent;
  border: 1px solid var(--danger-12);
  border-radius: 8px;
  padding: 5px 10px;
  cursor: pointer;
  transition: all .2s;
}
.mx-photo-del-btn:hover {
  background: var(--danger-12);
  border-color: var(--danger);
}
.mx-manage-album-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  background: var(--primary-12);
  border: 1px solid var(--color-primary-light);
  color: var(--color-primary-dark);
  font-size: 12px;
  font-weight: 800;
  text-decoration: none;
  transition: all .2s;
}
.mx-manage-album-btn:hover {
  background: var(--color-primary);
  color: #fff;
}
.mx-cover-badge {
  position: absolute;
  top: 8px;
  right: 44px;
  background: var(--cta-orange, #faa61a);
  color: #fff;
  font-size: 10.5px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 6px;
  z-index: 4;
}

/* انتخاب چندگانه و جابجایی ترتیبی کارت‌های عکس */
.mx-photo-checkbox-wrap {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 5;
  background: rgba(255,255,255,0.92);
  border-radius: 8px;
  padding: 3px 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0,0,0,0.18);
  transition: transform .15s;
}
.mx-photo-checkbox-wrap:hover { transform: scale(1.08); }
.mx-photo-checkbox-wrap input[type="checkbox"] {
  width: 17px;
  height: 17px;
  cursor: pointer;
  accent-color: var(--color-primary);
}
.mx-drag-handle {
  position: absolute;
  bottom: 8px;
  right: 8px;
  z-index: 5;
  background: rgba(0,0,0,0.65);
  color: #fff;
  border-radius: 6px;
  padding: 4px 7px;
  cursor: grab;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
  transition: background .2s;
}
.mx-drag-handle:active { cursor: grabbing; background: var(--color-primary); }
.mx-photo-item.is-dragging {
  opacity: 0.35;
  border: 2px dashed var(--color-primary);
}
.mx-photo-item.drag-over {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px var(--primary-12);
  transform: scale(1.02);
}
.mx-btn-bulk-del {
  font-family: inherit;
  font-size: 12.5px;
  font-weight: 800;
  color: #fff;
  background: var(--danger);
  border: none;
  border-radius: 8px;
  padding: 6px 14px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: background .2s, transform .15s;
}
.mx-btn-bulk-del:hover {
  background: #cc4458;
  transform: translateY(-1px);
}
.mx-comp-box {
  margin-top: 14px;
  padding: 14px 18px;
  border-radius: 12px;
  background: var(--primary-08);
  border: 1px solid var(--color-primary-light);
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.mx-comp-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12.5px;
  font-weight: 700;
  color: var(--color-primary-dark);
}
.mx-comp-progress {
  height: 6px;
  background: rgba(0, 143, 138, 0.2);
  border-radius: 99px;
  overflow: hidden;
}
.mx-comp-bar {
  height: 100%;
  width: 0%;
  background: var(--color-primary);
  border-radius: 99px;
  transition: width .2s ease;
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
  <?php elseif ($notice === 'album_created'): ?>
    <div class="mx-notice ok">آلبوم جدید با موفقیت ایجاد شد! اکنون می‌توانید تصاویر این رویداد را در زیر آپلود و کپشن‌گذاری نمایید.</div>
  <?php elseif ($notice === 'photos_uploaded'): ?>
    <div class="mx-notice ok">تصاویر انتخابی با موفقیت در این آلبوم بارگذاری شدند.</div>
  <?php elseif ($notice === 'photos_updated'): ?>
    <div class="mx-notice ok">کپشن‌ها، اندازه‌ها و ترتیب تصاویر با موفقیت ذخیره شد.</div>
  <?php elseif ($notice === 'photo_deleted'): ?>
    <div class="mx-notice ok">تصویر مورد نظر از آلبوم حذف شد.</div>
  <?php elseif ($notice === 'photos_batch_deleted'): ?>
    <div class="mx-notice ok">تصاویر انتخاب‌شده با موفقیت از آلبوم حذف شدند.</div>
  <?php elseif ($notice === 'updated'): ?>
    <div class="mx-notice ok">تغییرات با موفقیت ذخیره شد.</div>
  <?php elseif ($notice === 'deleted'): ?>
    <div class="mx-notice ok">محتوا با موفقیت حذف شد.</div>
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

  <?php if ($section === 'gallery' && $activeAlbum): ?>
    <!-- ======================================================================
         نمای اختصاصی مدیریت تصاویر آلبوم گالری مکسا
         ====================================================================== -->
    <div class="mx-album-header-card">
      <div class="mx-album-header-title">
        <a href="maxapedia.php?section=gallery" class="mx-album-back-btn">
          <?= iconoir('arrow-right', '', 16) ?> بازگشت به آلبوم‌ها
        </a>
        <div class="mx-album-header-info">
          <h2>آلبوم «<?= e($activeAlbum['title']) ?>»</h2>
          <div class="mx-album-header-meta">
            <span><?= iconoir('label', '', 14) ?> دسته‌بندی: <strong><?= e($activeAlbum['category'] ?: 'عمومی') ?></strong></span>
            <span>•</span>
            <span><?= iconoir('media-image-list', '', 14) ?> <strong><?= fa_digits(count($activeAlbumPhotos)) ?></strong> تصویر</span>
          </div>
        </div>
      </div>
      <div class="mx-album-actions-top">
        <a href="/macsapedia/gallery?album=<?= (int)$activeAlbum['id'] ?>" target="_blank" rel="noopener" class="mx-site-view-btn">
          <?= iconoir('open-new-window', '', 15) ?> مشاهده این آلبوم در سایت
        </a>
      </div>
    </div>

    <!-- باکس آپلود گروهی عکس‌ها (Drag & Drop Multi-upload) -->
    <div class="mx-multi-uploader-card">
      <form method="post" action="maxapedia.php?section=gallery" enctype="multipart/form-data" id="galleryMultiUploadForm"><?= csrf_field() ?>
        <input type="hidden" name="action" value="gallery_upload_photos">
        <input type="hidden" name="album_id" value="<?= (int)$activeAlbum['id'] ?>">

        <div class="mx-multi-dropzone" id="galleryMultiDropzone">
          <input type="file" name="gallery_photos[]" id="galleryMultiInput" multiple accept="image/jpeg,image/png,image/webp,image/gif">
          <div class="mx-multi-dropzone-icon"><?= iconoir('media-image-plus', '', 40) ?></div>
          <div class="mx-multi-dropzone-title">عکس‌های مورد نظر را بکشید و اینجا رها کنید، یا برای انتخاب کلیک نمایید</div>
          <div class="mx-multi-dropzone-hint">می‌توانید تا ۱۰۰ تصویر را همزمان انتخاب و آپلود کنید (فرمت‌های مجاز: JPG, PNG, WEBP)</div>
        </div>

        <div class="mx-selected-files-bar" id="gallerySelectedBar">
          <span class="mx-selected-summary" id="gallerySelectedSummary">۰ تصویر انتخاب شده</span>
          <button type="submit" class="mx-btn-upload-submit" id="galleryUploadSubmitBtn">
            <?= iconoir('cloud-upload', '', 18) ?> شروع آپلود و افزودن به آلبوم
          </button>
        </div>

        <!-- نوار وضعیت بهینه‌سازی و فشرده‌سازی خودکار تصاویر -->
        <div class="mx-comp-box" id="galleryCompBox" style="display:none;">
          <div class="mx-comp-info">
            <span id="galleryCompStatus">در حال آماده‌سازی و بهینه‌سازی تصاویر...</span>
            <span id="galleryCompPercent">۰٪</span>
          </div>
          <div class="mx-comp-progress">
            <div class="mx-comp-bar" id="galleryCompBar"></div>
          </div>
        </div>
      </form>
    </div>

    <!-- گرید مدیریت تصاویر و ویرایش کپشن‌ها -->
    <div class="mx-photos-container">
      <form method="post" action="maxapedia.php?section=gallery" id="gallerySavePhotosForm"><?= csrf_field() ?>
        <input type="hidden" name="action" value="gallery_save_photos">
        <input type="hidden" name="album_id" value="<?= (int)$activeAlbum['id'] ?>">

        <div class="mx-photos-toolbar">
          <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:10px;">
              <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--color-text);">تصاویر ثبت‌شده در این آلبوم</h3>
              <span class="mx-count-pill"><?= fa_digits(count($activeAlbumPhotos)) ?> عکس</span>
            </div>
            <?php if (!empty($activeAlbumPhotos)): ?>
              <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:12.5px;font-weight:700;color:var(--color-muted);margin-right:8px;">
                <input type="checkbox" id="gallerySelectAll" onchange="toggleSelectAllPhotos(this)" style="cursor:pointer;width:16px;height:16px;accent-color:var(--color-primary);">
                انتخاب همه
              </label>
              <button type="button" class="mx-btn-bulk-del" id="galleryBulkDelBtn" style="display:none;" onclick="submitBatchDelete()">
                <?= iconoir('trash', '', 14) ?> حذف تصاویر انتخاب‌شده (<span id="gallerySelectedCount">۰</span>)
              </button>
            <?php endif; ?>
          </div>
          <?php if (!empty($activeAlbumPhotos)): ?>
            <button type="submit" class="mx-btn-upload-submit">
              <?= iconoir('check', '', 16) ?> ذخیره تغییرات کپشن‌ها و چیدمان
            </button>
          <?php endif; ?>
        </div>

        <?php if (empty($activeAlbumPhotos)): ?>
          <div class="mx-empty" style="padding:48px 20px;">
            هنوز هیچ عکسی برای این آلبوم آپلود نشده است. با استفاده از کادر بالا، عکس‌های این رویداد را انتخاب و آپلود نمایید.
          </div>
        <?php else: ?>
          <div class="mx-photos-grid" id="galleryPhotosGrid">
            <?php foreach ($activeAlbumPhotos as $idx => $p): ?>
              <div class="mx-photo-item" draggable="true" data-photo-id="<?= (int)$p['id'] ?>">
                <div class="mx-photo-img-wrap">
                  <label class="mx-photo-checkbox-wrap" title="انتخاب برای حذف گروهی">
                    <input type="checkbox" class="mx-photo-cb" value="<?= (int)$p['id'] ?>" onchange="onPhotoSelectionChange()">
                  </label>
                  <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['caption'] ?? '') ?>" loading="lazy">
                  <?php if (!empty($activeAlbum['thumbnail']) && $activeAlbum['thumbnail'] === $p['image_url']): ?>
                    <span class="mx-cover-badge">کاور آلبوم</span>
                  <?php endif; ?>
                  <a href="<?= e($p['image_url']) ?>" target="_blank" rel="noopener" class="mx-photo-preview-link" title="مشاهده تصویر بزرگ">
                    <?= iconoir('expand', '', 14) ?>
                  </a>
                  <div class="mx-drag-handle" title="بکشید تا ترتیب جابجا شود">
                    <?= iconoir('drag-hand-gesture', '', 14) ?>
                  </div>
                </div>
                <div class="mx-photo-body">
                  <div class="mx-photo-field">
                    <label>نیمچه کپشن (توضیح کوتاه زیر عکس):</label>
                    <input type="text" name="caption[<?= (int)$p['id'] ?>]" value="<?= e($p['caption'] ?? '') ?>" placeholder="مثلاً: سخنرانی مراسم افتتاحیه...">
                  </div>
                  <div style="display:grid;grid-template-columns:1fr 85px;gap:8px;">
                    <div class="mx-photo-field">
                      <label>اندازه در گرید:</label>
                      <select name="layout_span[<?= (int)$p['id'] ?>]">
                        <option value="normal" <?= ($p['layout_span'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>عادی (۱×۱)</option>
                        <option value="wide" <?= ($p['layout_span'] ?? '') === 'wide' ? 'selected' : '' ?>>عریض (۲ ستونه)</option>
                        <option value="tall" <?= ($p['layout_span'] ?? '') === 'tall' ? 'selected' : '' ?>>عمودی (۲ سطره)</option>
                        <option value="featured" <?= ($p['layout_span'] ?? '') === 'featured' ? 'selected' : '' ?>>شاخص بزرگ (۲×۲)</option>
                      </select>
                    </div>
                    <div class="mx-photo-field">
                      <label>ترتیب:</label>
                      <input type="number" name="sort_order[<?= (int)$p['id'] ?>]" class="mx-sort-input" value="<?= (int)($p['sort_order'] ?? ($idx + 1)) ?>">
                    </div>
                  </div>
                  <div class="mx-photo-footer">
                    <span style="font-size:11px;color:var(--color-muted);">عکس <?= fa_digits($idx + 1) ?></span>
                    <button type="button" class="mx-photo-del-btn" onclick="deletePhoto(<?= (int)$p['id'] ?>)">
                      <?= iconoir('trash', '', 13) ?> حذف عکس
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div style="margin-top:24px;text-align:left;">
            <button type="submit" class="mx-btn-upload-submit">
              <?= iconoir('check', '', 16) ?> ذخیره تغییرات کپشن‌ها و چیدمان
            </button>
          </div>
        <?php endif; ?>
      </form>

      <!-- فرم مخفی حذف عکس تک‌تک -->
      <form id="galleryDelPhotoForm" method="post" action="maxapedia.php?section=gallery" style="display:none;"><?= csrf_field() ?>
        <input type="hidden" name="action" value="gallery_delete_photo">
        <input type="hidden" name="album_id" value="<?= (int)$activeAlbum['id'] ?>">
        <input type="hidden" name="photo_id" id="galleryDelPhotoId" value="">
      </form>

      <!-- فرم مخفی حذف دسته‌ای تصاویر -->
      <form id="galleryBatchDelForm" method="post" action="maxapedia.php?section=gallery" style="display:none;"><?= csrf_field() ?>
        <input type="hidden" name="action" value="gallery_batch_delete_photos">
        <input type="hidden" name="album_id" value="<?= (int)$activeAlbum['id'] ?>">
        <div id="galleryBatchDelInputs"></div>
      </form>
    </div>

  <?php else: ?>

  <div class="mx-grid">

    <!-- فرم افزودن / ویرایش محتوا -->
    <form id="maxapediaContentForm" class="mx-card <?= $isEditing ? 'mx-card--editing' : '' ?>" method="post" action="maxapedia.php?section=<?= e($section) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <?php if ($section === 'gallery'): ?>
        <h2><?= $meta['icon'] ?> <?= $isEditing ? 'ویرایش آلبوم «' . e($editItem['title']) . '»' : 'افزودن آلبوم جدید به گالری مکسا' ?></h2>
      <?php else: ?>
        <h2><?= $meta['icon'] ?> <?= $isEditing ? 'ویرایش «' . e($editItem['title']) . '»' : 'افزودن به «' . e($meta['title']) . '»' ?></h2>
      <?php endif; ?>

      <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'create' ?>">
      <input type="hidden" name="section" value="<?= e($section) ?>">
      <?php if ($isEditing): ?>
        <input type="hidden" name="id" value="<?= (int)$editItem['id'] ?>">
      <?php endif; ?>

      <?php
        $isDocSection = in_array($section, ['books', 'brochures'], true);
        $docLabel = ($section === 'books') ? 'کتاب' : (($section === 'brochures') ? 'بروشور' : 'فایل');

        $titlePlaceholder = ($section === 'gallery')
          ? 'مثلاً: عکس‌های همایش برج میلاد، خدمات در منزل و...'
          : (($section === 'books')
            ? 'مثلاً: راهنمای جامع مراقبت تسکینی'
            : (($section === 'brochures')
              ? 'مثلاً: بروشور راهنمای خودمراقبتی لنف‌ادم یا تغذیه'
              : 'مثلاً: آشنایی با بیماری‌های نادر'));

        $descPlaceholder = ($section === 'gallery')
          ? 'توضیحات و گزارش کوتاه درباره این رویداد یا مجموعه عکس…'
          : (($section === 'books')
            ? 'توضیح مختصر درباره کتاب و پدیدآورندگان…'
            : (($section === 'brochures')
              ? 'توضیح مختصر درباره موضوع بروشور و جامعه هدف…'
              : 'توضیح مختصر درباره این محتوا…'));
      ?>

      <div class="mx-field">
        <label><?= $section === 'gallery' ? 'نام آلبوم / رویداد' : 'عنوان' ?> <span style="color:var(--danger)">*</span></label>
        <input type="text" name="title" maxlength="255" required value="<?= e($isEditing ? ($editItem['title'] ?? '') : '') ?>" placeholder="<?= e($titlePlaceholder) ?>">
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
        <label><?= $section === 'gallery' ? 'توضیحات آلبوم' : 'توضیح کوتاه' ?></label>
        <textarea name="description" maxlength="2000" placeholder="<?= e($descPlaceholder) ?>"><?= e($isEditing ? ($editItem['description'] ?? '') : '') ?></textarea>
      </div>

      <?php if ($section === 'gallery'): ?>
        <!-- بخش اختصاصی کاور آلبوم گالری -->
        <div class="mx-field">
          <label>تصویر کاور آلبوم</label>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <input type="file" name="thumbnail_file" id="coverFileInput" accept="image/jpeg,image/png,image/webp,image/gif">
            <input type="url" name="thumbnail" id="coverUrlInput" value="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" placeholder="یا آدرس اینترنتی تصویر کاور (https://…)">
          </div>
          <img id="coverPreview" class="mx-cover-preview <?= ($isEditing && !empty($editItem['thumbnail'])) ? 'active' : '' ?>" src="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" alt="پیش‌نمایش تصویر کاور">
          <p class="mx-hint">در صورت خالی گذاشتن، اولین عکسی که در مرحله بعد برای این آلبوم آپلود کنید خودکار به عنوان کاور تنظیم می‌شود.</p>
        </div>

      <?php elseif ($isDocSection): ?>
        <!-- بخش اختصاصی آپلود کتاب و بروشور -->
        <div class="mx-field">
          <label>آپلود فایل <?= $docLabel ?> (PDF / <?= $section === 'books' ? 'EPUB / MOBI / ZIP' : 'سند آموزشی یا ZIP' ?> و...)</label>
          <div class="mx-dropzone" id="mxDropzone">
            <input type="file" name="file" id="mxFileInput" accept=".pdf,.epub,.mobi,.doc,.docx,.zip,.rar">
            <div class="mx-dropzone-icon"><?= iconoir('download', '', 36) ?></div>
            <div class="mx-dropzone-title">فایل <?= $docLabel ?> (PDF) را بکشید و اینجا رها کنید یا برای انتخاب کلیک کنید</div>
            <div class="mx-dropzone-hint">فرمت‌های مجاز: PDF, <?= $section === 'books' ? 'EPUB, MOBI, ' : '' ?>DOC, DOCX, ZIP, RAR (حداکثر <?= ini_get('upload_max_filesize') ?>)</div>
          </div>
          <div class="mx-file-info" id="mxFileInfo">
            <div>
              <span class="mx-file-name" id="mxFileName"></span>
              <span class="mx-file-size" id="mxFileSize"></span>
            </div>
            <button type="button" class="mx-file-clear" id="mxFileClear" title="حذف انتخاب">&times;</button>
          </div>
          <?php if ($isEditing && !empty($editItem['url'])): ?>
            <div class="mx-current-file">
              <span><?= iconoir('notes', '', 15) ?> فایل/لینک فعلی:</span>
              <a href="<?= e($editItem['url']) ?>" target="_blank" rel="noopener">مشاهده / دانلود <?= $docLabel ?></a>
              <?php if (strpos($editItem['url'], '/uploads/') === 0): ?>
                <span class="mx-badge-format">فایل ذخیره‌شده در سرور</span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="mx-field">
          <label>یا لینکِ مطالعه / دانلود مستقیم <?= $docLabel ?> (اختیاری در صورت آپلود فایل)</label>
          <input type="text" name="url" value="<?= e($isEditing ? ($editItem['url'] ?? '') : '') ?>" placeholder="https://… (اگر فایل <?= $docLabel ?> را آپلود کردید، این فیلد را خالی بگذارید)">
          <p class="mx-hint">اگر فایل <?= $docLabel ?> را در کادر بالا آپلود کنید، این آدرس به‌طور خودکار تنظیم می‌شود. در صورت تمایل به قرار دادن لینک خارجی، آن را اینجا بنویسید.</p>
        </div>

        <div class="mx-field">
          <label>تصویر جلد / پیش‌نمایش <?= $docLabel ?></label>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <input type="file" name="thumbnail_file" id="coverFileInput" accept="image/jpeg,image/png,image/webp,image/gif">
            <input type="url" name="thumbnail" id="coverUrlInput" value="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" placeholder="یا آدرس اینترنتی تصویر جلد (https://…)">
          </div>
          <img id="coverPreview" class="mx-cover-preview <?= ($isEditing && !empty($editItem['thumbnail'])) ? 'active' : '' ?>" src="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" alt="پیش‌نمایش جلد">
          <p class="mx-hint">می‌توانید تصویر جلد را مستقیماً آپلود کنید یا آدرس اینترنتی آن را وارد کنید (پیشنهاد: پرتره عمودی با نسبت ۳:۴).</p>
        </div>

      <?php else: ?>
        <!-- بخش‌های رسانه‌ای (ویدیو، پادکست، کلیپ، گالری) -->
        <div class="mx-field">
          <label>آپلود فایل مستقیم یا سند ضمیمه (اختیاری — PDF / رسانه)</label>
          <div class="mx-dropzone" id="mxDropzone">
            <input type="file" name="file" id="mxFileInput" accept=".pdf,.doc,.docx,.zip,.rar,.mp4,.mp3,.webm">
            <div class="mx-dropzone-icon"><?= iconoir('download', '', 32) ?></div>
            <div class="mx-dropzone-title">فایل PDF یا رسانه‌ای را بکشید و اینجا رها کنید یا برای انتخاب کلیک کنید</div>
            <div class="mx-dropzone-hint">فرمت‌های مجاز: PDF, MP4, MP3, ZIP, DOCX و... (حداکثر <?= ini_get('upload_max_filesize') ?>)</div>
          </div>
          <div class="mx-file-info" id="mxFileInfo">
            <div>
              <span class="mx-file-name" id="mxFileName"></span>
              <span class="mx-file-size" id="mxFileSize"></span>
            </div>
            <button type="button" class="mx-file-clear" id="mxFileClear" title="حذف انتخاب">&times;</button>
          </div>
          <?php if ($isEditing && !empty($editItem['url']) && strpos($editItem['url'], '/uploads/') === 0): ?>
            <div class="mx-current-file">
              <span><?= iconoir('notes', '', 15) ?> فایل آپلودشده فعلی:</span>
              <a href="<?= e($editItem['url']) ?>" target="_blank" rel="noopener">مشاهده / دریافت فایل</a>
              <span class="mx-badge-format">فایل ذخیره‌شده در سرور</span>
            </div>
          <?php endif; ?>
        </div>

        <div class="mx-field">
          <label>یا لینک یا کدِ امبدِ محتوا (ویدیو / صوت / فایل / لینک خارجی)</label>
          <textarea name="url" maxlength="6000" rows="3" placeholder="https://…  یا کلِ کدِ امبد (مثلاً &lt;iframe …&gt;…&lt;/iframe&gt;)"><?= e($isEditing ? ($editItem['url'] ?? '') : '') ?></textarea>
          <p class="mx-hint">اگر فایلی در کادر بالا آپلود نکرده‌اید، لینکِ معمولیِ یوتیوب، آپارات، نماشا، کست‌باکس، اسپاتیفای، ساندکلاد یا فایل مستقیم را بگذارید — یا کلِ کدِ امبد را همین‌جا بچسبانید.</p>
        </div>

        <div class="mx-field">
          <label>تصویر شاخص / کاور</label>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <input type="file" name="thumbnail_file" id="coverFileInput" accept="image/jpeg,image/png,image/webp,image/gif">
            <input type="url" name="thumbnail" id="coverUrlInput" value="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" placeholder="یا آدرس اینترنتی تصویر شاخص (https://…)">
          </div>
          <img id="coverPreview" class="mx-cover-preview <?= ($isEditing && !empty($editItem['thumbnail'])) ? 'active' : '' ?>" src="<?= e($isEditing ? ($editItem['thumbnail'] ?? '') : '') ?>" alt="پیش‌نمایش تصویر">
          <p class="mx-hint">می‌توانید تصویر شاخص را از سیستم آپلود کنید یا آدرس اینترنتی آن را وارد نمایید.</p>
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
        <button type="submit" class="mx-btn"><?= $section === 'gallery' ? 'ایجاد آلبوم و رفتن به آپلود تصاویر ←' : 'افزودن محتوا' ?></button>
      <?php endif; ?>
    </form>

    <!-- لیست محتوای بخش -->
    <div>
      <div class="mx-list-head" style="display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;align-items:center;gap:10px;">
          <h2><?= $section === 'gallery' ? 'آلبوم‌های «گالری مکسا»' : 'محتوای «' . e($meta['title']) . '»' ?></h2>
          <span class="mx-count-pill"><?= fa_digits(count($items)) ?> مورد</span>
        </div>
        <a href="/macsapedia/<?= e($section) ?>" target="_blank" rel="noopener" style="font-size:12px;font-weight:700;color:var(--color-primary);text-decoration:none;display:inline-flex;align-items:center;gap:5px;background:var(--primary-08);padding:5px 12px;border-radius:8px;border:1px solid var(--primary-12);transition:background .2s;">
          <?= iconoir('open-new-window', '', 14) ?> مشاهده صفحه در سایت
        </a>
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
              <?php $isDocItem = in_array($section, ['books', 'brochures'], true); ?>
              <?php if (!empty($it['thumbnail'])): ?>
                <img class="mx-row-thumb <?= $isDocItem ? 'mx-row-thumb--doc' : '' ?>" src="<?= e($it['thumbnail']) ?>" alt="">
              <?php elseif ($emInfo && !empty($emInfo['poster'])): ?>
                <img class="mx-row-thumb" src="<?= e($emInfo['poster']) ?>" alt="">
              <?php else: ?>
                <div class="mx-row-thumb <?= $isDocItem ? 'mx-row-thumb--doc' : '' ?> mx-row-thumb--icon"><?= $meta['icon'] ?></div>
              <?php endif; ?>

              <div class="mx-row-main">
                <div class="mx-row-title"><?= e($it['title']) ?></div>
                <div class="mx-row-tags">
                  <?php if (!empty($it['category'])): ?>
                    <span class="mx-chip"><?= iconoir('label', '', 13) ?> <?= e($it['category']) ?></span>
                  <?php endif; ?>

                  <?php if ($section === 'gallery'): ?>
                    <span class="mx-badge pub" style="font-size:11px;">
                      <?= iconoir('media-image', '', 12) ?> <?= fa_digits($galleryCounts[$it['id']] ?? 0) ?> تصویر
                    </span>
                  <?php endif; ?>

                  <?php if ($emInfo): ?>
                    <span class="mx-embed-badge">▶ <?= e($emInfo['provider']) ?></span>
                  <?php endif; ?>
                  <?php
                  if (!empty($it['url']) && $section !== 'gallery') {
                    $ext = strtolower(pathinfo(parse_url($it['url'], PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                    if ($ext) {
                      $prefix = ($section === 'books') ? 'کتاب ' : (($section === 'brochures') ? 'بروشور ' : '');
                      echo '<span class="mx-badge-format">' . iconoir('notes', '', 13) . ' ' . $prefix . strtoupper(e($ext)) . '</span>';
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
                <?php if ($section === 'gallery'): ?>
                  <a class="mx-manage-album-btn" href="maxapedia.php?section=gallery&album_id=<?= (int)$it['id'] ?>" title="مدیریت و آپلود تصاویر این آلبوم">
                    <?= iconoir('media-image-list', '', 14) ?> مدیریت تصاویر (<?= fa_digits($galleryCounts[$it['id']] ?? 0) ?>)
                  </a>
                <?php endif; ?>

                <a class="mx-action-edit" href="maxapedia.php?section=<?= e($section) ?>&edit=<?= (int)$it['id'] ?>" title="ویرایش"><?= iconoir('edit-pencil', '', 15) ?></a>
                <?php if (!empty($it['url']) && $section !== 'gallery'): ?>
                  <?php
                    $isDocLink = ($isDocItem || preg_match('/\.(pdf|epub|mobi|docx?|zip|rar)$/i', parse_url($it['url'], PHP_URL_PATH) ?? ''));
                  ?>
                  <a class="mx-item-link" href="<?= e($it['url']) ?>" target="_blank" rel="noopener" title="<?= $isDocLink ? ('دانلود یا مطالعه ' . $docLabel) : 'باز کردن لینک' ?>">
                    <?= $isDocLink ? iconoir('download', '', 15) : iconoir('arrow-up-right', '', 15) ?>
                  </a>
                <?php endif; ?>
                <form method="post" action="maxapedia.php?section=<?= e($section) ?><?= csrf_field() ?>" onsubmit="return confirm('<?= $section === 'gallery' ? 'این آلبوم و تمامی عکس‌های آن حذف شود؟' : 'این محتوا حذف شود؟' ?>');">
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
  <?php endif; ?>
</div>

<script>
/* همگام‌سازی تم تیره با بقیه‌ی پنل */
window.addEventListener('storage',function(e){
  if(e.key==='maxa-theme'){
    document.documentElement.setAttribute('data-theme', e.newValue==='dark'?'dark':'light');
  }
});

/* حذف تکی عکس در آلبوم گالری */
window.deletePhoto = function(id) {
  if (confirm('آیا از حذف این تصویر از آلبوم مطمئن هستید؟')) {
    var f = document.getElementById('galleryDelPhotoForm');
    var input = document.getElementById('galleryDelPhotoId');
    if (f && input) {
      input.value = id;
      f.submit();
    }
  }
};

/* انتخاب همه و حذف گروهی عکس‌های گالری */
window.toggleSelectAllPhotos = function(master) {
  var cbs = document.querySelectorAll('.mx-photo-cb');
  cbs.forEach(function(cb) { cb.checked = master.checked; });
  window.onPhotoSelectionChange();
};

window.onPhotoSelectionChange = function() {
  var selected = document.querySelectorAll('.mx-photo-cb:checked');
  var bulkBtn = document.getElementById('galleryBulkDelBtn');
  var countSpan = document.getElementById('gallerySelectedCount');
  var masterCb = document.getElementById('gallerySelectAll');
  var allCbs = document.querySelectorAll('.mx-photo-cb');

  if (countSpan) countSpan.textContent = selected.length;
  if (bulkBtn) bulkBtn.style.display = selected.length > 0 ? 'inline-flex' : 'none';
  if (masterCb) masterCb.checked = (selected.length === allCbs.length && allCbs.length > 0);
};

window.submitBatchDelete = function() {
  var selected = document.querySelectorAll('.mx-photo-cb:checked');
  if (!selected.length) {
    alert('لطفاً حداقل یک عکس را برای حذف انتخاب کنید.');
    return;
  }
  if (!confirm('آیا از حذف ' + selected.length + ' تصویر انتخاب‌شده از این آلبوم مطمئن هستید؟ فایل‌های این تصاویر نیز از سرور پاک خواهند شد.')) {
    return;
  }

  var container = document.getElementById('galleryBatchDelInputs');
  var form = document.getElementById('galleryBatchDelForm');
  if (container && form) {
    container.innerHTML = '';
    selected.forEach(function(cb) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'selected_photo_ids[]';
      input.value = cb.value;
      container.appendChild(input);
    });
    form.submit();
  }
};

/* مرتب‌سازی ویژوال کارت‌های عکس با کشیدن و رها کردن (Drag & Drop Reordering) */
(function() {
  var grid = document.getElementById('galleryPhotosGrid');
  if (!grid) return;

  var draggedItem = null;

  function renumberSortInputs() {
    var items = grid.querySelectorAll('.mx-photo-item');
    items.forEach(function(item, idx) {
      var sortInput = item.querySelector('.mx-sort-input');
      if (sortInput) {
        sortInput.value = idx + 1;
      }
    });
  }

  grid.addEventListener('dragstart', function(e) {
    var item = e.target.closest('.mx-photo-item');
    if (!item) return;
    draggedItem = item;
    item.classList.add('is-dragging');
    e.dataTransfer.effectAllowed = 'move';
    try { e.dataTransfer.setData('text/plain', ''); } catch(err) {}
  });

  grid.addEventListener('dragend', function(e) {
    var item = e.target.closest('.mx-photo-item');
    if (item) item.classList.remove('is-dragging');
    grid.querySelectorAll('.mx-photo-item').forEach(function(el) {
      el.classList.remove('drag-over');
    });
    draggedItem = null;
    renumberSortInputs();
  });

  grid.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    var targetItem = e.target.closest('.mx-photo-item');
    if (!targetItem || targetItem === draggedItem) return;

    var rect = targetItem.getBoundingClientRect();
    var isRtlNext = (e.clientX - rect.left) / (rect.right - rect.left) > 0.5;
    grid.insertBefore(draggedItem, isRtlNext ? targetItem : targetItem.nextSibling);
  });

  grid.addEventListener('dragenter', function(e) {
    var item = e.target.closest('.mx-photo-item');
    if (item && item !== draggedItem) {
      item.classList.add('drag-over');
    }
  });

  grid.addEventListener('dragleave', function(e) {
    var item = e.target.closest('.mx-photo-item');
    if (item) item.classList.remove('drag-over');
  });
})();

/* مدیریت دراگ و دراپ و فشرده‌سازی هوشمند تصاویر در کلاینت قبل از آپلود */
(function() {
  var multiDropzone = document.getElementById('galleryMultiDropzone');
  var multiInput    = document.getElementById('galleryMultiInput');
  var selectedBar   = document.getElementById('gallerySelectedBar');
  var summarySpan   = document.getElementById('gallerySelectedSummary');
  var uploadForm    = document.getElementById('galleryMultiUploadForm');

  function compressImageFile(file, maxWidth, maxHeight, quality) {
    maxWidth = maxWidth || 1920;
    maxHeight = maxHeight || 1920;
    quality = quality || 0.88;

    if (!file.type || !file.type.startsWith('image/')) return Promise.resolve(file);

    return new Promise(function(resolve) {
      var img = new Image();
      var url = URL.createObjectURL(file);
      img.onload = function() {
        URL.revokeObjectURL(url);
        var width = img.naturalWidth || img.width;
        var height = img.naturalHeight || img.height;

        if (width <= maxWidth && height <= maxHeight && file.size <= 600 * 1024) {
          resolve(file);
          return;
        }

        if (width > maxWidth || height > maxHeight) {
          if (width / height > maxWidth / maxHeight) {
            height = Math.round((height * maxWidth) / width);
            width = maxWidth;
          } else {
            width = Math.round((width * maxHeight) / height);
            height = maxHeight;
          }
        }

        var canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);

        canvas.toBlob(function(blob) {
          if (!blob || blob.size >= file.size) {
            resolve(file);
          } else {
            var newName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
            resolve(new File([blob], newName, { type: 'image/jpeg' }));
          }
        }, 'image/jpeg', quality);
      };
      img.onerror = function() {
        URL.revokeObjectURL(url);
        resolve(file);
      };
      img.src = url;
    });
  }

  if (multiDropzone && multiInput) {
    ['dragenter', 'dragover'].forEach(function(evt) {
      multiDropzone.addEventListener(evt, function(e) {
        e.preventDefault();
        e.stopPropagation();
        multiDropzone.classList.add('dragover');
      });
    });

    ['dragleave', 'drop'].forEach(function(evt) {
      multiDropzone.addEventListener(evt, function(e) {
        e.preventDefault();
        e.stopPropagation();
        multiDropzone.classList.remove('dragover');
      });
    });

    multiDropzone.addEventListener('drop', function(e) {
      if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
        multiInput.files = e.dataTransfer.files;
        handleMultiFiles();
      }
    });

    multiInput.addEventListener('change', handleMultiFiles);

    function handleMultiFiles() {
      if (multiInput.files && multiInput.files.length > 0) {
        var count = multiInput.files.length;
        var totalBytes = 0;
        for (var i = 0; i < count; i++) {
          totalBytes += multiInput.files[i].size;
        }
        var k = 1024;
        var sizes = ['Bytes', 'KB', 'MB', 'GB'];
        var idx = Math.floor(Math.log(totalBytes) / Math.log(k));
        var sizeStr = (totalBytes / Math.pow(k, idx)).toFixed(1) + ' ' + sizes[idx];

        if (summarySpan) {
          summarySpan.textContent = count + ' تصویر انتخاب شده (' + sizeStr + ') — آماده بهینه‌سازی و آپلود';
        }
        if (selectedBar) {
          selectedBar.classList.add('active');
        }
      } else {
        if (selectedBar) selectedBar.classList.remove('active');
      }
    }
  }

  // پایپ‌لاین فشرده‌سازی هوشمند تصاویر در هنگام سابمیت فرم
  var isCompressing = false;
  if (uploadForm && multiInput) {
    uploadForm.addEventListener('submit', async function(e) {
      if (isCompressing) return;
      var files = multiInput.files;
      if (!files || !files.length) return;

      e.preventDefault();
      isCompressing = true;

      var compBox = document.getElementById('galleryCompBox');
      var compBar = document.getElementById('galleryCompBar');
      var compStatus = document.getElementById('galleryCompStatus');
      var compPercent = document.getElementById('galleryCompPercent');
      var submitBtn = document.getElementById('galleryUploadSubmitBtn');

      if (compBox) compBox.style.display = 'flex';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.6';
      }

      try {
        var dt = new DataTransfer();
        var total = files.length;
        var origTotalSize = 0;
        var optTotalSize = 0;

        for (var i = 0; i < total; i++) {
          origTotalSize += files[i].size;
          var pct = Math.round(((i) / total) * 100);
          if (compBar) compBar.style.width = pct + '%';
          if (compPercent) compPercent.textContent = pct + '٪';
          if (compStatus) compStatus.textContent = 'در حال فشرده‌سازی هوشمند عکس ' + (i + 1) + ' از ' + total + '...';

          var optFile = await compressImageFile(files[i]);
          optTotalSize += optFile.size;
          dt.items.add(optFile);
        }

        if (compBar) compBar.style.width = '100%';
        if (compPercent) compPercent.textContent = '۱۰۰٪';
        var savedPct = origTotalSize > 0 ? Math.round(((origTotalSize - optTotalSize) / origTotalSize) * 100) : 0;
        if (compStatus) compStatus.textContent = 'بهینه‌سازی انجام شد (' + savedPct + '٪ کاهش حجم)! در حال ارسال به سرور...';

        multiInput.files = dt.files;
      } catch (err) {
        console.warn('Canvas compression error fallback:', err);
      }

      uploadForm.submit();
    });
  }
})();

/* دراگ و دراپ و پیش‌نمایش فایل و جلد برای مکساپدیا */
(function() {
  var dropzone  = document.getElementById('mxDropzone') || document.getElementById('bookDropzone');
  var fileInput = document.getElementById('mxFileInput') || document.getElementById('bookFileInput');
  var fileInfo  = document.getElementById('mxFileInfo') || document.getElementById('bookFileInfo');
  var fileName  = document.getElementById('mxFileName') || document.getElementById('bookFileName');
  var fileSize  = document.getElementById('mxFileSize') || document.getElementById('bookFileSize');
  var fileClear = document.getElementById('mxFileClear') || document.getElementById('bookFileClear');

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

  // پیش‌نمایش تصویر جلد / شاخص
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
<?php if($section==='gallery'): ?>
<template id="contentPhotoTemplate">
  <div class="mx-photo-item">
    <div class="mx-photo-img-wrap">
      <div class="mx-dropzone"><input type="file" name="image_url" accept="image/jpeg,image/png,image/webp,image/gif"><div class="mx-dropzone-icon"><?= iconoir('media-image-plus','',40) ?></div><div class="mx-dropzone-title">انتخاب تصویر</div></div>
      <span class="mx-drag-handle" title="جابه‌جایی تصویر"><?= iconoir('drag-hand-gesture','',14) ?></span>
    </div>
    <div class="mx-photo-body">
      <div class="mx-photo-field"><label>نیمچه کپشن (توضیح کوتاه زیر عکس):</label><input name="caption" placeholder="مثلاً: سخنرانی مراسم افتتاحیه..."></div>
      <div class="mx-photo-field"><label>اندازه در گرید:</label><select name="layout_span"><option value="normal">عادی (۱×۱)</option><option value="wide">عریض (۲ ستونه)</option><option value="tall">عمودی (۲ سطره)</option><option value="featured">شاخص بزرگ (۲×۲)</option></select></div>
      <button type="button" class="mx-btn-bulk-del" data-cl-remove-row><?= iconoir('trash','',14) ?> حذف تصویر</button>
    </div>
  </div>
</template>
<script>window.ContentEditorRowTemplates={...(window.ContentEditorRowTemplates||{}),maxapedia_items:{photos:()=>document.getElementById('contentPhotoTemplate').innerHTML}};</script>
<?php endif; ?>
<?php content_editor('maxapedia_items',$isEditing?(string)$editItem['id']:'','#maxapediaContentForm',['section'=>$section]); ?>
</body>
</html>
