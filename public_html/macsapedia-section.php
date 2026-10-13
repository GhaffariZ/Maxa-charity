<?php
/* صفحه‌ی عمومیِ یک بخش از مکساپدیا — محتوای منتشرشده‌ی همان بخش را نمایش می‌دهد. */
require_once __DIR__ . '/core/icons.php';
require __DIR__ . '/dashboard/maxapedia-db.php';

$mpSlug = $_GET['section'] ?? '';

if (!maxapedia_is_section($mpSlug)) {
    http_response_code(404);
    include __DIR__ . '/404.html';
    exit;
}

$mpSection = maxapedia_section_meta($mpSlug);

/* فیلترها: جستجو + دسته‌بندی (از طریق query string روی URL تمیز) */
$mpQ   = trim((string)($_GET['q'] ?? ''));
$mpCat = trim((string)($_GET['cat'] ?? ''));

$mpCats  = ($pdo) ? maxapedia_categories($pdo, $mpSlug, true) : [];
$mpItems = ($pdo) ? maxapedia_items($pdo, $mpSlug, true, ['category' => $mpCat, 'q' => $mpQ]) : [];

$mpBase = '/macsapedia/' . rawurlencode($mpSlug);
/* ساخت آدرسِ چیپ دسته‌بندی با حفظ عبارت جستجو */
$mpChipUrl = static function (string $cat) use ($mpBase, $mpQ): string {
    $params = [];
    if ($cat  !== '') { $params['cat'] = $cat; }
    if ($mpQ  !== '') { $params['q']   = $mpQ; }
    return $mpBase . ($params ? '?' . http_build_query($params) : '');
};
$mpFiltered = ($mpQ !== '' || $mpCat !== '');

/* جداسازی ویدیوها از سایر محتوا برای چیدمانِ «تماشا» (پلیر بزرگ + فهرست کناری) */
$mpVideos = [];
$mpOthers = [];
foreach ($mpItems as $it) {
    $em = maxapedia_embed((string)($it['url'] ?? ''));
    $it['_em'] = $em;
    if ($em && $em['kind'] === 'video') { $mpVideos[] = $it; }
    else                                { $mpOthers[] = $it; }
}

/* گالری مکسا: وضعیت مشاهده آلبوم اختصاصی یا کل آلبوم‌ها */
$galleryAlbumId = ($mpSlug === 'gallery') ? (int)($_GET['album'] ?? 0) : 0;
$galleryAlbum   = ($galleryAlbumId > 0 && $pdo) ? maxapedia_find($pdo, $galleryAlbumId) : null;
if ($galleryAlbum && ($galleryAlbum['section'] !== 'gallery' || $galleryAlbum['status'] !== 'published')) {
  $galleryAlbum   = null;
  $galleryAlbumId = 0;
}
$galleryPhotos = ($galleryAlbum && $pdo) ? maxapedia_gallery_photos($pdo, $galleryAlbumId) : [];
$galleryCounts = ($mpSlug === 'gallery' && $pdo) ? maxapedia_gallery_counts_map($pdo) : [];

if ($galleryAlbum) {
  $pageTitle = $galleryAlbum['title'] . ' — گالری مکساپدیا';
} else {
  $pageTitle = $mpSection['title'] . ' — مکساپدیا';
}
require_once __DIR__ . '/core/component-renderer.php';
render_dashboard_component('header', get_defined_vars());
?>

