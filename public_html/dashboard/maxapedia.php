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

$notice = $_GET['msg'] ?? '';

/* ---------- پردازش POST (افزودن / حذف) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'create') {
      maxapedia_create($pdo, [
        'section'     => $section,
        'category'    => trim((string)($_POST['category'] ?? '')),
        'title'       => trim((string)($_POST['title'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'url'         => maxapedia_extract_url((string)($_POST['url'] ?? '')),
        'thumbnail'   => trim((string)($_POST['thumbnail'] ?? '')),
        'status'      => $_POST['status'] ?? 'published',
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
      ]);
      $msg = 'created';
    } elseif ($action === 'delete') {
      maxapedia_delete($pdo, (int)($_POST['id'] ?? 0));
      $msg = 'deleted';
    } else {
      $msg = '';
    }
  } catch (Throwable $e) {
    $dbError = $e->getMessage();
    $msg = 'error';
  }
  // PRG: جلوگیری از ارسال مجدد فرم
  header('Location: maxapedia.php?section=' . urlencode($section) . ($msg ? '&msg=' . $msg : ''));
  exit;
}

/* ---------- فیلترها (جستجو + دسته‌بندی) ---------- */
$q         = trim((string)($_GET['q'] ?? ''));
$catFilter = trim((string)($_GET['cat'] ?? ''));

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
.mx-tab .em{font-size:24px;line-height:1}
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
</style>
</head>
<body>
<div class="mx-wrap">

  <div class="mx-head">
    <div class="mx-head-ic">📖</div>
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
  <?php elseif ($notice === 'deleted'): ?>
    <div class="mx-notice ok">محتوا حذف شد.</div>
  <?php elseif ($notice === 'error'): ?>
    <div class="mx-notice err">خطا در انجام عملیات.</div>
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

    <!-- فرم افزودن محتوا -->
    <form class="mx-card" method="post" action="maxapedia.php?section=<?= e($section) ?>">
      <h2><?= $meta['icon'] ?> افزودن به «<?= e($meta['title']) ?>»</h2>
      <input type="hidden" name="action" value="create">
      <input type="hidden" name="section" value="<?= e($section) ?>">

      <div class="mx-field">
        <label>عنوان <span style="color:var(--danger)">*</span></label>
        <input type="text" name="title" maxlength="255" required placeholder="مثلاً: آشنایی با بیماری‌های نادر">
      </div>

      <div class="mx-field">
        <label>دسته‌بندی</label>
        <input type="text" name="category" maxlength="120" list="mx-cat-list"
               value="<?= e($catFilter) ?>" placeholder="انتخاب از فهرست یا تعریف دسته‌ی جدید…">
        <datalist id="mx-cat-list">
          <?php foreach ($catOptions as $c): ?>
            <option value="<?= e($c) ?>"></option>
          <?php endforeach; ?>
        </datalist>
        <p class="mx-hint">یک دسته‌ی موجود را انتخاب کنید یا برای ساخت دسته‌ی جدید، نام آن را تایپ کنید.</p>
      </div>

      <div class="mx-field">
        <label>توضیح کوتاه</label>
        <textarea name="description" maxlength="2000" placeholder="توضیح مختصر درباره این محتوا…"></textarea>
      </div>

      <div class="mx-field">
        <label>لینک یا کدِ امبدِ محتوا (ویدیو / صوت / فایل)</label>
        <textarea name="url" maxlength="6000" rows="3" placeholder="https://…  یا کلِ کدِ امبد (مثلاً &lt;iframe …&gt;…&lt;/iframe&gt;)"></textarea>
        <p class="mx-hint">می‌توانید لینکِ معمولیِ یوتیوب، آپارات، نماشا، کست‌باکس، اسپاتیفای، ساندکلاد یا فایل مستقیم (mp4/mp3) را بگذارید — یا کلِ کدِ امبدی که این سرویس‌ها می‌دهند (iframe یا اسکریپتِ آپارات) را همین‌جا بچسبانید؛ آدرسِ پخش به‌صورت خودکار استخراج می‌شود.</p>
      </div>

      <div class="mx-field">
        <label>تصویر شاخص (لینک)</label>
        <input type="url" name="thumbnail" maxlength="1024" placeholder="https://… (اختیاری)">
      </div>

      <div class="mx-field">
        <label>وضعیت</label>
        <select name="status">
          <option value="published">منتشرشده</option>
          <option value="draft">پیش‌نویس</option>
        </select>
      </div>

      <div class="mx-field">
        <label>ترتیب نمایش</label>
        <input type="number" name="sort_order" value="0">
      </div>

      <button type="submit" class="mx-btn">افزودن محتوا</button>
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
                <img class="mx-row-thumb" src="<?= e($it['thumbnail']) ?>" alt="">
              <?php elseif ($emInfo && !empty($emInfo['poster'])): ?>
                <img class="mx-row-thumb" src="<?= e($emInfo['poster']) ?>" alt="">
              <?php else: ?>
                <div class="mx-row-thumb mx-row-thumb--icon"><?= $meta['icon'] ?></div>
              <?php endif; ?>

              <div class="mx-row-main">
                <div class="mx-row-title"><?= e($it['title']) ?></div>
                <div class="mx-row-tags">
                  <?php if (!empty($it['category'])): ?>
                    <span class="mx-chip">🏷 <?= e($it['category']) ?></span>
                  <?php endif; ?>
                  <?php if ($emInfo): ?>
                    <span class="mx-embed-badge">▶ <?= e($emInfo['provider']) ?></span>
                  <?php endif; ?>
                  <span class="mx-badge <?= $it['status']==='published'?'pub':'draft' ?>">
                    <?= $it['status']==='published'?'منتشرشده':'پیش‌نویس' ?>
                  </span>
                  <span class="mx-row-date"><?= jalali_date($it['created_at']) ?></span>
                </div>
              </div>

              <div class="mx-row-actions">
                <?php if (!empty($it['url'])): ?>
                  <a class="mx-item-link" href="<?= e($it['url']) ?>" target="_blank" rel="noopener" title="باز کردن لینک">↗</a>
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
</script>
</body>
</html>
