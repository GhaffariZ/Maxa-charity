<?php
require_once __DIR__ . '/../../../core/site-locale.php';
require_once __DIR__ . '/../../../core/content-locales.php';
if (!function_exists('iconoir')) { require_once __DIR__ . '/../../../core/icons.php'; }

$patientArticles = [];
if (isset($pdo) && $pdo instanceof PDO) {
    $articleScope = content_where($pdo, 'news', 'n.id');
    $articleStmt = $pdo->prepare("SELECT n.id, n.title, n.subtitle, n.content, n.author, n.category_id, n.publish_date, n.news_code, n.featured_image, n.read_time
        FROM news n JOIN news_categories c ON c.id = n.category_id
        WHERE c.slug = 'patients-articles' AND c.is_active = 1 AND n.status = 'published' AND n.publish_date <= NOW()
          AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '') AND $articleScope
        ORDER BY n.publish_date DESC, n.id DESC LIMIT 4");
    $articleStmt->execute();
    $patientArticles = content_rows($pdo, 'news', $articleStmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
<!-- ========================= -->
<!-- Section 1 — Link Cards (no images, links stacked) -->
<section class="po-section">
  <div class="po-container">

    <div class="po-header wow fadeInUp">
      <span class="po-header-accent"></span>
      <h2 class="po-heading"><?= site_e('شناخت و درک سرطان') ?></h2>
      <p class="po-desc"><?= site_e('اطلاعات علمی و به‌روز درباره سرطان، علل ایجاد، روش‌های تشخیص و انواع آن') ?></p>
    </div>

    <div class="po-grid">
      <div class="po-link-card wow fadeInUp">
        <div class="po-link-icon">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h3 class="po-link-title"><?= site_e('شناخت و درک سرطان') ?></h3>
        <div class="po-link-list">
          <a href="/cancer1" class="po-link-item"><span class="po-link-dot"></span><?= site_e('تعریف سرطان') ?></a>
          <a href="/cancer2" class="po-link-item"><span class="po-link-dot"></span><?= site_e('تفاوت‌های سلول‌های سرطانی و سلول‌های طبیعی') ?></a>
          <a href="/understandingcancer" class="po-link-item"><span class="po-link-dot"></span><?= site_e('درک سرطان') ?></a>
          <a href="/tissuealterations" class="po-link-item"><span class="po-link-dot"></span><?= site_e('تغییرات بافتی که سرطان نیستند') ?></a>
        </div>
      </div>

      <div class="po-link-card wow fadeInUp" data-wow-delay="0.2s">
        <div class="po-link-icon">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48 2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48 2.83-2.83" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/></svg>
        </div>
        <h3 class="po-link-title"><?= site_e('علل، عوامل و فرآیند ایجاد سرطان') ?></h3>
        <div class="po-link-list">
          <a href="/carcinogenesis" class="po-link-item"><span class="po-link-dot"></span><?= site_e('نحوه ایجاد سرطان') ?></a>
          <a href="/cancergenes" class="po-link-item"><span class="po-link-dot"></span><?= site_e('ژن‌های دخیل در سرطان') ?></a>
          <a href="/riskfactors" class="po-link-item"><span class="po-link-dot"></span><?= site_e('عوامل خطر در بروز سرطان') ?></a>
          <a href="/ayacancer" class="po-link-item"><span class="po-link-dot"></span><?= site_e('نوجوانان و جوانان مبتلا به سرطان') ?></a>
        </div>
      </div>

      <div class="po-link-card wow fadeInUp" data-wow-delay="0.4s">
        <div class="po-link-icon">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none"><path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h3 class="po-link-title"><?= site_e('تشخیص، پیشرفت و گسترش سرطان') ?></h3>
        <div class="po-link-list">
          <a href="/diagnosis" class="po-link-item"><span class="po-link-dot"></span><?= site_e('تشخیص و مرحله‌بندی') ?></a>
          <a href="/metastasis" class="po-link-item"><span class="po-link-dot"></span><?= site_e('متاستاز سرطان') ?></a>
          <a href="/cancerspread" class="po-link-item"><span class="po-link-dot"></span><?= site_e('محل و نحوه گسترش سرطان') ?></a>
          <a href="/cancertypes" class="po-link-item"><span class="po-link-dot"></span><?= site_e('انواع سرطان') ?></a>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ========================= -->
<!-- Section 2 — Image Cards (square-ish with images) -->
<section class="po-section po-section--alt">
  <div class="po-container">

    <div class="po-header wow fadeInUp">
      <span class="po-header-accent"></span>
      <h2 class="po-heading"><?= site_e('مراقبت‌های حمایتی و تسکینی') ?></h2>
      <p class="po-desc"><?= site_e('مدیریت احساسات و چالش‌های روانی ناشی از بیماری سرطان') ?></p>
    </div>

    <div class="po-grid">
      <a href="fear.html" class="po-card-link">
        <div class="po-card wow fadeInUp">
          <div class="po-card-img">
            <img src="/dashboard/components/psychologicalcare/images/1.png" alt="<?= site_e('مدیریت ترس از عوارض جانبی') ?>">
            <span class="po-badge"><?= site_e('ترس') ?></span>
          </div>
          <div class="po-card-body">
            <h3 class="po-card-title"><?= site_e('مدیریت ترس از عوارض جانبی ناشی از درمان سرطان') ?></h3>
            <span class="po-card-cta"><?= site_e('مطالعه بیشتر') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          </div>
        </div>
      </a>

      <a href="stress.html" class="po-card-link">
        <div class="po-card wow fadeInUp" data-wow-delay="0.2s">
          <div class="po-card-img">
            <img src="/dashboard/components/socialworkcare/images/1.png" alt="<?= site_e('مدیریت استرس') ?>">
            <span class="po-badge"><?= site_e('استرس') ?></span>
          </div>
          <div class="po-card-body">
            <h3 class="po-card-title"><?= site_e('مدیریت استرس ناشی از روند بیماری') ?></h3>
            <span class="po-card-cta"><?= site_e('مطالعه بیشتر') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          </div>
        </div>
      </a>

      <a href="anger.html" class="po-card-link">
        <div class="po-card wow fadeInUp" data-wow-delay="0.4s">
          <div class="po-card-img">
            <img src="/dashboard/components/spritualcare/images/1.png" alt="<?= site_e('مدیریت خشم') ?>">
            <span class="po-badge"><?= site_e('خشم') ?></span>
          </div>
          <div class="po-card-body">
            <h3 class="po-card-title"><?= site_e('مدیریت و مدارا با خشم') ?></h3>
            <span class="po-card-cta"><?= site_e('مطالعه بیشتر') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
          </div>
        </div>
      </a>
    </div>

  </div>
</section>

<!-- ========================= -->
<!-- Section 3 — Image + Text Feature -->
<section class="po-section">
  <div class="po-container">

    <div class="po-header wow fadeInUp">
      <span class="po-header-accent"></span>
      <h2 class="po-heading"><?= site_e('مراقبت جامع و همه‌جانبه') ?></h2>
      <p class="po-desc"><?= site_e('بیماری سرطان فقط یک بیماری جسمی نیست، مراقبت واقعی شامل ذهن، روح و زندگی روزمره بیمار و خانواده‌اش است') ?></p>
    </div>

    <div class="po-feature wow fadeInUp" data-wow-delay="0.2s">
      <div class="po-feature-text">
        <h3 class="po-feature-title"><?= site_e('چرا مراقبت‌های حمایتی و تسکینی مهم هستند؟') ?></h3>
        <p>
          <?= site_e('مراقبت‌های حمایتی و تسکینی بر بهبود کیفیت زندگی بیماران مبتلا به سرطان و خانواده‌هایشان تمرکز دارد. این مراقبت‌ها شامل مدیریت درد و علائم فیزیکی، حمایت روانی، مشاوره اجتماعی و کمک در تصمیم‌گیری‌های درمانی است.') ?>
        </p>
        <a href="socialcare.html" class="po-feature-btn">
          <?= site_e('آشنایی با خدمات') ?>
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </div>
      <div class="po-feature-img">
        <img src="/dashboard/components/supportive_and_palliative_care/images/1.png" alt="<?= site_e('مراقبت حمایتی و تسکینی') ?>">
      </div>
    </div>

  </div>
</section>

<!-- ========================= -->
<!-- Section 4 — Patient Articles -->
<section id="patient-articles" class="po-section po-section--alt po-section--articles" aria-labelledby="patient-articles-title">
  <div class="po-container">

    <div class="po-articles-heading wow fadeInUp">
      <div>
        <span class="po-header-accent"></span>
        <span class="po-articles-kicker"><?= site_e('آموزش و خودمراقبتی') ?></span>
        <h2 id="patient-articles-title" class="po-heading"><?= site_e('مقالات بیماران و مراقبین') ?></h2>
        <p class="po-desc"><?= site_e('یادداشت‌ها، راهنماها و مطالب آموزشی کاربردی برای بیماران و خانواده‌ها') ?></p>
      </div>
      <?php if ($patientArticles): ?>
        <a href="/news.php?category=<?= (int)$patientArticles[0]['category_id'] ?>" class="po-all-link">
          <?= site_e('همه مقالات') ?> <span aria-hidden="true">←</span>
        </a>
      <?php endif; ?>
    </div>

    <?php if ($patientArticles): ?>
      <div class="po-articles-grid wow fadeInUp" data-wow-delay="0.2s">
        <?php foreach ($patientArticles as $index => $article):
            $articleImage = !empty($article['featured_image'])
                ? (preg_match('~^(?:/|https?://)~', (string)$article['featured_image'])
                    ? (string)$article['featured_image']
                    : (!empty($article['news_code']) ? '/uploads/news/' . rawurlencode((string)$article['news_code']) . '/' . rawurlencode((string)$article['featured_image']) : ''))
                : '';
            $articleExcerpt = trim(strip_tags((string)($article['subtitle'] ?: $article['content'])));
        ?>
        <a class="po-article-card<?= $index === 0 ? ' po-article-featured' : '' ?>" href="/<?= (int)$article['id'] ?>/">
          <div class="po-article-image">
            <?php if ($articleImage): ?>
              <img src="<?= htmlspecialchars($articleImage, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
            <?php else: ?>
              <span aria-hidden="true"><?= iconoir('book', '', 34) ?></span>
            <?php endif; ?>
          </div>
          <div class="po-article-body">
            <span class="po-article-label"><?= site_e('آموزش بیمار') ?></span>
            <h3 class="po-article-title"><?= htmlspecialchars((string)$article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="po-article-excerpt"><?= htmlspecialchars(mb_substr($articleExcerpt, 0, $index === 0 ? 165 : 105, 'UTF-8'), ENT_QUOTES, 'UTF-8') ?><?= mb_strlen($articleExcerpt, 'UTF-8') > ($index === 0 ? 165 : 105) ? '…' : '' ?></p>
            <span class="po-article-meta"><?= htmlspecialchars((string)($article['author'] ?: site_text('مکسا')), ENT_QUOTES, 'UTF-8') ?> · <?= max(1, (int)$article['read_time']) ?> <?= site_e('دقیقه مطالعه') ?></span>
            <span class="po-article-read"><?= site_e('مطالعه مقاله') ?> <span aria-hidden="true">←</span></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="po-articles-empty wow fadeInUp"><?= site_e('مقاله‌ای در این بخش منتشر نشده است. به‌زودی مطالب تازه را اینجا می‌خوانید.') ?></div>
    <?php endif; ?>

  </div>
</section>

<style>
/* ── design tokens ── */
.po-section {
  --po-teal: #008f8a;
  --po-teal-dark: #078994;
  --po-teal-light: rgba(15,159,170,0.09);
  --po-text: #1e293b;
  --po-muted: #475569;
  --po-border: #e2e8f0;
  --po-bg: #ffffff;
  --po-bg-alt: #f8fafc;
  --po-radius: 22px;

  padding: 40px 20px;
  direction: var(--site-direction, rtl);
  text-align: start;
  font-family: 'Vazirmatn', Tahoma, sans-serif;
  background: var(--po-bg);
}

.po-section--alt {
  background: var(--po-bg-alt);
}

.po-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* ── header ── */
.po-header {
  text-align: center;
  margin-bottom: 48px;
}

.po-header-accent {
  display: inline-block;
  width: 48px;
  height: 4px;
  border-radius: 99px;
  background: var(--po-teal);
  margin-bottom: 16px;
}

.po-heading {
  font-size: 30px;
  font-weight: 900;
  color: var(--po-text);
  margin: 0 0 12px;
  line-height: 1.5;
}

.po-desc {
  font-size: 15px;
  color: var(--po-muted);
  margin: 0 auto;
  max-width: 600px;
  line-height: 1.9;
}

/* ── grid ── */
.po-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

/* ── 1. link cards ── */
.po-link-card {
  background: var(--po-bg);
  border: 1px solid var(--po-border);
  border-radius: var(--po-radius);
  padding: 30px 26px;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s cubic-bezier(0.16,1,0.3,1),
              box-shadow 0.3s cubic-bezier(0.16,1,0.3,1),
              border-color 0.3s ease;
  box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}

.po-link-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 36px rgba(0,0,0,0.08);
  border-color: rgba(15,159,170,0.22);
}

.po-link-icon {
  width: 50px;
  height: 50px;
  border-radius: 14px;
  background: linear-gradient(135deg, var(--po-teal), #12b7bd);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 18px;
  color: #fff;
  flex-shrink: 0;
}

.po-link-title {
  font-size: 17px;
  font-weight: 800;
  color: var(--po-text);
  margin: 0 0 18px;
  line-height: 1.6;
}

.po-link-list {
  display: flex;
  flex-direction: column;
  gap: 0;
  flex-grow: 1;
}

.po-link-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 14px;
  border-radius: 10px;
  color: var(--po-muted);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.22s ease;
  line-height: 1.7;
}

.po-link-item:hover {
  background: var(--po-teal-light);
  color: var(--po-teal);
  padding-right: 20px;
  text-decoration: none;
}

.po-link-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--po-teal);
  flex-shrink: 0;
  transition: transform 0.22s ease;
}

