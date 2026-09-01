<?php
/**
 * صفحه اختصاصی خواندن تک‌روایت امید مکسا
 * Single Macsa Story Detail Page
 */

$dbCfg = require __DIR__ . '/core/db-config.php';
try {
    $pdo = new PDO("mysql:host={$dbCfg['host']};dbname={$dbCfg['name']};charset=utf8mb4", $dbCfg['user'], $dbCfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}

$id = (int)($_GET['id'] ?? 0);
$story = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM `macsa_stories` WHERE `id` = ? AND `status` = 'published' LIMIT 1");
    $stmt->execute([$id]);
    $story = $stmt->fetch();
}

// Fallback: If story ID is invalid or not provided, fetch the latest published story
if (!$story) {
    $fallbackStmt = $pdo->query("SELECT * FROM `macsa_stories` WHERE `status` = 'published' ORDER BY `sort_order` ASC, `id` DESC LIMIT 1");
    $story = $fallbackStmt->fetch();
}

// If database is completely empty
if (!$story) {
    $story = [
        'id' => 1,
        'title' => 'زندگی تا آخرین لحظه',
        'narrator_name' => 'دکتر زهرا جعفری',
        'narrator_role' => 'روانشناس مراقبت درمنزل شعبه تهران',
        'tag' => 'روایت کادر درمان',
        'excerpt' => 'یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود...',
        'content' => "یکی از بهترین خاطرات من در بخش مراقبت در منزل، مرتبط با مرجان، خانم جوانی بود که سال‌ها در آمریکا زندگی کرده بود. همسر ایشان ایرانی‌الاصل بود، اما در آمریکا بزرگ شده بود.\n\nدر جریان درمان و همراهی با این خانواده، شاهد پیوند عمیق عاطفی و امید بی‌پایان به زندگی بودیم که تا آخرین لحظات نیز جریان داشت. تیم مراقبت در منزل مکسا با ارائه مشاوره‌های منظم روانشناختی، آموزش مهارت‌های تاب‌آوری به همراهان، و ایجاد فضایی امن و آرام، تلاش کرد تا کیفیت زندگی بیمار و اطرافیان در بالاترین سطح ممکن حفظ شود.\n\nتجربه این خانواده نشان داد که مراقبت تسکینی تنها افزودن روز به زندگی نیست، بلکه بخشیدن زندگی، معنا و آرامش به لحظه‌لحظه روزهاست.",
        'image' => '',
        'read_time' => '۴ دقیقه مطالعه',
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Fetch other related stories (excluding current)
$relatedStmt = $pdo->prepare("SELECT * FROM `macsa_stories` WHERE `status` = 'published' AND `id` != ? ORDER BY `sort_order` ASC, `id` DESC LIMIT 3");
$relatedStmt->execute([$story['id']]);
$relatedStories = $relatedStmt->fetchAll();

$pageTitle = 'روایت امید مکسا | ' . htmlspecialchars($story['title']);
require __DIR__ . '/dashboard/components/header/component.php';
?>

<style>
/* Self-hosted Vazirmatn variable font */
@font-face {
  font-family: 'Vazirmatn';
  src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
       url('/webfont/Vazirmatn[wght].woff2') format('woff2');
  font-weight: 100 900;
  font-style: normal;
  font-display: swap;
}

:root {
  --ms-primary: #0899A9;
  --ms-primary-dark: #067c89;
  --ms-primary-light: #e0f4f5;
  --ms-accent: #f3a21b;
  --ms-accent-dark: #e08c0c;
  --ms-text-main: #1d2b2d;
  --ms-text-muted: #6b7c80;
  --ms-bg-card: rgba(255, 255, 255, 0.85);
  --ms-border: rgba(8, 153, 169, 0.15);
  --ms-radius-lg: 24px;
}

body {
  font-family: 'Vazirmatn', sans-serif;
  color: var(--ms-text-main);
  background-color: #f8fcfa;
}

/* Ambient Background */
.story-global-bg {
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background:
    radial-gradient(circle at 10% 15%, rgba(8, 153, 169, 0.09), transparent 45%),
    radial-gradient(circle at 90% 65%, rgba(243, 162, 27, 0.11), transparent 50%),
    linear-gradient(180deg, #fffdf9 0%, #f4faf9 100%);
}

.story-wrapper {
  max-width: 960px;
  margin: 0 auto;
  padding: 40px 20px 80px;
  direction: rtl;
}

/* Breadcrumbs */
.story-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13.5px;
  color: var(--ms-text-muted);
  margin-bottom: 28px;
  flex-wrap: wrap;
}
.story-breadcrumb a {
  color: var(--ms-text-muted);
  text-decoration: none;
  transition: color 0.2s;
}
.story-breadcrumb a:hover {
  color: var(--ms-primary);
}
.story-breadcrumb span.sep {
  opacity: 0.5;
}
.story-breadcrumb span.current {
  color: var(--ms-primary);
  font-weight: 700;
}

/* Main Story Card */
.story-main-card {
  background: var(--ms-bg-card);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--ms-border);
  border-radius: var(--ms-radius-lg);
  padding: 48px 44px;
  box-shadow: 0 15px 45px rgba(0, 0, 0, 0.04), 0 30px 80px rgba(8, 153, 169, 0.06);
  position: relative;
  overflow: hidden;
}
@media (max-width: 768px) {
  .story-main-card {
    padding: 30px 20px;
    border-radius: 20px;
  }
}

/* Top Meta Header */
.story-meta-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.story-tag-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: linear-gradient(135deg, var(--ms-accent), var(--ms-accent-dark));
  color: #fff;
  font-size: 12.5px;
  font-weight: 700;
  padding: 6px 14px;
  border-radius: 20px;
  box-shadow: 0 4px 12px rgba(243, 162, 27, 0.3);
}

