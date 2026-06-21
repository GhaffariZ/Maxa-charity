<?php
/* صفحه‌ی عمومیِ یک بخش از مکساپدیا — محتوای منتشرشده‌ی همان بخش را نمایش می‌دهد. */
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

$pageTitle = $mpSection['title'] . ' — مکساپدیا';
require __DIR__ . '/dashboard/components/header/component.php';
?>

<style>
  .mp-wrap {
    max-width: var(--cta-container, 1440px);
    margin: 0 auto;
    padding: 48px 20px 80px;
    font-family: 'Vazirmatn', sans-serif;
  }
  .mp-breadcrumb { font-size: 14px; color: #8b8f96; margin-bottom: 20px; }
  .mp-breadcrumb a { color: var(--cta-orange, #f5a623); text-decoration: none; font-weight: 700; }
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
  .mp-search:focus-within { border-color: #0899A9; }
  .mp-search input[type="search"] {
    width: 260px; max-width: 60vw; font-family: inherit; font-size: 14px; color: #2f3437;
    background: transparent; border: 0; outline: none; padding: 10px 14px;
  }
  .mp-search button {
    border: 0; cursor: pointer; flex-shrink: 0;
    background: #0899A9; color: #fff; font-size: 16px; padding: 10px 16px;
  }
  .mp-chips { display: flex; flex-wrap: wrap; gap: 8px; }
  .mp-chip {
    font-size: 13px; font-weight: 700; color: #4b5563; text-decoration: none;
    background: #fff; border: 1.5px solid #e6e8ea; border-radius: 99px; padding: 7px 16px;
    transition: border-color .2s ease, color .2s ease, background .2s ease;
  }
  .mp-chip:hover { border-color: #0899A9; color: #2f3437; }
  .mp-chip.active { background: #0899A9; border-color: #0899A9; color: #fff; }
  .mp-result-note { font-size: 13.5px; color: #6b7280; margin-bottom: 22px; }
  .mp-result-clear { color: #0899A9; font-weight: 700; text-decoration: none; margin-right: 8px; }
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
  .mp-player-poster:hover .mp-play { background: var(--cta-orange, #f5a623); transform: translate(-50%, -50%) scale(1.06); }

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
  .mp-pl-item.active { border-color: var(--cta-orange, #f5a623); background: #fff8ef; }
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
    background: linear-gradient(180deg, #0ab2c5, #067d8a);
  }
  .mp-pod-card:hover { transform: translateY(-5px); box-shadow: 0 18px 40px rgba(20,20,40,.11); }
  .mp-pod-head { display: flex; gap: 14px; align-items: center; padding: 20px 22px 14px; }
  .mp-pod-cover {
    flex: 0 0 64px; width: 64px; height: 64px; border-radius: 16px; overflow: hidden;
    display: grid; place-items: center; font-size: 30px; color: #fff;
    background: linear-gradient(135deg, #0ab2c5, #0899A9);
    box-shadow: 0 10px 22px -8px rgba(8,153,169,.55);
  }
  .mp-pod-cover img { width: 100%; height: 100%; object-fit: cover; }
  .mp-pod-meta { min-width: 0; flex: 1; }
  .mp-pod-title {
    font-size: 16.5px; font-weight: 800; color: #2f3437; margin: 0 0 8px; line-height: 1.55;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  }
  .mp-pod-tags { display: flex; flex-wrap: wrap; gap: 6px; }
  .mp-pod-provider { font-size: 11.5px; font-weight: 700; color: #067d8a; background: rgba(8,153,169,.12); padding: 3px 10px; border-radius: 99px; }
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
    background: var(--cta-orange, #f5a623); padding: 9px 16px; border-radius: 10px;
  }
  .mp-item-source { margin-top: auto; align-self: flex-start; font-size: 12.5px; font-weight: 700; color: #8b8f96; text-decoration: none; }
  .mp-item-source:hover { color: var(--cta-orange, #f5a623); }

  /* پخش‌کننده‌ی صوت (درون‌خطی) */
  .mxp-embed { width: 100%; background: #000; }
  .mxp-embed--audio { background: #f1f3f5; padding: 14px 16px; }
  .mxp-embed--audio > audio { width: 100%; display: block; }
  .mxp-embed--audio > iframe { width: 100%; height: 180px; border: 0; display: block; }
</style>

<div class="mp-wrap">
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
      <input type="search" name="q" value="<?= e($mpQ) ?>" placeholder="جستجو در این بخش…" aria-label="جستجو">
      <button type="submit" aria-label="جستجو">🔍</button>
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
      — <?= fa_digits(count($mpItems)) ?> مورد
      <a class="mp-result-clear" href="<?= e($mpBase) ?>">پاک کردن فیلتر</a>
    </div>
  <?php endif; ?>

  <?php if (empty($mpItems)): ?>
    <div class="mp-empty">
      <?php if ($mpFiltered): ?>
        موردی با این جستجو/دسته‌بندی پیدا نشد.
      <?php else: ?>
        به‌زودی محتوای این بخش اضافه می‌شود.
      <?php endif; ?>
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
               href="<?= e($first['url'] ?? '') ?>"<?= empty($first['url']) ? ' style="display:none"' : '' ?>>باز کردن در منبع ↗</a>
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
                    <span>🎙️</span>
                  <?php endif; ?>
                </div>
                <div class="mp-pod-meta">
                  <h3 class="mp-pod-title"><?= e($it['title']) ?></h3>
                  <div class="mp-pod-tags">
                    <?php if ($em): ?><span class="mp-pod-provider">🎧 <?= e($em['provider']) ?></span><?php endif; ?>
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
                  <a class="mp-item-link" href="<?= e($it['url']) ?>" target="_blank" rel="noopener">شنیدن ↗</a>
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <div class="mp-items">
          <?php foreach ($mpOthers as $it): $em = $it['_em']; ?>
            <article class="mp-item">
              <?php if ($em && $em['kind'] === 'audio'): ?>
                <?= maxapedia_embed_html((string)$it['url'], (string)$it['title']) ?>
              <?php elseif (!empty($it['thumbnail'])): ?>
                <img class="mp-item-thumb" src="<?= e($it['thumbnail']) ?>" alt="<?= e($it['title']) ?>" loading="lazy">
              <?php else: ?>
                <div class="mp-item-thumb"><?= $mpSection['icon'] ?></div>
              <?php endif; ?>
              <div class="mp-item-body">
                <h3><?= e($it['title']) ?></h3>
                <?php if (!empty($it['description'])): ?>
                  <p><?= nl2br(e($it['description'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($it['url'])): ?>
                  <?php if ($em): ?>
                    <a class="mp-item-source" href="<?= e($it['url']) ?>" target="_blank" rel="noopener">باز کردن در منبع ↗</a>
                  <?php else: ?>
                    <a class="mp-item-link" href="<?= e($it['url']) ?>" target="_blank" rel="noopener">مشاهده ↗</a>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  <?php endif; ?>
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

<?php
require __DIR__ . '/dashboard/components/footer/component.php';