.po-link-item:hover .po-link-dot {
  transform: scale(1.4);
  background: var(--po-teal-dark);
}

/* ── 2. image cards ── */
.po-card-link {
  text-decoration: none;
  color: inherit;
  display: block;
}

.po-card {
  background: var(--po-bg);
  border: 1px solid var(--po-border);
  border-radius: var(--po-radius);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;
  box-shadow: 0 4px 14px rgba(0,0,0,0.04);
  transition: transform 0.3s cubic-bezier(0.16,1,0.3,1),
              box-shadow 0.3s cubic-bezier(0.16,1,0.3,1),
              border-color 0.3s ease;
}

.po-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 36px rgba(0,0,0,0.08);
  border-color: rgba(15,159,170,0.22);
  text-decoration: none;
}

.po-card-img {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: #f1f5f9;
}

.po-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.16,1,0.3,1);
}

.po-card:hover .po-card-img img {
  transform: scale(1.06);
}

.po-badge {
  position: absolute;
  top: 14px;
  right: 14px;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  color: var(--po-teal);
  padding: 4px 13px;
  font-size: 11px;
  font-weight: 800;
  border-radius: 20px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  z-index: 2;
  transition: all 0.3s ease;
}

.po-card:hover .po-badge {
  background: var(--po-teal);
  color: #fff;
}

