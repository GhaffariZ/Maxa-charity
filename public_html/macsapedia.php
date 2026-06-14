<?php
/* صفحه‌ی اصلیِ مکساپدیا — شش کارت که به بخش‌های محتوایی لینک می‌دهند.
   تعریف بخش‌ها و شمارش محتوا از لایه‌ی داده‌ی مشترک می‌آید. */
require __DIR__ . '/dashboard/maxapedia-db.php';
$mpCounts = ($pdo) ? maxapedia_counts($pdo) : [];

$pageTitle = 'مکساپدیا';
require __DIR__ . '/dashboard/components/header/component.php';
?>

<style>
  .mp-wrap {
    max-width: var(--cta-container, 1440px);
    margin: 0 auto;
    padding: 48px 20px 80px;
    font-family: 'Vazirmatn', sans-serif;
  }
  .mp-header {
    text-align: center;
    margin-bottom: 40px;
  }
  .mp-header h1 {
    font-size: clamp(28px, 4vw, 42px);
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
  /* شش کارت در یک ردیف روی دسکتاپ، و واکنش‌گرا در صفحه‌های کوچک‌تر */
  .mp-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 18px;
  }
  @media (max-width: 1100px) { .mp-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
  @media (max-width: 700px)  { .mp-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 460px)  { .mp-grid { grid-template-columns: 1fr; } }
  .mp-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    background: #fff;
    border: 1px solid #ecedf0;
    border-radius: 18px;
    padding: 26px 16px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 4px 14px rgba(0,0,0,.04);
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
  }
  .mp-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0,0,0,.08);
    border-color: var(--cta-orange, #f5a623);
  }
  .mp-icon {
    font-size: 34px;
    margin-bottom: 14px;
    line-height: 1;
  }
  .mp-card h3 {
    font-size: 16px;
    font-weight: 800;
    color: #2f3437;
    margin: 0 0 8px;
  }
  .mp-card p {
    font-size: 13px;
    color: #8b8f96;
    line-height: 1.9;
    margin: 0 0 16px;
  }
  .mp-cta {
    margin-top: auto;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--cta-orange, #f5a623);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .mp-count {
    font-size: 12px;
    color: #b0b4ba;
    margin-bottom: 12px;
  }
</style>

<div class="mp-wrap">
  <div class="mp-header">
    <h1>مکساپدیا</h1>
    <p>مرکز محتوای آموزشی مکسا؛ ویدیوها، کتاب‌ها، پادکست‌ها و کلیپ‌هایی برای آشنایی بیشتر با بیماری‌های نادر و فعالیت‌های انجمن.</p>
  </div>

  <div class="mp-grid">
    <?php foreach (MAXAPEDIA_SECTIONS as $slug => $s): ?>
      <a class="mp-card" href="/macsapedia/<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>">
        <div class="mp-icon"><?= $s['icon'] ?></div>
        <h3><?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p><?= htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8') ?></p>
        <div class="mp-count"><?= fa_digits($mpCounts[$slug] ?? 0) ?> مورد</div>
        <span class="mp-cta">مشاهده ←</span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';