.story-read-time {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--ms-text-muted);
  background: rgba(8, 153, 169, 0.08);
  padding: 5px 12px;
  border-radius: 12px;
}

/* Headline */
.story-headline {
  font-size: clamp(24px, 3.8vw, 36px);
  font-weight: 900;
  line-height: 1.55;
  color: var(--ms-text-main);
  margin-bottom: 24px;
  letter-spacing: -0.5px;
}

/* Narrator Profile Box */
.narrator-profile-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 16px 20px;
  background: rgba(8, 153, 169, 0.05);
  border: 1px solid rgba(8, 153, 169, 0.12);
  border-radius: 18px;
  margin-bottom: 36px;
  flex-wrap: wrap;
}

.narrator-info-left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.narrator-avatar-frame {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0899A9, #067c89);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 20px;
  font-weight: 900;
  overflow: hidden;
  box-shadow: 0 6px 16px rgba(8, 153, 169, 0.3);
  flex-shrink: 0;
}
.narrator-avatar-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.narrator-name {
  font-size: 16px;
  font-weight: 800;
  color: var(--ms-text-main);
  margin-bottom: 2px;
}
.narrator-sub {
  font-size: 13px;
  color: var(--ms-text-muted);
}

.story-share-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.share-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  background: #fff;
  border: 1px solid #d9e4e6;
  border-radius: 10px;
  font-size: 12.5px;
  font-weight: 700;
  color: var(--ms-text-main);
  cursor: pointer;
  transition: all 0.2s;
}
.share-btn:hover {
  border-color: var(--ms-primary);
  color: var(--ms-primary);
  transform: translateY(-2px);
}

/* Pull Quote / Excerpt */
<?php if (!empty($story['excerpt'])): ?>
.story-pull-quote {
  background: linear-gradient(135deg, rgba(8, 153, 169, 0.08), rgba(243, 162, 27, 0.08));
  border-right: 4px solid var(--ms-primary);
  border-radius: 14px 4px 4px 14px;
  padding: 20px 24px;
  font-size: 15.5px;
  font-weight: 600;
  line-height: 2.1;
  color: #0b4e54;
  margin-bottom: 36px;
  position: relative;
}
.story-pull-quote::before {
  content: '“';
  font-size: 48px;
  font-family: serif;
  color: var(--ms-primary);
  opacity: 0.3;
  position: absolute;
  top: 4px;
  left: 16px;
  line-height: 1;
}
<?php endif; ?>