.po-card-body {
  padding: 22px 24px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.po-card-title {
  font-size: 17px;
  font-weight: 800;
  color: var(--po-text);
  margin: 0 0 16px;
  line-height: 1.6;
  flex-grow: 1;
}

.po-card-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--po-teal);
  font-size: 13px;
  font-weight: 800;
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid var(--po-border);
  transition: color 0.22s ease;
}

.po-card-cta svg {
  transition: transform 0.25s cubic-bezier(0.16,1,0.3,1);
  color: #faa61a;
}

.po-card:hover .po-card-cta {
  color: var(--po-teal-dark);
}

.po-card:hover .po-card-cta svg {
  transform: translateX(-4px);
}

/* ── 3. feature box ── */
.po-feature {
  display: flex;
  gap: 0;
  background: var(--po-bg);
  border: 1px solid var(--po-border);
  border-radius: var(--po-radius);
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}

.po-feature-text {
  flex: 1;
  padding: 40px 36px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.po-feature-title {
  font-size: 22px;
  font-weight: 900;
  color: var(--po-text);
  margin: 0 0 18px;
  line-height: 1.5;
  border-right: 4px solid var(--po-teal);
  padding-right: 14px;
}

.po-feature-text p {
  font-size: 15px;
  color: var(--po-muted);
  line-height: 2;
  text-align: justify;
  margin: 0 0 26px;
}

.po-feature-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(180deg, var(--po-teal) 0%, var(--po-teal-dark) 100%);
  color: #fff;
  padding: 13px 30px;
  border-radius: 999px;
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
  width: fit-content;
  box-shadow: 0 10px 22px rgba(15,159,170,0.22);
  transition: all 0.22s ease;
}

