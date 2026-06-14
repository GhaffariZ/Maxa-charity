<?php
$mpSectionsData = [
    'videos' => [
        'title' => 'ویدیوهای آموزشی',
        'desc'  => 'مجموعه ویدیوهای آموزشی مکسا برای آشنایی با بیماری‌های نادر و مراقبت از بیماران.',
        'icon'  => '🎬',
    ],
    'brochures' => [
        'title' => 'بروشورها',
        'desc'  => 'بروشورهای اطلاع‌رسانی و آموزشی قابل دانلود و چاپ.',
        'icon'  => '📄',
    ],
    'books' => [
        'title' => 'کتاب‌های آموزشی',
        'desc'  => 'کتاب‌ها و جستارهای تخصصی در حوزه بیماری‌های نادر.',
        'icon'  => '📚',
    ],
    'podcasts' => [
        'title' => 'پادکست‌ها',
        'desc'  => 'گفت‌وگوها و برنامه‌های صوتی مکسا.',
        'icon'  => '🎙️',
    ],
    'clips' => [
        'title' => 'کلیپ‌های مکسی',
        'desc'  => 'کلیپ‌های کوتاه و انگیزشی مکسی.',
        'icon'  => '🎞️',
    ],
    'gallery' => [
        'title' => 'گالری مکسی',
        'desc'  => 'تصاویر و خاطرات تصویری از فعالیت‌های مکسا.',
        'icon'  => '🖼️',
    ],
];

$mpSlug = $_GET['section'] ?? '';

if (!isset($mpSectionsData[$mpSlug])) {
    http_response_code(404);
    include __DIR__ . '/404.html';
    exit;
}

$mpSection = $mpSectionsData[$mpSlug];

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

  <div class="mp-empty">
    به‌زودی محتوای این بخش اضافه می‌شود.
  </div>
</div>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';