/* Story Body Text */
.story-body-content {
  font-size: 16.5px;
  line-height: 2.3;
  color: #2b393b;
  text-align: justify;
  margin-bottom: 40px;
}
.story-body-content p {
  margin-bottom: 22px;
}

/* Optional Cover Image */
<?php if (!empty($story['image'])): ?>
.story-cover-img {
  width: 100%;
  max-height: 420px;
  border-radius: 18px;
  object-fit: cover;
  margin-bottom: 36px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
}
<?php endif; ?>

/* Call to Action Box inside card */
.story-cta-box {
  background: linear-gradient(135deg, #0899A9 0%, #066974 100%);
  border-radius: 20px;
  padding: 32px 28px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
  box-shadow: 0 15px 35px rgba(8, 153, 169, 0.3);
  margin-top: 50px;
}
.story-cta-text h4 {
  font-size: 19px;
  font-weight: 800;
  margin-bottom: 6px;
}
.story-cta-text p {
  font-size: 13.5px;
  opacity: 0.9;
  line-height: 1.8;
  max-width: 500px;
}
.story-cta-btns {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
.cta-btn-gold {
  background: var(--ms-accent);
  color: #1a2a2b;
  padding: 12px 22px;
  border-radius: 50px;
  font-weight: 800;
  font-size: 13.5px;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(243, 162, 27, 0.4);
  transition: all 0.2s;
}
.cta-btn-gold:hover {
  background: #ffb12c;
  transform: translateY(-2px);
  color: #1a2a2b;
}
.cta-btn-outline {
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.35);
  padding: 12px 20px;
  border-radius: 50px;
  font-weight: 700;
  font-size: 13.5px;
  text-decoration: none;
  transition: all 0.2s;
}
.cta-btn-outline:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}

/* Related Stories Section */
.related-stories-wrap {
  margin-top: 60px;
}
.related-title {
  font-size: 22px;
  font-weight: 800;
  color: var(--ms-text-main);
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.related-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 22px;
}
.related-card {
  background: #fff;
  border: 1px solid #e4eced;
  border-radius: 18px;
  padding: 24px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all 0.25s ease;
  text-decoration: none;
  color: inherit;
}
.related-card:hover {
  border-color: var(--ms-primary);
  transform: translateY(-4px);
  box-shadow: 0 16px 36px rgba(8, 153, 169, 0.12);
}
.related-tag {
  font-size: 11px;
  font-weight: 700;
  color: var(--ms-accent-dark);
  background: rgba(243, 162, 27, 0.12);
  padding: 3px 10px;
  border-radius: 12px;
  align-self: flex-start;
  margin-bottom: 12px;
}
.related-card h4 {
  font-size: 16px;
  font-weight: 800;
  line-height: 1.6;
  margin-bottom: 10px;
  color: var(--ms-text-main);
}
.related-card p {
  font-size: 13px;
  color: var(--ms-text-muted);
  line-height: 1.8;
  margin-bottom: 18px;
}
.related-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #f0f4f5;
  padding-top: 12px;
  font-size: 12.5px;
  font-weight: 700;
  color: var(--ms-primary);
}
</style>

<div class="story-global-bg"></div>