<style>
  .mp-wrap {
    max-width: var(--cta-container, 1440px);
    margin: 0 auto;
    padding: 32px 20px 48px;
    font-family: 'Vazirmatn', sans-serif;
    direction: rtl;
  }
  .iconoir-icon { display: inline-flex; vertical-align: middle; width: 1.2em; height: 1.2em; stroke-width: 1.6; flex-shrink: 0; }
  .mp-breadcrumb { font-size: 14px; color: #8b8f96; margin-bottom: 20px; }
  .mp-breadcrumb a { color: var(--cta-orange, #faa61a); text-decoration: none; font-weight: 700; }
  .mp-header { text-align: center; margin-bottom: 40px; }
  .mp-header .mp-icon { font-size: 44px; margin-bottom: 12px; }
  .mp-header h1 { font-size: clamp(26px, 4vw, 38px); font-weight: 900; color: #2f3437; margin: 0 0 12px; }
  .mp-header p { color: #6b7280; font-size: 16px; max-width: 640px; margin: 0 auto; }
  .mp-empty {
    text-align: center; padding: 60px 24px;
    background: #fafbfc; border: 1px dashed #e1e4e8; border-radius: 18px;
    color: #8b8f96; font-size: 15px;
  }

  /* ---------- نوار فیلتر: جستجو + دسته‌بندی ---------- */
  .mp-filters {
    display: flex; flex-wrap: wrap; align-items: center; gap: 14px;
    margin-bottom: 28px;
  }
  /* پیلِ یکپارچه‌ی جستجو — مستقل از جهت (RTL/LTR) */
  .mp-search {
    display: flex; align-items: center; flex: 0 0 auto;
    background: #fff; border: 1.5px solid #e1e4e8; border-radius: 12px; overflow: hidden;
    transition: border-color .2s ease;
  }
  .mp-search:focus-within { border-color: #008f8a; }
  .mp-search input[type="search"] {
    width: 260px; max-width: 60vw; font-family: inherit; font-size: 14px; color: #2f3437;
    background: transparent; border: 0; outline: none; padding: 10px 14px;
  }
  .mp-search button {
    border: 0; cursor: pointer; flex-shrink: 0;
    background: #008f8a; color: #fff; font-size: 16px; padding: 10px 16px;
  }
  .mp-chips { display: flex; flex-wrap: wrap; gap: 8px; }
  .mp-chip {
    font-size: 13px; font-weight: 700; color: #4b5563; text-decoration: none;
    background: #fff; border: 1.5px solid #e6e8ea; border-radius: 99px; padding: 7px 16px;
    transition: border-color .2s ease, color .2s ease, background .2s ease;
  }
  .mp-chip:hover { border-color: #008f8a; color: #2f3437; }
  .mp-chip.active { background: #008f8a; border-color: #008f8a; color: #fff; }
  .mp-result-note { font-size: 13.5px; color: #6b7280; margin-bottom: 22px; }
  .mp-result-clear { color: #008f8a; font-weight: 700; text-decoration: none; margin-right: 8px; }
  .mp-result-clear:hover { text-decoration: underline; }

  /* ---------- چیدمان تماشا: پلیر بزرگ + فهرست ویدیوها ---------- */
  .mp-watch {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 26px;
    align-items: start;
    margin-bottom: 48px;
  }
  @media (max-width: 1000px) { .mp-watch { grid-template-columns: 1fr; } }

  /* ستون پلیر — هنگام اسکرولِ فهرست در جای خود می‌ماند */
  .mp-watch-main { position: sticky; top: 16px; min-width: 0; }
  @media (max-width: 1000px) { .mp-watch-main { position: static; } }

  .mp-player {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #000;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 14px 36px rgba(0,0,0,.16);
  }
  .mp-player > iframe,
  .mp-player > video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; display: block; }
  .mp-player-poster {
    position: absolute; inset: 0; width: 100%; height: 100%;
    border: 0; padding: 0; margin: 0; cursor: pointer; background: #000; display: block;
  }
  .mp-player-poster img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
  .mp-poster-icon {
    position: absolute; inset: 0; display: grid; place-items: center;
    font-size: 64px; background: linear-gradient(135deg, #1f2937, #111827);
  }
  .mp-play {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    width: 72px; height: 72px; display: grid; place-items: center; border-radius: 50%;
    background: rgba(0,0,0,.55); color: #fff; font-size: 28px; padding-right: 4px;
    transition: background .2s ease, transform .2s ease; pointer-events: none;
  }
  .mp-player-poster:hover .mp-play { background: var(--cta-orange, #faa61a); transform: translate(-50%, -50%) scale(1.06); }

  .mp-player-info { margin-top: 16px; }
  .mp-player-info h2 { font-size: 20px; font-weight: 800; color: #2f3437; margin: 0 0 8px; }
  .mp-player-info p { font-size: 14px; color: #6b7280; line-height: 1.9; margin: 0 0 10px; white-space: pre-line; }

  /* فهرست ویدیوها */
  .mp-watch-list { min-width: 0; }
  .mp-watch-list h3 { font-size: 15px; font-weight: 800; color: #2f3437; margin: 0 0 14px; }
  .mp-watch-scroller { display: flex; flex-direction: column; gap: 10px; }
  .mp-pl-item {
    display: flex; gap: 12px; align-items: stretch; text-align: right;
    background: #fff; border: 1px solid #ecedf0; border-radius: 12px; padding: 8px;
    cursor: pointer; font-family: inherit;
    transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
  }
  .mp-pl-item:hover { box-shadow: 0 6px 16px rgba(0,0,0,.07); }
  .mp-pl-item.active { border-color: var(--cta-orange, #faa61a); background: #fff8ef; }
  .mp-pl-thumb {
    position: relative; flex: 0 0 132px; width: 132px; aspect-ratio: 16 / 9;
    border-radius: 8px; overflow: hidden; background: #e9edf0;
    display: grid; place-items: center; font-size: 30px; color: #6b7280;
  }
  .mp-pl-thumb img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
  .mp-pl-meta { display: flex; flex-direction: column; justify-content: center; min-width: 0; gap: 4px; }
  .mp-pl-title {
    font-size: 13.5px; font-weight: 700; color: #2f3437; line-height: 1.6;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  }
  .mp-pl-provider { font-size: 11.5px; color: #8b8f96; }

  .mp-others-title { font-size: 19px; font-weight: 800; color: #2f3437; margin: 0 0 20px; }

  /* ---------- چیدمان مدرنِ پادکست‌ها ---------- */
  .mp-pod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(440px, 1fr)); gap: 24px; }
  @media (max-width: 540px) { .mp-pod-grid { grid-template-columns: 1fr; } }
  .mp-pod-card {
    position: relative;
    display: flex; flex-direction: column;
    background: #fff;
    border: 1px solid #eef0f2;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 6px 22px rgba(20,20,40,.05);
    transition: transform .25s ease, box-shadow .25s ease;
  }
  /* نوار رنگیِ نازک در لبه‌ی کارت (لهجه‌ی مدرن، هم‌رنگِ برندِ سایت) */
  .mp-pod-card::before {
    content: ""; position: absolute; inset-block: 0; inset-inline-start: 0; width: 4px;
    background: linear-gradient(180deg, #05a8a2, #00736f);
  }
  .mp-pod-card:hover { transform: translateY(-5px); box-shadow: 0 18px 40px rgba(20,20,40,.11); }
  .mp-pod-head { display: flex; gap: 14px; align-items: center; padding: 20px 22px 14px; }
  .mp-pod-cover {
    flex: 0 0 64px; width: 64px; height: 64px; border-radius: 16px; overflow: hidden;
    display: grid; place-items: center; font-size: 30px; color: #fff;
    background: linear-gradient(135deg, #05a8a2, #008f8a);
    box-shadow: 0 10px 22px -8px rgba(0, 143, 138,.55);
  }
  .mp-pod-cover img { width: 100%; height: 100%; object-fit: cover; }
  .mp-pod-meta { min-width: 0; flex: 1; }
  .mp-pod-title {
    font-size: 16.5px; font-weight: 800; color: #2f3437; margin: 0 0 8px; line-height: 1.55;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  }
  .mp-pod-tags { display: flex; flex-wrap: wrap; gap: 6px; }
  .mp-pod-provider { font-size: 11.5px; font-weight: 700; color: #00736f; background: rgba(0, 143, 138,.12); padding: 3px 10px; border-radius: 99px; }
  .mp-pod-cat { font-size: 11.5px; font-weight: 700; color: #6b7280; background: #f1f3f5; padding: 3px 10px; border-radius: 99px; }
  .mp-pod-desc {
    font-size: 13.5px; color: #8b8f96; line-height: 1.95; margin: 0; padding: 0 22px 16px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  }
  .mp-pod-player { margin-top: auto; padding: 0 16px 16px; }
  .mp-pod-player .mxp-embed { border-radius: 14px; overflow: hidden; }
  .mp-pod-player .mxp-embed--audio { background: #f6f7f9; padding: 10px 12px; }
  .mp-pod-foot { padding: 0 22px 20px; margin-top: auto; }

  /* ---------- شبکه‌ی سایر محتوا (صوت / فایل / گالری) ---------- */
  .mp-items { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 26px; }
  @media (max-width: 460px) { .mp-items { grid-template-columns: 1fr; } }
  .mp-item {
    display: flex; flex-direction: column; background: #fff; border: 1px solid #ecedf0;
    border-radius: 16px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,.04);
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .mp-item:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.08); }
  .mp-item-thumb {
    aspect-ratio: 16 / 9; width: 100%; object-fit: cover; background: #f1f3f5;
    display: grid; place-items: center; font-size: 44px;
  }
  .mp-item-body { padding: 18px 18px 20px; display: flex; flex-direction: column; flex: 1; }
  .mp-item-body h3 { font-size: 16.5px; font-weight: 800; color: #2f3437; margin: 0 0 8px; }
  .mp-item-body p { font-size: 13.5px; color: #8b8f96; line-height: 1.9; margin: 0 0 16px; }
  .mp-item-link {
    margin-top: auto; display: inline-flex; align-items: center; gap: 6px; align-self: flex-start;
    font-size: 14px; font-weight: 700; color: #fff; text-decoration: none;
    background: var(--cta-orange, #faa61a); padding: 9px 16px; border-radius: 10px;
    transition: background .2s ease, transform .15s ease;
  }
  .mp-item-link:hover { transform: translateY(-1px); }
  .mp-item-link--book {
    background: linear-gradient(135deg, #05a8a2, #008f8a);
  }
  .mp-item-link--book:hover {
    background: linear-gradient(135deg, #008f8a, #00736f);
  }
  .mp-item-thumb--book {
    aspect-ratio: 3 / 4;
    max-height: 280px;
    object-fit: contain;
    background: #f8f9fa;
    padding: 12px;
  }
  .mp-book-badge {
    display: inline-block;
    align-self: flex-start;
    font-size: 11px;
    font-weight: 700;
    color: #00736f;
    background: rgba(0, 143, 138,.12);
    padding: 2px 8px;
    border-radius: 6px;
    margin-bottom: 8px;
  }
  .mp-item-source { margin-top: auto; align-self: flex-start; font-size: 12.5px; font-weight: 700; color: #8b8f96; text-decoration: none; }
  .mp-item-source:hover { color: var(--cta-orange, #faa61a); }

  /* پخش‌کننده‌ی صوت (درون‌خطی) */
  .mxp-embed { width: 100%; background: #000; }
  .mxp-embed--audio { background: #f1f3f5; padding: 14px 16px; }
  .mxp-embed--audio > audio { width: 100%; display: block; }
  .mxp-embed--audio > iframe { width: 100%; height: 180px; border: 0; display: block; }

  /* ==========================================================================
   * گالری مکسا: ویترین آلبوم‌ها (Albums Hub)
   * ========================================================================== */
  .mp-albums-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 28px;
    margin-bottom: 48px;
  }
  @media (max-width: 480px) { .mp-albums-grid { grid-template-columns: 1fr; } }

  .mp-album-card {
    background: #fff;
    border: 1px solid #eef0f2;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 6px 20px rgba(0,0,0,.04);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
  }
  .mp-album-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(0,0,0,.10);
    border-color: #008f8a;
  }
  .mp-album-cover-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: #111827;
    display: block;
    text-decoration: none;
  }
  .mp-album-cover-wrap img {
    position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s ease;
  }
  .mp-album-card:hover .mp-album-cover-wrap img {
    transform: scale(1.05);
  }
  .mp-album-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.65) 100%);
    opacity: 0;
    transition: opacity .3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .mp-album-card:hover .mp-album-cover-overlay { opacity: 1; }
  .mp-album-enter-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--cta-orange, #faa61a); color: #fff;
    font-size: 13.5px; font-weight: 800; padding: 10px 20px; border-radius: 99px;
    box-shadow: 0 8px 20px rgba(0,0,0,.3);
    transform: translateY(10px);
    transition: transform .25s ease;
  }
  .mp-album-card:hover .mp-album-enter-btn { transform: translateY(0); }
  .mp-album-count-badge {
    position: absolute; bottom: 14px; right: 14px;
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(17, 24, 39, 0.85); color: #fff;
    font-size: 12px; font-weight: 800; padding: 5px 12px; border-radius: 99px;
    backdrop-filter: blur(8px);
  }
  .mp-album-cat-badge {
    position: absolute; top: 14px; right: 14px;
    background: rgba(0, 143, 138, 0.9); color: #fff;
    font-size: 11.5px; font-weight: 800; padding: 4px 10px; border-radius: 8px;
    backdrop-filter: blur(8px);
  }
  .mp-album-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex: 1;
  }
  .mp-album-title {
    font-size: 17px;
    font-weight: 900;
    margin: 0 0 10px;
    line-height: 1.5;
  }
  .mp-album-title a { color: #2f3437; text-decoration: none; transition: color .2s; }
  .mp-album-title a:hover { color: #008f8a; }
  .mp-album-desc {
    font-size: 13.5px;
    color: #6b7280;
    line-height: 1.8;
    margin: 0 0 16px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .mp-album-footer {
    margin-top: auto;
    padding-top: 14px;
    border-top: 1px solid #f1f3f5;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .mp-album-date { font-size: 12px; color: #9ca3af; }
  .mp-album-cta {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 800; color: #008f8a; text-decoration: none;
    transition: transform .2s ease;
  }
  .mp-album-cta:hover { transform: translateX(-4px); }

  /* ==========================================================================
   * گالری مکسا: نمای آلبوم و چیدمان موزاییک/بنتو متنوع (Mosaic Bento Grid)
   * ========================================================================== */
  .mp-album-view-header {
    background: #fff;
    border: 1px solid #eef0f2;
    border-radius: 22px;
    padding: 28px 32px;
    margin-bottom: 32px;
    box-shadow: 0 6px 24px rgba(0,0,0,.04);
  }
  .mp-album-back-link {
    display: inline-flex; align-items: center; gap: 8px;
    color: #4b5563; font-size: 13.5px; font-weight: 800; text-decoration: none;
    background: #f3f4f6; padding: 8px 16px; border-radius: 99px; margin-bottom: 18px;
    transition: background .2s, color .2s;
  }
  .mp-album-back-link:hover { background: #008f8a; color: #fff; }
  .mp-album-view-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 12px; }
  .mp-album-badge-cat {
    background: rgba(0, 143, 138, 0.12); color: #00736f;
    font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 99px;
  }
  .mp-album-badge-count {
    background: #f3f4f6; color: #374151;
    font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 99px;
    display: inline-flex; align-items: center; gap: 6px;
  }
  .mp-album-view-title { font-size: clamp(22px, 3.5vw, 32px); font-weight: 900; color: #1f2937; margin: 0 0 12px; }
  .mp-album-view-desc { font-size: 14.5px; color: #4b5563; line-height: 1.9; margin: 0; max-width: 860px; }

  /* گرید خلاقانه، متنوع و غیرکسل‌کننده (Mosaic Asymmetric Grid) */
  .mp-mosaic-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-auto-rows: 240px;
    grid-auto-flow: dense;
    gap: 22px;
    margin-bottom: 48px;
  }
  @media (max-width: 1100px) {
    .mp-mosaic-grid { grid-template-columns: repeat(3, 1fr); grid-auto-rows: 220px; gap: 18px; }
  }
  @media (max-width: 768px) {
    .mp-mosaic-grid { grid-template-columns: repeat(2, 1fr); grid-auto-rows: 190px; gap: 14px; }
  }
  @media (max-width: 480px) {
    .mp-mosaic-grid { grid-template-columns: 1fr; grid-auto-rows: auto; }
  }

  /* اسپَن‌ها و ابعاد کارت‌های گرید برای ایجاد ریتم دیداری */
  .mp-mosaic-item {
    margin: 0;
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    background: #fff;
    border: 1px solid #eef0f2;
    box-shadow: 0 4px 16px rgba(0,0,0,.04);
    display: flex;
    flex-direction: column;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .mp-mosaic-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(0,0,0,.10);
  }
  .mp-span-wide { grid-column: span 2; }
  .mp-span-tall { grid-row: span 2; }
  .mp-span-featured { grid-column: span 2; grid-row: span 2; }
  @media (max-width: 768px) {
    .mp-span-wide { grid-column: span 2; }
    .mp-span-featured { grid-column: span 2; grid-row: span 2; }
  }
  @media (max-width: 480px) {
    .mp-span-wide, .mp-span-tall, .mp-span-featured { grid-column: span 1; grid-row: auto; }
  }

  .mp-mosaic-thumb {
    position: relative;
    width: 100%;
    flex: 1;
    min-height: 160px;
    background: #111827;
    cursor: pointer;
    overflow: hidden;
  }
  .mp-mosaic-thumb img {
    position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s ease;
  }
  .mp-mosaic-item:hover .mp-mosaic-thumb img {
    transform: scale(1.05);
  }
  .mp-mosaic-hover-overlay {
    position: absolute; inset: 0;
    background: rgba(0, 0, 0, 0.35);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .25s ease;
  }
  .mp-mosaic-item:hover .mp-mosaic-hover-overlay { opacity: 1; }
  .mp-mosaic-zoom-ic {
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(255,255,255,0.9); color: #111827;
    display: grid; place-items: center;
    transform: scale(0.85); transition: transform .25s ease;
  }
  .mp-mosaic-item:hover .mp-mosaic-zoom-ic { transform: scale(1); }

  /* نیمچه کپشن زیر هر عکس (ظریف، مینیمال و خوانا) */
  .mp-mosaic-caption {
    padding: 10px 14px 12px;
    font-size: 12.5px;
    font-weight: 600;
    color: #4b5563;
    line-height: 1.6;
    background: #fff;
    border-top: 1px solid #f3f4f6;
    white-space: normal;
    text-align: right;
  }

  /* دکمه و نوار پیشرفت بارگذاری مرحله‌ای تصاویر (Load More) */
  .mp-load-more-wrap {
    max-width: 440px;
    margin: 10px auto 56px;
    text-align: center;
    background: #fff;
    border: 1px solid #eef0f2;
    padding: 22px 28px;
    border-radius: 20px;
    box-shadow: 0 4px 18px rgba(0,0,0,.03);
  }
  .mp-load-more-info {
    font-size: 13.5px;
    font-weight: 700;
    color: #4b5563;
    margin-bottom: 12px;
  }
  .mp-load-progress {
    height: 7px;
    background: #f1f3f5;
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: 18px;
  }
  .mp-load-bar {
    height: 100%;
    background: linear-gradient(90deg, #008f8a, #faa61a);
    border-radius: 99px;
    transition: width .4s ease;
  }
  .mp-load-more-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #fff;
    border: 2px solid #008f8a;
    color: #008f8a;
    font-size: 14px;
    font-weight: 800;
    padding: 11px 26px;
    border-radius: 99px;
    cursor: pointer;
    font-family: inherit;
    transition: all .25s ease;
  }
  .mp-load-more-btn:hover {
    background: #008f8a;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0, 143, 138, 0.28);
  }
  @keyframes mpFadeInUp {
    from { opacity: 0; transform: translateY(22px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .mp-animate-in {
    animation: mpFadeInUp .45s cubic-bezier(.2,.8,.2,1) forwards;
  }

  /* ==========================================================================
   * لایت‌باکس تمام‌صفحه تعاملی (Interactive Lightbox)
   * ========================================================================== */
  .mp-lightbox {
    position: fixed; inset: 0; z-index: 99999;
    display: none; align-items: center; justify-content: center;
    padding: 20px;
  }
  .mp-lightbox.is-open { display: flex; }
  .mp-lb-backdrop {
    position: absolute; inset: 0;
    background: rgba(10, 15, 25, 0.92);
    backdrop-filter: blur(10px);
  }
  .mp-lb-dialog {
    position: relative; z-index: 2;
    width: 100%; max-width: 1200px;
    display: flex; flex-direction: column;
    max-height: 94vh;
  }
  .mp-lb-close {
    position: absolute; top: -14px; left: -14px;
    width: 44px; height: 44px; border-radius: 50%;
    background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25);
    font-size: 20px; cursor: pointer; display: grid; place-items: center;
    transition: background .2s, transform .15s; z-index: 10;
  }
  .mp-lb-close:hover { background: var(--cta-orange, #faa61a); transform: scale(1.08); }
  .mp-lb-stage {
    position: relative;
    display: flex; align-items: center; justify-content: space-between;
    width: 100%;
    max-height: calc(88vh - 80px);
    gap: 16px;
  }
  .mp-lb-img-wrap {
    flex: 1; display: flex; align-items: center; justify-content: center;
    max-height: calc(88vh - 80px); overflow: hidden;
  }
  .mp-lb-img-wrap img {
    max-width: 100%; max-height: calc(88vh - 80px);
    object-fit: contain; border-radius: 14px;
    box-shadow: 0 20px 50px rgba(0,0,0,.5);
    transition: opacity .2s ease;
  }
  .mp-lb-nav {
    flex-shrink: 0; width: 50px; height: 50px; border-radius: 50%;
    background: rgba(255,255,255,0.14); color: #fff; border: 1px solid rgba(255,255,255,0.2);
    display: grid; place-items: center; cursor: pointer;
    transition: background .2s, transform .2s;
  }
  .mp-lb-nav:hover { background: var(--cta-orange, #faa61a); transform: scale(1.1); }
  @media (max-width: 600px) {
    .mp-lb-nav { width: 40px; height: 40px; }
  }
  .mp-lb-footer {
    margin-top: 14px;
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
    gap: 12px; background: rgba(255,255,255,0.08); padding: 12px 20px; border-radius: 14px;
    color: #fff; border: 1px solid rgba(255,255,255,0.12);
  }
  .mp-lb-caption { font-size: 14px; font-weight: 700; color: #f3f4f6; flex: 1; min-width: 200px; }
  .mp-lb-meta { display: flex; align-items: center; gap: 16px; }
  .mp-lb-counter { font-size: 13px; color: #9ca3af; font-weight: 700; }
  .mp-lb-download {
    display: inline-flex; align-items: center; gap: 6px;
    color: #fff; text-decoration: none; font-size: 12.5px; font-weight: 800;
    background: #008f8a; padding: 6px 14px; border-radius: 8px;
    transition: background .2s;
  }
  .mp-lb-download:hover { background: #00736f; }
</style>

<div class="mp-wrap">
<?php if ($galleryAlbum): ?>
  <!-- نمای اختصاصی آلبوم گالری مکسا -->
  <div class="mp-breadcrumb">
    <a href="/macsapedia">مکساپدیا</a> / <a href="<?= e($mpBase) ?>"><?= e($mpSection['title']) ?></a> / <?= e($galleryAlbum['title']) ?>
  </div>

  <div class="mp-album-view-header">
    <a href="<?= e($mpBase) ?>" class="mp-album-back-link">
      <?= iconoir('arrow-right', '', 16) ?> بازگشت به همه آلبوم‌های گالری
    </a>
    <div class="mp-album-view-meta">
      <?php if (!empty($galleryAlbum['category'])): ?>
        <span class="mp-album-badge-cat"><?= e($galleryAlbum['category']) ?></span>
      <?php endif; ?>
      <span class="mp-album-badge-count">
        <?= iconoir('media-image', '', 14) ?> <?= fa_digits(count($galleryPhotos)) ?> تصویر
      </span>
      <?php if (!empty($galleryAlbum['created_at'])): ?>
        <span class="mp-album-date"><?= fa_digits(date('Y/m/d', strtotime($galleryAlbum['created_at']))) ?></span>
      <?php endif; ?>
    </div>
    <h1 class="mp-album-view-title"><?= e($galleryAlbum['title']) ?></h1>
    <?php if (!empty($galleryAlbum['description'])): ?>
      <p class="mp-album-view-desc"><?= nl2br(e($galleryAlbum['description'])) ?></p>
    <?php endif; ?>
  </div>

  <?php if (empty($galleryPhotos)): ?>
    <div class="mp-empty">
      هنوز تصویری برای این آلبوم آپلود نشده است.
    </div>
  <?php else: ?>
    <?php
      $rhythm = ['normal', 'wide', 'normal', 'tall', 'normal', 'normal', 'featured', 'normal'];
    ?>
    <div class="mp-mosaic-grid">
      <?php foreach ($galleryPhotos as $idx => $p): ?>
        <?php
          $span = $p['layout_span'] ?? 'normal';
          if ($span === 'normal') {
            $span = $rhythm[$idx % count($rhythm)];
          }
          $spanClass = 'mp-span-' . $span;
          $isHidden = ($idx >= 24);
        ?>
        <figure class="mp-mosaic-item <?= $spanClass ?><?= $isHidden ? ' mp-item-hidden' : '' ?>"
                onclick="openPhotoLightbox(<?= $idx ?>)"
                role="button" tabindex="0"
                aria-label="مشاهده تصویر <?= $idx + 1 ?>"
                <?= $isHidden ? 'style="display:none;"' : '' ?>>
          <div class="mp-mosaic-thumb">
            <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['caption'] ?: $galleryAlbum['title']) ?>" loading="lazy">
            <div class="mp-mosaic-hover-overlay">
              <span class="mp-mosaic-zoom-ic"><?= iconoir('expand', '', 20) ?></span>
            </div>
          </div>
          <?php if (!empty($p['caption'])): ?>
            <figcaption class="mp-mosaic-caption">
              <?= e($p['caption']) ?>
            </figcaption>
          <?php endif; ?>
        </figure>
      <?php endforeach; ?>
    </div>

    <?php if (count($galleryPhotos) > 24): ?>
      <div class="mp-load-more-wrap" id="mpLoadMoreWrap">
        <div class="mp-load-more-info">
          نمایش <span id="mpLoadedCount">۲۴</span> از <span id="mpTotalCount"><?= fa_digits(count($galleryPhotos)) ?></span> تصویر
        </div>
        <div class="mp-load-progress">
          <div class="mp-load-bar" id="mpLoadBar" style="width: <?= round((24 / count($galleryPhotos)) * 100) ?>%;"></div>
        </div>
        <button type="button" class="mp-load-more-btn" id="mpLoadMoreBtn" onclick="loadMorePhotos()">
          <?= iconoir('refresh-double', '', 18) ?> مشاهده تصاویر بیشتر
        </button>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- لایت‌باکس تعاملی تمام‌صفحه -->
  <div class="mp-lightbox" id="mpLightbox" role="dialog" aria-modal="true" aria-label="نمایش تصویر بزرگ">
    <div class="mp-lb-backdrop" onclick="closePhotoLightbox()"></div>
    <div class="mp-lb-dialog">
      <button type="button" class="mp-lb-close" onclick="closePhotoLightbox()" aria-label="بستن">✕</button>
      <div class="mp-lb-stage">
        <button type="button" class="mp-lb-nav mp-lb-prev" onclick="prevPhotoLightbox()" aria-label="تصویر قبلی">❮</button>
        <div class="mp-lb-img-wrap">
          <img id="mpLbImg" src="" alt="">
        </div>
        <button type="button" class="mp-lb-nav mp-lb-next" onclick="nextPhotoLightbox()" aria-label="تصویر بعدی">❯</button>
      </div>
      <div class="mp-lb-footer">
        <div class="mp-lb-caption" id="mpLbCaption"></div>
        <div class="mp-lb-meta">
          <span class="mp-lb-counter" id="mpLbCounter"></span>
          <a id="mpLbDownload" class="mp-lb-download" href="" download target="_blank" rel="noopener">
            <?= iconoir('download', '', 14) ?> دانلود کیفیت اصلی
          </a>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- نمای عمومی بخش‌های مکساپدیا (شامل هاب گالری مکسا و سایر بخش‌ها) -->
  <div class="mp-breadcrumb">
    <a href="/macsapedia">مکساپدیا</a> / <?= e($mpSection['title']) ?>
  </div>

  <div class="mp-header">
    <div class="mp-icon"><?= $mpSection['icon'] ?></div>
    <h1><?= e($mpSection['title']) ?></h1>
    <p><?= e($mpSection['desc']) ?></p>
  </div>

  <!-- نوار فیلتر: جستجو + دسته‌بندی‌ها -->
  <div class="mp-filters">
    <form class="mp-search" method="get" action="<?= e($mpBase) ?>" role="search">
      <?php if ($mpCat !== ''): ?>
        <input type="hidden" name="cat" value="<?= e($mpCat) ?>">
      <?php endif; ?>
      <input type="search" name="q" value="<?= e($mpQ) ?>" placeholder="<?= $mpSlug === 'gallery' ? 'جستجو در آلبوم‌های تصاویر…' : 'جستجو در این بخش…' ?>" aria-label="جستجو">
      <button type="submit" aria-label="جستجو"><?= iconoir('search', '', 18) ?></button>
    </form>

    <?php if (!empty($mpCats)): ?>
      <div class="mp-chips">
        <a class="mp-chip<?= $mpCat === '' ? ' active' : '' ?>" href="<?= e($mpChipUrl('')) ?>">همه</a>
        <?php foreach ($mpCats as $c): ?>
          <a class="mp-chip<?= $c === $mpCat ? ' active' : '' ?>" href="<?= e($mpChipUrl($c)) ?>"><?= e($c) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ($mpFiltered): ?>
    <div class="mp-result-note">
      نتایج
      <?php if ($mpQ !== ''): ?> برای «<?= e($mpQ) ?>»<?php endif; ?>
      <?php if ($mpCat !== ''): ?> در دسته‌ی «<?= e($mpCat) ?>»<?php endif; ?>
      — <?= fa_digits(count($mpItems)) ?> <?= $mpSlug === 'gallery' ? 'آلبوم' : 'مورد' ?>
      <a class="mp-result-clear" href="<?= e($mpBase) ?>">پاک کردن فیلتر</a>
    </div>
  <?php endif; ?>

  <?php if (empty($mpItems)): ?>
    <div class="mp-empty">
      <?php if ($mpFiltered): ?>
        <?= $mpSlug === 'gallery' ? 'آلبومی با این مشخصات یافت نشد.' : 'موردی با این جستجو/دسته‌بندی پیدا نشد.' ?>
      <?php else: ?>
        <?= $mpSlug === 'gallery' ? 'به‌زودی آلبوم‌های تصاویر اضافه خواهد شد.' : 'به‌زودی محتوای این بخش اضافه می‌شود.' ?>
      <?php endif; ?>
    </div>
  <?php elseif ($mpSlug === 'gallery'): ?>
    <div class="mp-albums-grid">
      <?php foreach ($mpItems as $alb): ?>
        <?php
          $pCount = $galleryCounts[$alb['id']] ?? 0;
          $albumLink = e($mpBase) . '?album=' . (int)$alb['id'];
        ?>
        <article class="mp-album-card">
          <a class="mp-album-cover-wrap" href="<?= $albumLink ?>">
            <?php if (!empty($alb['thumbnail'])): ?>
              <img src="<?= e($alb['thumbnail']) ?>" alt="<?= e($alb['title']) ?>" loading="lazy">
            <?php else: ?>
              <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #008f8a, #004d40); display: grid; place-items: center; color: rgba(255,255,255,0.7); font-size: 48px;">
                <?= $mpSection['icon'] ?>
              </div>
            <?php endif; ?>
            <div class="mp-album-cover-overlay">
              <span class="mp-album-enter-btn">
                مشاهده آلبوم <?= iconoir('arrow-left', '', 14) ?>
              </span>
            </div>
            <?php if (!empty($alb['category'])): ?>
              <span class="mp-album-cat-badge"><?= e($alb['category']) ?></span>
            <?php endif; ?>
            <span class="mp-album-count-badge">
              <?= iconoir('media-image', '', 13) ?> <?= fa_digits($pCount) ?> عکس
            </span>
          </a>
          <div class="mp-album-body">
            <h2 class="mp-album-title">
              <a href="<?= $albumLink ?>"><?= e($alb['title']) ?></a>
            </h2>
            <?php if (!empty($alb['description'])): ?>
              <p class="mp-album-desc"><?= e($alb['description']) ?></p>
            <?php endif; ?>
            <div class="mp-album-footer">
              <span class="mp-album-date">
                <?= !empty($alb['created_at']) ? fa_digits(date('Y/m/d', strtotime($alb['created_at']))) : '' ?>
              </span>
              <a class="mp-album-cta" href="<?= $albumLink ?>">
                ورود به آلبوم <?= iconoir('arrow-left', '', 14) ?>
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>

    <?php if (!empty($mpVideos)): $first = $mpVideos[0]; $fem = $first['_em']; ?>
      <div class="mp-watch">

        <!-- پلیر بزرگ (می‌ماند سرِ جایش هنگام اسکرول) -->
        <div class="mp-watch-main">
          <div class="mp-player" id="mpPlayer">
            <button type="button" class="mp-player-poster" id="mpPlayerPoster"
                    data-index="0"
                    data-type="<?= $fem['type'] === 'video' ? 'video' : 'iframe' ?>"
                    data-src="<?= e($fem['src']) ?>"
                    data-title="<?= e($first['title']) ?>"
                    data-desc="<?= e($first['description'] ?? '') ?>"
                    data-source="<?= e($first['url'] ?? '') ?>"
                    aria-label="پخش: <?= e($first['title']) ?>">
              <?php if (!empty($fem['poster'])): ?>
                <img src="<?= e($fem['poster']) ?>" alt="" loading="lazy">
              <?php elseif (!empty($first['thumbnail'])): ?>
                <img src="<?= e($first['thumbnail']) ?>" alt="" loading="lazy">
              <?php else: ?>
                <span class="mp-poster-icon"><?= $mpSection['icon'] ?></span>
              <?php endif; ?>
              <span class="mp-play">▶</span>
            </button>
          </div>
          <div class="mp-player-info">
            <h2 id="mpPlayerTitle"><?= e($first['title']) ?></h2>
            <p id="mpPlayerDesc"<?= empty($first['description']) ? ' style="display:none"' : '' ?>><?= e($first['description'] ?? '') ?></p>
            <a id="mpPlayerSource" class="mp-item-source" target="_blank" rel="noopener"
               href="<?= e($first['url'] ?? '') ?>"<?= empty($first['url']) ? ' style="display:none"' : '' ?>>باز کردن در منبع <?= iconoir('arrow-up-right', '', 14) ?></a>
          </div>
        </div>

        <!-- فهرست ویدیوها -->
        <aside class="mp-watch-list">
          <h3><?= fa_digits(count($mpVideos)) ?> ویدیو</h3>
          <div class="mp-watch-scroller">
            <?php foreach ($mpVideos as $i => $v): $vem = $v['_em']; ?>
              <button type="button" class="mp-pl-item<?= $i === 0 ? ' active' : '' ?>"
                      data-index="<?= $i ?>"
                      data-type="<?= $vem['type'] === 'video' ? 'video' : 'iframe' ?>"
                      data-src="<?= e($vem['src']) ?>"
                      data-title="<?= e($v['title']) ?>"
                      data-desc="<?= e($v['description'] ?? '') ?>"
                      data-source="<?= e($v['url'] ?? '') ?>">
                <span class="mp-pl-thumb">
                  <?php if (!empty($vem['poster'])): ?>
                    <img src="<?= e($vem['poster']) ?>" alt="" loading="lazy">
                  <?php elseif (!empty($v['thumbnail'])): ?>
                    <img src="<?= e($v['thumbnail']) ?>" alt="" loading="lazy">
                  <?php else: ?>
                    <?= $mpSection['icon'] ?>
                  <?php endif; ?>
                </span>
                <span class="mp-pl-meta">
                  <span class="mp-pl-title"><?= e($v['title']) ?></span>
                  <span class="mp-pl-provider"><?= e($vem['provider']) ?></span>
                </span>
              </button>
            <?php endforeach; ?>
          </div>
        </aside>
      </div>
    <?php endif; ?>

    <?php if (!empty($mpOthers)): ?>
      <?php if (!empty($mpVideos)): ?>
        <h2 class="mp-others-title">سایر محتوا</h2>
      <?php endif; ?>

      <?php if ($mpSlug === 'podcasts'): ?>
        <!-- چیدمان مدرنِ پادکست‌ها -->
        <div class="mp-pod-grid">
          <?php foreach ($mpOthers as $it): $em = $it['_em']; ?>
            <article class="mp-pod-card">
              <div class="mp-pod-head">
                <div class="mp-pod-cover">
                  <?php if (!empty($it['thumbnail'])): ?>
                    <img src="<?= e($it['thumbnail']) ?>" alt="" loading="lazy">
                  <?php else: ?>
                    <span><?= iconoir('microphone', '', 32) ?></span>
                  <?php endif; ?>
                </div>
                <div class="mp-pod-meta">
                  <h3 class="mp-pod-title"><?= e($it['title']) ?></h3>
                  <div class="mp-pod-tags">
                    <?php if ($em): ?><span class="mp-pod-provider"><?= iconoir('headset', '', 14) ?> <?= e($em['provider']) ?></span><?php endif; ?>
                    <?php if (!empty($it['category'])): ?><span class="mp-pod-cat"><?= e($it['category']) ?></span><?php endif; ?>
                  </div>
                </div>
              </div>

              <?php if (!empty($it['description'])): ?>
                <p class="mp-pod-desc"><?= nl2br(e($it['description'])) ?></p>
              <?php endif; ?>

              <?php if ($em): ?>
                <div class="mp-pod-player"><?= maxapedia_embed_html((string)$it['url'], (string)$it['title']) ?></div>
              <?php elseif (!empty($it['url'])): ?>
                <div class="mp-pod-foot">
                  <a class="mp-item-link" href="<?= e($it['url']) ?>" target="_blank" rel="noopener">شنیدن <?= iconoir('arrow-up-right', '', 14) ?></a>
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <div class="mp-items">
          <?php foreach ($mpOthers as $it): $em = $it['_em']; ?>
            <article class="mp-item">
              <?php $isPortraitCard = in_array($mpSlug, ['books', 'brochures'], true); ?>
              <?php if ($em && $em['kind'] === 'audio'): ?>
                <?= maxapedia_embed_html((string)$it['url'], (string)$it['title']) ?>
              <?php elseif (!empty($it['thumbnail'])): ?>
                <img class="mp-item-thumb <?= $isPortraitCard ? 'mp-item-thumb--book' : '' ?>" src="<?= e($it['thumbnail']) ?>" alt="<?= e($it['title']) ?>" loading="lazy">
              <?php else: ?>
                <div class="mp-item-thumb <?= $isPortraitCard ? 'mp-item-thumb--book' : '' ?>"><?= $mpSection['icon'] ?></div>
              <?php endif; ?>
              <div class="mp-item-body">
                <?php
                  $bookExt = strtolower(pathinfo(parse_url($it['url'] ?? '', PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                  $isDownloadable = in_array($bookExt, ['pdf', 'epub', 'mobi', 'doc', 'docx', 'zip', 'rar'], true) || in_array($mpSlug, ['books', 'brochures'], true);
                ?>
                <?php if ($bookExt): ?>
                  <?php if ($mpSlug === 'books'): ?>
                    <span class="mp-book-badge">کتاب <?= strtoupper(e($bookExt)) ?></span>
                  <?php elseif ($mpSlug === 'brochures'): ?>
                    <span class="mp-book-badge">بروشور <?= strtoupper(e($bookExt)) ?></span>
                  <?php elseif ($isDownloadable): ?>
                    <span class="mp-book-badge">فایل <?= strtoupper(e($bookExt)) ?></span>
                  <?php endif; ?>
                <?php endif; ?>
                <h3><?= e($it['title']) ?></h3>
                <?php if (!empty($it['description'])): ?>
                  <p><?= nl2br(e($it['description'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($it['url'])): ?>
                  <?php if ($em): ?>
                    <a class="mp-item-source" href="<?= e($it['url']) ?>" target="_blank" rel="noopener">باز کردن در منبع <?= iconoir('arrow-up-right', '', 14) ?></a>
                  <?php elseif ($mpSlug === 'books'): ?>
                    <a class="mp-item-link mp-item-link--book" href="<?= e($it['url']) ?>" target="_blank" rel="noopener"><?= iconoir('download', '', 15) ?> دانلود و مطالعه کتاب</a>
                  <?php elseif ($mpSlug === 'brochures'): ?>
                    <a class="mp-item-link mp-item-link--book" href="<?= e($it['url']) ?>" target="_blank" rel="noopener"><?= iconoir('download', '', 15) ?> دانلود و مطالعه بروشور</a>
                  <?php elseif ($isDownloadable): ?>
                    <a class="mp-item-link mp-item-link--book" href="<?= e($it['url']) ?>" target="_blank" rel="noopener"><?= iconoir('download', '', 15) ?> دانلود فایل (<?= strtoupper(e($bookExt ?: 'PDF')) ?>)</a>
                  <?php else: ?>
                    <a class="mp-item-link" href="<?= e($it['url']) ?>" target="_blank" rel="noopener">مشاهده <?= iconoir('arrow-up-right', '', 14) ?></a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  <?php endif; ?>
<?php endif; /* end if $galleryAlbum */ ?>
</div>

<script>
(function () {
  var player   = document.getElementById('mpPlayer');
  if (!player) return;
  var elTitle  = document.getElementById('mpPlayerTitle');
  var elDesc   = document.getElementById('mpPlayerDesc');
  var elSource = document.getElementById('mpPlayerSource');
  var items    = Array.prototype.slice.call(document.querySelectorAll('.mp-pl-item'));

  function activate(index) {
    items.forEach(function (b) {
      b.classList.toggle('active', parseInt(b.getAttribute('data-index'), 10) === index);
    });
  }

  function load(el) {
    var type   = el.getAttribute('data-type');
    var src    = el.getAttribute('data-src');
    var title  = el.getAttribute('data-title')  || '';
    var desc   = el.getAttribute('data-desc')   || '';
    var source = el.getAttribute('data-source') || '';
    var index  = parseInt(el.getAttribute('data-index'), 10) || 0;

    var node;
    if (type === 'video') {
      node = document.createElement('video');
      node.src = src; node.controls = true; node.autoplay = true;
      node.setAttribute('playsinline', '');
    } else {
      node = document.createElement('iframe');
      node.src = src + (src.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1';
      node.title = title;
      node.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
      node.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
      node.setAttribute('allowfullscreen', '');
    }
    player.innerHTML = '';
    player.appendChild(node);

    if (elTitle) elTitle.textContent = title;
    if (elDesc)  { elDesc.textContent = desc; elDesc.style.display = desc ? '' : 'none'; }
    if (elSource){ if (source) { elSource.href = source; elSource.style.display = ''; } else { elSource.style.display = 'none'; } }
    activate(index);

    // روی موبایل (تک‌ستونه) پلیر را به دید بیاور
    if (window.matchMedia('(max-width: 1000px)').matches) {
      player.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  var poster = document.getElementById('mpPlayerPoster');
  if (poster) poster.addEventListener('click', function () { load(poster); });
  items.forEach(function (b) { b.addEventListener('click', function () { load(b); }); });
})();
</script>

<?php if ($galleryAlbum && !empty($galleryPhotos)): ?>
<script>
(function () {
  var photos = <?= json_encode(array_map(function($p) {
    return [
      'src'     => $p['image_url'],
      'caption' => $p['caption'] ?? '',
    ];
  }, $galleryPhotos), JSON_UNESCAPED_UNICODE) ?>;

  var currentIndex = 0;
  var lightbox = document.getElementById('mpLightbox');
  var lbImg = document.getElementById('mpLbImg');
  var lbCaption = document.getElementById('mpLbCaption');
  var lbCounter = document.getElementById('mpLbCounter');
  var lbDownload = document.getElementById('mpLbDownload');

  function faDigits(str) {
    var d = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    return String(str).replace(/[0-9]/g, function(w) { return d[+w]; });
  }

  function render(index) {
    if (!photos || !photos.length) return;
    if (index < 0) index = photos.length - 1;
    if (index >= photos.length) index = 0;
    currentIndex = index;

    var cur = photos[currentIndex];
    if (lbImg) {
      lbImg.style.opacity = '0';
      lbImg.src = cur.src;
      lbImg.onload = function() { lbImg.style.opacity = '1'; };
    }
    if (lbCaption) {
      lbCaption.textContent = cur.caption || '';
      lbCaption.style.display = cur.caption ? '' : 'none';
    }
    if (lbCounter) {
      lbCounter.textContent = 'تصویر ' + faDigits(currentIndex + 1) + ' از ' + faDigits(photos.length);
    }
    if (lbDownload) {
      lbDownload.href = cur.src;
    }
  }

  window.openPhotoLightbox = function(index) {
    render(index);
    if (lightbox) {
      lightbox.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
  };

  window.closePhotoLightbox = function() {
    if (lightbox) {
      lightbox.classList.remove('is-open');
      document.body.style.overflow = '';
    }
  };

  window.prevPhotoLightbox = function() {
    render(currentIndex - 1);
  };

  window.nextPhotoLightbox = function() {
    render(currentIndex + 1);
  };

  document.addEventListener('keydown', function(e) {
    if (!lightbox || !lightbox.classList.contains('is-open')) return;
    if (e.key === 'Escape') {
      window.closePhotoLightbox();
    } else if (e.key === 'ArrowRight') {
      window.prevPhotoLightbox();
    } else if (e.key === 'ArrowLeft') {
      window.nextPhotoLightbox();
    }
  });

  /* بارگذاری مرحله‌ای تصاویر (Load More) */
  var totalPhotos = photos.length;
  var batchSize = 24;
  var currentVisibleCount = Math.min(batchSize, totalPhotos);

  window.loadMorePhotos = function() {
    var hiddenItems = Array.prototype.slice.call(document.querySelectorAll('.mp-item-hidden'));
    if (!hiddenItems.length) return;

    var nextBatch = hiddenItems.slice(0, batchSize);
    nextBatch.forEach(function(el) {
      el.classList.remove('mp-item-hidden');
      el.style.display = 'flex';
      el.classList.add('mp-animate-in');
    });

    currentVisibleCount += nextBatch.length;
    var loadedCountEl = document.getElementById('mpLoadedCount');
    var loadBarEl = document.getElementById('mpLoadBar');
    if (loadedCountEl) loadedCountEl.textContent = faDigits(currentVisibleCount);
    if (loadBarEl) loadBarEl.style.width = Math.min(100, Math.round((currentVisibleCount / totalPhotos) * 100)) + '%';

    if (currentVisibleCount >= totalPhotos) {
      var wrap = document.getElementById('mpLoadMoreWrap');
      if (wrap) wrap.style.display = 'none';
    }
  };
})();
</script>
<?php endif; ?>

<?php
render_dashboard_component('footer', get_defined_vars());