.po-feature-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 30px rgba(15,159,170,0.30);
  color: #fff;
  text-decoration: none;
}

.po-feature-btn svg {
  transition: transform 0.25s cubic-bezier(0.16,1,0.3,1);
  color: #faa61a;
}

.po-feature-btn:hover svg {
  transform: translateX(-4px);
  color: #fff;
}

.po-feature-img {
  flex: 1;
  min-height: 320px;
  overflow: hidden;
}

.po-feature-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* ── entrance animations (CSS-only) ── */
@keyframes poFadeInUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}

.po-section .wow {
  opacity: 0;
  animation: poFadeInUp 0.7s cubic-bezier(0.22,1,0.36,1) forwards;
}

.po-section [data-wow-delay="0.2s"] { animation-delay: 0.2s; }
.po-section [data-wow-delay="0.4s"] { animation-delay: 0.4s; }
.po-section [data-wow-delay="0.6s"] { animation-delay: 0.6s; }
.po-section [data-wow-delay="0.8s"] { animation-delay: 0.8s; }

@media (prefers-reduced-motion: reduce) {
  .po-section .wow { opacity: 1 !important; animation: none !important; }
}

/* ── responsive ── */
@media (max-width: 991px) {
  .po-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .po-feature {
    flex-direction: column-reverse;
  }

  .po-feature-img {
    min-height: 260px;
  }
}