<main class="story-wrapper">

  <!-- Breadcrumbs -->
  <nav class="story-breadcrumb" aria-label="مسیریابی">
    <a href="/home">صفحه اصلی</a>
    <span class="sep">/</span>
    <a href="/macsa-stories-details">بانک روایت‌های امید</a>
    <span class="sep">/</span>
    <span class="current"><?= htmlspecialchars($story['title']) ?></span>
  </nav>

  <!-- Main Story Content Card -->
  <article class="story-main-card">

    <!-- Top Meta -->
    <div class="story-meta-top">
      <span class="story-tag-pill">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <?= htmlspecialchars($story['tag'] ?: 'روایت امید') ?>
      </span>
      <span class="story-read-time">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <?= htmlspecialchars($story['read_time'] ?: '۴ دقیقه مطالعه') ?>
      </span>
    </div>

    <!-- Title -->
    <h1 class="story-headline"><?= htmlspecialchars($story['title']) ?></h1>

    <!-- Narrator Profile -->
    <div class="narrator-profile-box">
      <div class="narrator-info-left">
        <div class="narrator-avatar-frame">
          <?php if (!empty($story['image'])): ?>
            <img src="<?= htmlspecialchars($story['image']) ?>" alt="<?= htmlspecialchars($story['narrator_name']) ?>">
          <?php else: ?>
            <?= mb_substr($story['narrator_name'], 0, 1, 'UTF-8') ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="narrator-name"><?= htmlspecialchars($story['narrator_name']) ?></div>
          <div class="narrator-sub"><?= htmlspecialchars($story['narrator_role'] ?: 'همراه و حامی مکسا') ?></div>
        </div>
      </div>

      <!-- Share Action -->
      <div class="story-share-actions">
        <button type="button" class="share-btn" onclick="copyStoryLink()" id="copyBtn">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
          <span id="copyText">اشتراک‌گذاری</span>
        </button>
      </div>
    </div>

    <!-- Optional Cover Image -->
    <?php if (!empty($story['image'])): ?>
      <img src="<?= htmlspecialchars($story['image']) ?>" alt="<?= htmlspecialchars($story['title']) ?>" class="story-cover-img">
    <?php endif; ?>

    <!-- Pull Quote / Highlight -->
    <?php if (!empty($story['excerpt'])): ?>
      <div class="story-pull-quote">
        <?= htmlspecialchars($story['excerpt']) ?>
      </div>
    <?php endif; ?>

    <!-- Full Content -->
    <div class="story-body-content">
      <?php
      // Render paragraphs cleanly
      $paragraphs = explode("\n", trim($story['content']));
      foreach ($paragraphs as $para) {
          $para = trim($para);
          if ($para !== '') {
              echo '<p>' . nl2br(htmlspecialchars($para)) . '</p>';
          }
      }
      ?>
    </div>

    <!-- CTA Banner -->
    <div class="story-cta-box">
      <div class="story-cta-text">
        <h4>همراه با مکسا، نوری در دل تاریکی باشید</h4>
        <p>با مشارکت در طرح‌های حمایتی و استندهای تبریک و تسلیت مکسا، به تداوم خدمات رایگان حمایتی و تسکینی برای بیماران مبتلا به سرطان کمک کنید.</p>
      </div>
      <div class="story-cta-btns">
        <a href="/onlinedonation" class="cta-btn-gold">کمک و حمایت آنلاین</a>
        <a href="/stand-sell-section.php" class="cta-btn-outline">استند تبریک و تسلیت</a>
      </div>
    </div>

  </article>

  <!-- Related Stories -->
  <?php if (!empty($relatedStories)): ?>
    <section class="related-stories-wrap">
      <h3 class="related-title">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0899A9" stroke-width="2.5"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
        سایر روایت‌های امید مکسا
      </h3>
      <div class="related-grid">
        <?php foreach ($relatedStories as $rel): ?>
          <a href="/macsa-story.php?id=<?= $rel['id'] ?>" class="related-card">
            <div>
              <span class="related-tag"><?= htmlspecialchars($rel['tag'] ?: 'روایت امید') ?></span>
              <h4><?= htmlspecialchars($rel['title']) ?></h4>
              <p><?= htmlspecialchars($rel['excerpt'] ?: mb_substr(strip_tags($rel['content']), 0, 100, 'UTF-8') . '...') ?></p>
            </div>
            <div class="related-footer">
              <span><?= htmlspecialchars($rel['narrator_name']) ?></span>
              <span>خواندن روایت ←</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</main>

<script>
function copyStoryLink() {
  const url = window.location.href;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(() => {
      document.getElementById('copyText').textContent = 'لینک کپی شد!';
      setTimeout(() => {
        document.getElementById('copyText').textContent = 'اشتراک‌گذاری';
      }, 2500);
    });
  } else {
    prompt('آدرس این روایت:', url);
  }
}
</script>

<?php
require __DIR__ . '/dashboard/components/footer/component.php';
