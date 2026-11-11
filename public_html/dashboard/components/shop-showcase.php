<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/site-locale.php';
require_once __DIR__ . '/../../core/shop-db.php';

if (!function_exists('shop_format_toman')) {
    function shop_format_toman(float|int|string $price): string {
        $num = (int)$price;
        $formatted = number_format($num);
        return strtr($formatted, [
            '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',','=>'٬'
        ]);
    }
}

// Ensure database connection
$featuredProducts = [];
try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $dbPath = dirname(__DIR__, 2) . '/core/database.php';
        if (file_exists($dbPath)) {
            require_once $dbPath;
        }
    }
    if (isset($pdo) && ($pdo instanceof PDO)) {
        shop_ensure_schema($pdo);
        $allActive = shop_get_products($pdo);
        $featuredProducts = array_slice($allActive, 0, 4);
    }
} catch (Throwable $e) {
    $featuredProducts = [];
}
?>

<section class="maxa-shop-showcase-section" aria-labelledby="shop-showcase-title">
  <style>
    .maxa-shop-showcase-section {
      width: min(var(--cta-container, 1400px), 100%);
      margin-inline: auto;
      padding: 64px 20px 72px;
      direction: var(--site-direction, rtl);
      font-family: 'Vazirmatn', sans-serif;
      box-sizing: border-box;
    }
    .msc-head {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      margin-bottom: 40px;
    }
    .msc-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 16px;
      border-radius: 999px;
      background: #fef3c7;
      color: #92400e;
      font-size: 13.5px;
      font-weight: 700;
      margin-bottom: 14px;
    }
    .msc-title {
      font-size: clamp(22px, 3.2vw, 34px);
      font-weight: 900;
      color: #0f172a;
      line-height: 1.4;
      margin: 0 0 12px;
    }
    .msc-desc {
      max-width: 680px;
      color: #64748b;
      font-size: 15px;
      line-height: 1.8;
      margin: 0;
    }
    .msc-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 24px;
      margin-bottom: 40px;
    }
    .msc-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
      position: relative;
    }
    .msc-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
      border-color: #cbd5e1;
    }
    .msc-img-wrap {
      position: relative;
      width: 100%;
      aspect-ratio: 4/3;
      background: #f1f5f9;
      overflow: hidden;
    }
    .msc-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.35s ease;
      display: block;
    }
    .msc-card:hover .msc-img {
      transform: scale(1.04);
    }
    .msc-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      color: #94a3b8;
    }
    .msc-cat {
      position: absolute;
      top: 12px;
      right: 12px;
      background: rgba(255, 255, 255, 0.94);
      backdrop-filter: blur(8px);
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 11.5px;
      font-weight: 700;
      color: #475569;
      box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }
    .msc-body {
      padding: 18px 18px 20px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }
    .msc-p-title {
      font-size: 16px;
      font-weight: 800;
      color: #0f172a;
      margin: 0 0 10px;
      line-height: 1.5;
    }
    .msc-p-title a {
      color: inherit;
      text-decoration: none;
    }
    .msc-p-title a:hover {
      color: #d97706;
    }
    .msc-p-meta {
      margin-top: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding-top: 14px;
      border-top: 1px dashed #e2e8f0;
    }
    .msc-price-wrap {
      display: flex;
      flex-direction: column;
    }
    .msc-price-val {
      font-size: 17px;
      font-weight: 900;
      color: #0f172a;
    }
    .msc-price-unit {
      font-size: 11px;
      color: #64748b;
    }
    .msc-btn-view {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 10px;
      background: #f8fafc;
      border: 1px solid #cbd5e1;
      color: #1e293b;
      font-size: 13px;
      font-weight: 700;
      text-decoration: none;
      transition: background 0.2s, color 0.2s, border-color 0.2s;
    }
    .msc-btn-view:hover {
      background: #faa61a;
      border-color: #faa61a;
      color: #ffffff;
    }
    .msc-cta-footer {
      text-align: center;
    }
    .msc-btn-all {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: #faa61a;
      color: #ffffff;
      font-weight: 800;
      font-size: 15px;
      padding: 14px 32px;
      border-radius: 14px;
      text-decoration: none;
      box-shadow: 0 8px 20px rgba(250, 166, 26, 0.35);
      transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
    }
    .msc-btn-all:hover {
      background: #e99508;
      transform: translateY(-2px);
      box-shadow: 0 12px 24px rgba(250, 166, 26, 0.45);
      color: #ffffff;
    }
    .msc-empty-state {
      background: #f8fafc;
      border: 2px dashed #cbd5e1;
      border-radius: 18px;
      padding: 40px 20px;
      text-align: center;
      color: #64748b;
    }
  </style>

  <div class="msc-head">
    <div class="msc-badge">
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <path d="M16 10a4 4 0 0 1-8 0"></path>
      </svg>
      <span><?= site_e('فروشگاه خیریه مکسا') ?></span>
    </div>
    <h2 id="shop-showcase-title" class="msc-title"><?= site_e('خرید هدفمند برای حمایت از بیماران مبتلا به سرطان') ?></h2>
    <p class="msc-desc"><?= site_e('تمام عواید حاصل از فروش محصولات این بخش، مستقیماً صرف ارائه خدمات رایگان مراقبت‌های حمایتی و تسکینی برای بیماران مبتلا به سرطان و خانواده‌های آنان می‌شود.') ?></p>
  </div>

  <?php if (!empty($featuredProducts)): ?>
    <div class="msc-grid">
      <?php foreach ($featuredProducts as $item): ?>
        <?php
          $pId = (int)$item['id'];
          $pTitle = htmlspecialchars($item['title'] ?? '');
          $pCat = htmlspecialchars($item['category_title'] ?? 'فروشگاه خیریه');
          $pPrice = (float)($item['price'] ?? 0);
          $pImg = !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : '';
        ?>
        <article class="msc-card">
          <div class="msc-img-wrap">
            <?php if ($pImg): ?>
              <img src="<?= $pImg ?>" alt="<?= $pTitle ?>" class="msc-img" loading="lazy">
            <?php else: ?>
              <div class="msc-placeholder">
                <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                  <line x1="3" y1="6" x2="21" y2="6"></line>
                  <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
              </div>
            <?php endif; ?>
            <span class="msc-cat"><?= $pCat ?></span>
          </div>

          <div class="msc-body">
            <h3 class="msc-p-title">
              <a href="/shop.php#prod-<?= $pId ?>"><?= $pTitle ?></a>
            </h3>

            <div class="msc-p-meta">
              <div class="msc-price-wrap">
                <span class="msc-price-val"><?= shop_format_toman($pPrice) ?></span>
                <span class="msc-price-unit"><?= site_e('تومان') ?></span>
              </div>
              <a href="/shop.php#prod-<?= $pId ?>" class="msc-btn-view">
                <span><?= site_e('مشاهده جزئیات') ?></span>
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M15 18l-6-6 6-6"/>
                </svg>
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="msc-empty-state">
      <p style="font-size: 15px; margin: 0 0 16px;"><?= site_e('محصولات فروشگاه خیریه مکسا به زودی بارگذاری خواهند شد.') ?></p>
      <a href="/shop.php" class="msc-btn-view" style="display: inline-flex;"><?= site_e('ورود به فروشگاه خیریه') ?></a>
    </div>
  <?php endif; ?>

  <div class="msc-cta-footer">
    <a href="/shop.php" class="msc-btn-all">
      <span><?= site_e('مشاهده همه محصولات فروشگاه خیریه') ?></span>
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M15 18l-6-6 6-6"/>
      </svg>
    </a>
  </div>
</section>
