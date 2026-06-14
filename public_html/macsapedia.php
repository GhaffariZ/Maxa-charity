<?php
require __DIR__ . '/dashboard/components/header/component.php';

$mpSections = [
    [
        'slug'  => 'videos',
        'title' => 'ویدیوهای آموزشی',
        'desc'  => 'مجموعه ویدیوهای آموزشی مکسا برای آشنایی با بیماری‌های نادر و مراقبت از بیماران.',
        'icon'  => '🎬',
    ],
    [
        'slug'  => 'brochures',
        'title' => 'بروشورها',
        'desc'  => 'بروشورهای اطلاع‌رسانی و آموزشی قابل دانلود و چاپ.',
        'icon'  => '📄',
    ],
    [
        'slug'  => 'books',
        'title' => 'کتاب‌های آموزشی',
        'desc'  => 'کتاب‌ها و جستارهای تخصصی در حوزه بیماری‌های نادر.',
        'icon'  => '📚',
    ],
    [
        'slug'  => 'podcasts',
        'title' => 'پادکست‌ها',
        'desc'  => 'گفت‌وگوها و برنامه‌های صوتی مکسا.',
        'icon'  => '🎙️',
    ],
    [
        'slug'  => 'clips',
        'title' => 'کلیپ‌های مکسی',
        'desc'  => 'کلیپ‌های کوتاه و انگیزشی مکسی.',
        'icon'  => '🎞️',
    ],
    [
        'slug'  => 'gallery',
        'title' => 'گالری مکسی',
        'desc'  => 'تصاویر و خاطرات تصویری از فعالیت‌های مکسا.',
        'icon'  => '🖼️',
    ],
];
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
  .mp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
  }
  .mp-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: right;
    background: #fff;
    border: 1px solid #ecedf0;
    border-radius: 18px;
    padding: 28px 24px;
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
    font-size: 36px;
    margin-bottom: 16px;
    line-height: 1;
  }
  .mp-card h3 {
    font-size: 19px;
    font-weight: 800;
    color: #2f3437;
    margin: 0 0 8px;
  }
  .mp-card p {
    font-size: 14px;
    color: #8b8f96;
    line-height: 1.8;
    margin: 0 0 16px;
  }
  .mp-cta {
    margin-top: auto;
    font-size: 14px;
    font-weight: 700;
    color: var(--cta-orange, #f5a623);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
</style>

<div class="mp-wrap">
  <div class="mp-header">
    <h1>مکساپدیا</h1>
    <p>مرکز محتوای آموزشی مکسا؛ ویدیوها، کتاب‌ها، پادکست‌ها و کلیپ‌هایی برای آشنایی بیشتر با بیماری‌های نادر و فعالیت‌های انجمن.</p>
  </div>

  <div class="mp-grid">
    <?php foreach ($mpSections as $s): ?>
      <a class="mp-card" href="/macsapedia/<?= htmlspecialchars($s['slug'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="mp-icon"><?= $s['icon'] ?></div>
        <h3><?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p><?= htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8') ?></p>
        <span class="mp-cta">مشاهده ←</span>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';