@media (max-width: 768px) {
  .po-section {
    padding: 56px 16px;
  }

  .po-heading {
    font-size: 24px;
  }

  .po-desc {
    font-size: 14px;
  }

  .po-grid {
    grid-template-columns: 1fr;
    gap: 18px;
  }

  .po-link-card {
    padding: 24px 20px;
  }

  .po-feature-text {
    padding: 28px 22px;
  }

  .po-feature-title {
    font-size: 18px;
  }
}

@media (max-width: 480px) {
  .po-section {
    padding: 40px 14px;
  }

  .po-heading {
    font-size: 21px;
  }
}

/* ── 4. patient articles ── */
.po-section--articles {
  padding-top: 50px;
  padding-bottom: 70px;
}
.po-articles-heading {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 24px;
  margin-bottom: 36px;
  flex-wrap: wrap;
}
.po-articles-kicker {
  display: block;
  color: var(--po-teal);
  font-size: 13px;
  font-weight: 800;
  margin-bottom: 6px;
}
.po-articles-heading .po-heading {
  margin: 0 0 8px;
}
.po-articles-heading .po-desc {
  margin: 0;
  max-width: 550px;
  text-align: start;
}
.po-all-link {
  color: var(--po-teal);
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
  white-space: nowrap;
  padding: 8px 16px;
  border-radius: 999px;
  background: var(--po-teal-light);
  transition: all 0.22s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.po-all-link:hover {
  background: var(--po-teal);
  color: #fff;
  text-decoration: none;
}
.po-articles-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
  gap: 20px;
}
.po-article-card {
  min-width: 0;
  display: grid;
  grid-template-columns: 160px minmax(0, 1fr);
  gap: 18px;
  padding: 16px;
  color: inherit;
  text-decoration: none;
  background: var(--po-bg);
  border: 1px solid var(--po-border);
  border-radius: var(--po-radius);
  transition: transform 0.3s cubic-bezier(0.16,1,0.3,1),
              box-shadow 0.3s cubic-bezier(0.16,1,0.3,1),
              border-color 0.3s ease;
  box-shadow: 0 4px 14px rgba(0,0,0,0.04);
}
.po-article-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 36px rgba(0,0,0,0.08);
  border-color: rgba(15,159,170,0.22);
  text-decoration: none;
  color: inherit;
}
.po-article-featured {
  grid-row: span 3;
  display: flex;
  flex-direction: column;
  padding: 0;
  overflow: hidden;
}
.po-article-image {
  min-height: 130px;
  background: #e6f7f7;
  color: var(--po-teal);
  display: grid;
  place-items: center;
  overflow: hidden;
  border-radius: 14px;
}
.po-article-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.po-article-featured .po-article-image {
  height: 270px;
  border-radius: 0;
}
.po-article-body {
  min-width: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  flex-grow: 1;
}
.po-article-featured .po-article-body {
  padding: 24px 26px 26px;
  flex: 1;
}
.po-article-label {
  display: inline-block;
  background: var(--po-teal-light);
  color: var(--po-teal-dark);
  font-size: 11px;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 12px;
  margin-bottom: 8px;
}
.po-article-title {
  margin: 4px 0 8px;
  color: var(--po-text);
  font-size: 16px;
  font-weight: 800;
  line-height: 1.6;
}
.po-article-featured .po-article-title {
  font-size: 21px;
  line-height: 1.5;
}
.po-article-excerpt {
  margin: 0 0 12px;
  color: var(--po-muted);
  font-size: 13.5px;
  line-height: 1.8;
  text-align: start;
}
.po-article-featured .po-article-excerpt {
  font-size: 14.5px;
}
.po-article-meta {
  color: #94a3b8;
  font-size: 12px;
  font-weight: 600;
  margin-top: auto;
}
.po-article-read {
  color: var(--po-teal);
  font-size: 13px;
  font-weight: 800;
  margin-top: 10px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.po-articles-empty {
  background: #f1f8f7;
  color: #476662;
  border: 1px dashed #b2ded7;
  border-radius: var(--po-radius);
  padding: 48px 24px;
  text-align: center;
  font-size: 15px;
  font-weight: 600;
}
@media (max-width: 900px) {
  .po-articles-grid { grid-template-columns: 1fr; }
  .po-article-featured { grid-row: auto; }
  .po-article-featured .po-article-image { height: 220px; }
}
@media (max-width: 540px) {
  .po-article-card { grid-template-columns: 1fr; }
  .po-article-image { height: 160px; }
}
</style>
