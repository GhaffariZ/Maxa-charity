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
$mpItems   = ($pdo) ? maxapedia_items($pdo, $mpSlug, true) : [];

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

  <?php if (empty($mpItems)): ?>
    <div class="mp-empty">به‌زودی محتوای این بخش اضافه می‌شود.</div>
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