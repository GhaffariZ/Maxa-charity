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
  .mp-breadcrumb {
    font-size: 14px;
    color: #8b8f96;
    margin-bottom: 20px;
  }
  .mp-breadcrumb a {
    color: var(--cta-orange, #f5a623);
    text-decoration: none;
    font-weight: 700;
  }
  .mp-header {
    text-align: center;
    margin-bottom: 40px;
  }
  .mp-header .mp-icon {
    font-size: 44px;
    margin-bottom: 12px;
  }
  .mp-header h1 {
    font-size: clamp(26px, 4vw, 38px);
    font-weight: 900;
    color: #2f3437;
    margin: 0 0 12px;
  }
  .mp-header p {
    color: #6b7280;
    font-size: 16px;
    max-width: 640px;
    margin: 0 auto;
  }
  .mp-empty {
    text-align: center;
    padding: 60px 24px;
    background: #fafbfc;
    border: 1px dashed #e1e4e8;
    border-radius: 18px;
    color: #8b8f96;
    font-size: 15px;
  }
  .mp-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 22px;
  }
  .mp-item {
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid #ecedf0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0,0,0,.04);
    transition: transform .2s ease, box-shadow .2s ease;
  }
  .mp-item:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.08); }
  .mp-item-thumb {
    aspect-ratio: 16 / 9;
    width: 100%;
    object-fit: cover;
    background: #f1f3f5;
    display: grid;
    place-items: center;
    font-size: 44px;
  }
  .mp-item-body { padding: 18px 18px 20px; display: flex; flex-direction: column; flex: 1; }
  .mp-item-body h3 { font-size: 16.5px; font-weight: 800; color: #2f3437; margin: 0 0 8px; }
  .mp-item-body p { font-size: 13.5px; color: #8b8f96; line-height: 1.9; margin: 0 0 16px; }
  .mp-item-link {
    margin-top: auto;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    align-self: flex-start;
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
    background: var(--cta-orange, #f5a623);
    padding: 9px 16px;
    border-radius: 10px;
  }
  /* پخش‌کننده‌ی امبد (ویدیو/صوت) */
  .mxp-embed { width: 100%; background: #000; }
  .mxp-embed--video { position: relative; width: 100%; aspect-ratio: 16 / 9; }
  .mxp-embed--video > iframe,
  .mxp-embed--video > video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; display: block; }
  .mxp-embed--audio { background: #f1f3f5; padding: 14px 16px; }
  .mxp-embed--audio > audio { width: 100%; display: block; }
  .mxp-embed--audio > iframe { width: 100%; height: 180px; border: 0; display: block; }
  .mp-item-source {
    margin-top: auto;
    align-self: flex-start;
    font-size: 12.5px;
    font-weight: 700;
    color: #8b8f96;
    text-decoration: none;
  }
  .mp-item-source:hover { color: var(--cta-orange, #f5a623); }
</style>

<div class="mp-wrap">
  <div class="mp-breadcrumb">
    <a href="/macsapedia">مکساپدیا</a> / <?= htmlspecialchars($mpSection['title'], ENT_QUOTES, 'UTF-8') ?>
  </div>

  <div class="mp-header">
    <div class="mp-icon"><?= $mpSection['icon'] ?></div>
    <h1><?= htmlspecialchars($mpSection['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars($mpSection['desc'], ENT_QUOTES, 'UTF-8') ?></p>
  </div>

  <?php if (empty($mpItems)): ?>
    <div class="mp-empty">به‌زودی محتوای این بخش اضافه می‌شود.</div>
  <?php else: ?>
    <div class="mp-items">
      <?php foreach ($mpItems as $it): ?>
        <?php $mpEmbed = maxapedia_embed_html((string)($it['url'] ?? ''), (string)$it['title']); ?>
        <article class="mp-item">
          <?php if ($mpEmbed): ?>
            <?= $mpEmbed ?>
          <?php elseif (!empty($it['thumbnail'])): ?>
            <img class="mp-item-thumb" src="<?= htmlspecialchars($it['thumbnail'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($it['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
          <?php else: ?>
            <div class="mp-item-thumb"><?= $mpSection['icon'] ?></div>
          <?php endif; ?>
          <div class="mp-item-body">
            <h3><?= htmlspecialchars($it['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <?php if (!empty($it['description'])): ?>
              <p><?= nl2br(htmlspecialchars($it['description'], ENT_QUOTES, 'UTF-8')) ?></p>
            <?php endif; ?>
            <?php if (!empty($it['url'])): ?>
              <?php if ($mpEmbed): ?>
                <a class="mp-item-source" href="<?= htmlspecialchars($it['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">باز کردن در منبع ↗</a>
              <?php else: ?>
                <a class="mp-item-link" href="<?= htmlspecialchars($it['url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">مشاهده ↗</a>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';
