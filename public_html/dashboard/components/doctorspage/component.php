<?php
require_once __DIR__ . '/../../../core/site-locale.php';
require_once __DIR__ . '/../../../core/content-locales.php';
if (!function_exists('iconoir')) { require_once __DIR__ . '/../../../core/icons.php'; }

$doctorArticles = [];
if (isset($pdo) && $pdo instanceof PDO) {
    $articleScope = content_where($pdo, 'news', 'n.id');
    $articleStmt = $pdo->prepare("SELECT n.id, n.title, n.subtitle, n.content, n.author, n.category_id, n.publish_date, n.news_code, n.featured_image, n.read_time
        FROM news n JOIN news_categories c ON c.id = n.category_id
        WHERE c.slug = 'doctors-articles' AND c.is_active = 1 AND n.status = 'published' AND n.publish_date <= NOW()
          AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '') AND $articleScope
        ORDER BY n.publish_date DESC, n.id DESC LIMIT 9");
    $articleStmt->execute();
    $doctorArticles = content_rows($pdo, 'news', $articleStmt->fetchAll(PDO::FETCH_ASSOC));
}
?>

<div class="dp-page">

  <!-- ========================= -->
  <!-- 1. Hero Section -->
  <!-- ========================= -->
  <section class="dp-hero">
    <div class="dp-container">
      <div class="dp-hero-grid">
        <div class="dp-hero-text wow fadeInUp">
          <nav class="dp-breadcrumb" aria-label="<?= site_e('مسیر صفحه') ?>">
            <a href="/"><?= site_e('صفحه اصلی') ?></a>
            <span class="dp-breadcrumb-sep">/</span>
            <span><?= site_e('کادر درمان و متخصصان مکسا') ?></span>
          </nav>
          <div class="dp-tag">
            <span class="dp-tag-icon"><?= iconoir('stethoscope', '', 16) ?></span>
            <span><?= site_e('تخصص در کنار همدلی') ?></span>
          </div>
          <h1 class="dp-hero-title"><?= site_e('همراه با متخصصان و کادر درمان') ?></h1>
          <p class="dp-hero-lead">
            <?= site_e('تیمی چندرشته‌ای و متعهد از پزشکان، پرستاران بالینی، روانشناسان و مددکاران سلامت؛ همگام با استانداردهای روز جهان برای مراقبت همه‌جانبه از بیماران مبتلا به سرطان و خانواده‌های آنان.') ?>
          </p>
          <div class="dp-hero-actions">
            <a href="/network.php?role=treatment" class="dp-btn dp-btn--primary">
              <span><?= site_e('مشاهده رزومه متخصصان') ?></span>
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="#doctor-articles" class="dp-btn dp-btn--secondary">
              <span><?= site_e('مطالعه مقالات تخصصی') ?></span>
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
        </div>
        <div class="dp-hero-visual wow fadeInUp" data-wow-delay="0.2s">
          <div class="dp-hero-card">
            <img src="{{image1}}" alt="<?= site_e('کادر درمان و متخصصان مکسا') ?>" class="dp-hero-img" loading="eager">
            <div class="dp-hero-stat">
              <div class="dp-hero-stat-icon"><?= iconoir('healthcare', '', 20) ?></div>
              <div>
                <strong><?= site_e('+۱۰۰ متخصص') ?></strong>
                <small><?= site_e('در شعب سراسر کشور') ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========================= -->
  <!-- 2. بسترهای همکاری و خدمات کادر درمان -->
  <!-- ========================= -->
  <section class="dp-section">
    <div class="dp-container">
      <div class="dp-header wow fadeInUp">
        <span class="dp-header-accent"></span>
        <h2 class="dp-heading"><?= site_e('خدمات و بسترهای همراهی کادر درمان') ?></h2>
        <p class="dp-desc"><?= site_e('فرآیندهای ارتباطی، ارجاع بیمار، بازآموزی مهارتی و دسترسی آزاد به پروتکل‌ها و منابع علمی طب تسکینی') ?></p>
      </div>

      <div class="dp-grid dp-grid--3">
        <!-- Card 1 -->
        <a href="/patientintake" class="dp-card-link">
          <div class="dp-card wow fadeInUp">
            <div class="dp-card-img">
              <img src="{{image3}}" alt="<?= site_e('ارجاع بیمار به مکسا') ?>" loading="lazy">
              <span class="dp-badge"><?= site_e('ارجاع بالینی') ?></span>
            </div>
            <div class="dp-card-body">
              <h3 class="dp-card-title"><?= site_e('ارجاع بیمار به مکسا') ?></h3>
              <p class="dp-card-desc"><?= site_e('معرفی مستقیم بیماران نیازمند مراقبت در منزل، تجهیزات پزشکی و خدمات حمایتی توسط پزشکان و مراکز درمانی.') ?></p>
              <span class="dp-card-cta"><?= site_e('فرآیند ارجاع بیمار') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </div>
          </div>
        </a>

        <!-- Card 2 -->
        <a href="/contactus" class="dp-card-link">
          <div class="dp-card wow fadeInUp" data-wow-delay="0.15s">
            <div class="dp-card-img">
              <img src="{{image4}}" alt="<?= site_e('همکاری بالینی و علمی') ?>" loading="lazy">
              <span class="dp-badge"><?= site_e('عضویت در شبکه') ?></span>
            </div>
            <div class="dp-card-body">
              <h3 class="dp-card-title"><?= site_e('همکاری بالینی و علمی') ?></h3>
              <p class="dp-card-desc"><?= site_e('پیوستن به شبکه داوطلبانه و تخصصی پزشکان، درمانگران و مشاوران مکسا در درمانگاه‌ها و شبکه هوم‌کر.') ?></p>
              <span class="dp-card-cta"><?= site_e('همکاری با مکسا') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </div>
          </div>
        </a>

        <!-- Card 3 -->
        <a href="/courses" class="dp-card-link">
          <div class="dp-card wow fadeInUp" data-wow-delay="0.3s">
            <div class="dp-card-img">
              <img src="{{image5}}" alt="<?= site_e('آموزش‌های مهارتی و دوره‌ها') ?>" loading="lazy">
              <span class="dp-badge"><?= site_e('آموزش مداوم') ?></span>
            </div>
            <div class="dp-card-body">
              <h3 class="dp-card-title"><?= site_e('آموزش‌های مهارتی و دوره‌ها') ?></h3>
              <p class="dp-card-desc"><?= site_e('دوره‌های مهارتی و بازآموزی مراقبت‌های تسکینی سرطان ویژه پزشکان، پرستاران و کادر توانبخشی سلامت.') ?></p>
              <span class="dp-card-cta"><?= site_e('مشاهده دوره‌ها') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </div>
          </div>
        </a>

        <!-- Card 4 -->
        <a href="/macsapedia" class="dp-card-link">
          <div class="dp-card wow fadeInUp">
            <div class="dp-card-img">
              <img src="{{image2}}" alt="<?= site_e('منابع علمی و پروتکل‌ها') ?>" loading="lazy">
              <span class="dp-badge"><?= site_e('مکساپدیا') ?></span>
            </div>
            <div class="dp-card-body">
              <h3 class="dp-card-title"><?= site_e('منابع علمی و پروتکل‌ها') ?></h3>
              <p class="dp-card-desc"><?= site_e('دسترسی آزاد به کتاب‌ها، گایدلاین‌های بالینی بین‌المللی، مقالات روز و پروتکل‌های مراقبت تسکینی سرطان.') ?></p>
              <span class="dp-card-cta"><?= site_e('کتابخانه و مقالات') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </div>
          </div>
        </a>

        <!-- Card 5 -->
        <a href="/branches.php" class="dp-card-link">
          <div class="dp-card wow fadeInUp" data-wow-delay="0.15s">
            <div class="dp-card-img">
              <img src="{{image6}}" alt="<?= site_e('راه‌اندازی مراکز جدید') ?>" loading="lazy">
              <span class="dp-badge"><?= site_e('توسعه و شعب') ?></span>
            </div>
            <div class="dp-card-body">
              <h3 class="dp-card-title"><?= site_e('راه‌اندازی مراکز جدید') ?></h3>
              <p class="dp-card-desc"><?= site_e('مشاوره تخصصی، انتقال پروتکل‌ها و همکاری با دانشگاه‌های علوم پزشکی در تأسیس بخش‌های طب تسکینی.') ?></p>
              <span class="dp-card-cta"><?= site_e('شعب و مراکز همکار') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </div>
          </div>
        </a>

        <!-- Card 6 -->
        <a href="/iran-situation-in-palliative" class="dp-card-link">
          <div class="dp-card wow fadeInUp" data-wow-delay="0.3s">
            <div class="dp-card-img">
              <img src="{{image1}}" alt="<?= site_e('طب تسکینی در ایران و جهان') ?>" loading="lazy">
              <span class="dp-badge"><?= site_e('گزارش تحلیلی') ?></span>
            </div>
            <div class="dp-card-body">
              <h3 class="dp-card-title"><?= site_e('طب تسکینی در ایران و جهان') ?></h3>
              <p class="dp-card-desc"><?= site_e('بررسی شاخص‌های کشوری، وضعیت مراقبت‌های تسکینی در ایران و تحلیل تطبیقی با استانداردهای بهداشت جهانی.') ?></p>
              <span class="dp-card-cta"><?= site_e('مطالعه گزارش تحلیلی') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            </div>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- ========================= -->
  <!-- 3. مقالات تخصصی کادر درمان -->
  <!-- ========================= -->
  <section class="dp-section dp-section--alt" id="doctor-articles">
    <div class="dp-container">
      <div class="dp-header-split wow fadeInUp">
        <div>
          <span class="dp-header-accent"></span>
          <h2 class="dp-heading"><?= site_e('مقالات تخصصی کادر درمان') ?></h2>
          <p class="dp-desc"><?= site_e('یادداشت‌ها، پژوهش‌ها و یافته‌های بالینی منتشرشده توسط متخصصان مکسا') ?></p>
        </div>
        <?php if (!empty($doctorArticles)): ?>
          <a href="/news.php?category=<?= (int)$doctorArticles[0]['category_id'] ?>" class="dp-more-link">
            <span><?= site_e('همه مقالات') ?></span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
        <?php endif; ?>
      </div>

      <div class="dp-grid dp-grid--3">
        <?php if (!empty($doctorArticles)): ?>
          <?php foreach ($doctorArticles as $index => $article):
              $articleImage = !empty($article['featured_image'])
                  ? (preg_match('~^(?:/|https?://)~', (string)$article['featured_image'])
                      ? (string)$article['featured_image']
                      : (!empty($article['news_code']) ? '/uploads/news/' . rawurlencode((string)$article['news_code']) . '/' . rawurlencode((string)$article['featured_image']) : ''))
                  : '{{image2}}';
              $articleBadge = !empty($article['author']) ? $article['author'] : site_text('کادر درمان');
              $articleUrl = '/' . (int)$article['id'] . '/';
              $delay = sprintf('%.1fs', ($index % 3) * 0.15);
          ?>
          <a href="<?= htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8') ?>" class="dp-card-link">
            <div class="dp-card wow fadeInUp" data-wow-delay="<?= $delay ?>">
              <div class="dp-card-img">
                <img src="<?= htmlspecialchars($articleImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$article['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                <span class="dp-badge"><?= htmlspecialchars((string)$articleBadge, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
              <div class="dp-card-body">
                <h3 class="dp-card-title"><?= htmlspecialchars((string)$article['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                <span class="dp-card-cta"><?= site_e('مطالعه بیشتر') ?> <svg viewBox="0 0 24 24" width="14" height="14" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="dp-empty wow fadeInUp"><?= site_e('مقاله‌ای در این بخش منتشر نشده است. به‌زودی مطالب تازه را اینجا می‌خوانید.') ?></div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ========================= -->
  <!-- 4. دپارتمان‌های تیم سلامت مکسا (MDT) -->
  <!-- ========================= -->
  <section class="dp-section">
    <div class="dp-container">
      <div class="dp-header wow fadeInUp">
        <span class="dp-header-accent"></span>
        <h2 class="dp-heading"><?= site_e('دپارتمان‌های تخصصی تیم سلامت مکسا (MDT)') ?></h2>
        <p class="dp-desc"><?= site_e('کادر درمان مکسا در قالب گروه‌های تخصصی به‌هم‌پیوسته، خدمات جامع و رایگان پزشکی و حمایتی را ارائه می‌دهند') ?></p>
      </div>

      <div class="dp-grid dp-grid--4">
        <div class="dp-team-card wow fadeInUp">
          <div class="dp-team-icon"><?= iconoir('stethoscope', '', 24) ?></div>
          <h4 class="dp-team-title"><?= site_e('پزشکان طب تسکینی و انکولوژی') ?></h4>
          <p class="dp-team-desc"><?= site_e('ارزیابی بالینی جامع، پروتکل‌های تسکین درد، کنترل علائم گوارشی و تنفسی، و نظارت بر سیر درمان.') ?></p>
        </div>

        <div class="dp-team-card wow fadeInUp" data-wow-delay="0.1s">
          <div class="dp-team-icon"><?= iconoir('healthcare', '', 24) ?></div>
          <h4 class="dp-team-title"><?= site_e('پرستاران بالینی و مراقبت در منزل') ?></h4>
          <p class="dp-team-desc"><?= site_e('مراقبت تخصصی از زخم و استومی، کنترل علائم حیاتی، پورت‌گذاری، و آموزش مراقبت به خانواده.') ?></p>
        </div>

        <div class="dp-team-card wow fadeInUp" data-wow-delay="0.2s">
          <div class="dp-team-icon"><?= iconoir('brain', '', 24) ?></div>
          <h4 class="dp-team-title"><?= site_e('روانشناسان سلامت و مشاوره') ?></h4>
          <p class="dp-team-desc"><?= site_e('روان‌درمانی حمایتی، مداخلات مدیریت اضطراب و افسردگی بیماری، مشاوره سوگ و جلسات خانواده.') ?></p>
        </div>

        <div class="dp-team-card wow fadeInUp" data-wow-delay="0.3s">
          <div class="dp-team-icon"><?= iconoir('handshake', '', 24) ?></div>
          <h4 class="dp-team-title"><?= site_e('مددکاران اجتماعی بالینی') ?></h4>
          <p class="dp-team-desc"><?= site_e('ارزیابی نیازهای معیشتی، پیگیری امور بیمه و درمان، و اتصال بیمار به زنجیره خدمات حمایتی.') ?></p>
        </div>

        <div class="dp-team-card wow fadeInUp">
          <div class="dp-team-icon"><?= iconoir('apple', '', 24) ?></div>
          <h4 class="dp-team-title"><?= site_e('متخصصان تغذیه و رژیم‌درمانی') ?></h4>
          <p class="dp-team-desc"><?= site_e('تنظیم رژیم‌های اختصاصی در دوران درمان، مدیریت بی‌اشتهایی و کاشکسی، و تغذیه بالینی.') ?></p>
        </div>

        <div class="dp-team-card wow fadeInUp" data-wow-delay="0.1s">
          <div class="dp-team-icon"><?= iconoir('activity', '', 24) ?></div>
          <h4 class="dp-team-title"><?= site_e('فیزیوتراپی و مدیریت لنف‌ادم') ?></h4>
          <p class="dp-team-desc"><?= site_e('بازتوانی حرکتی، ماساژ تخصصی تخلیه لنفاوی (CDT)، بانداژ فشاری و پیشگیری از ناتوانی‌ها.') ?></p>
        </div>

        <div class="dp-team-card wow fadeInUp" data-wow-delay="0.2s">
          <div class="dp-team-icon"><?= iconoir('dna', '', 24) ?></div>
          <h4 class="dp-team-title"><?= site_e('مشاوره ژنتیک و غربالگری') ?></h4>
          <p class="dp-team-desc"><?= site_e('بررسی سندروم‌های توارثی سرطان، ارزیابی شجره‌نامه ژنتیکی و راهنمایی آزمایش‌های تشخیصی زودهنگام.') ?></p>
        </div>

        <div class="dp-team-card wow fadeInUp" data-wow-delay="0.3s">
          <div class="dp-team-icon"><?= iconoir('heart', '', 24) ?></div>
          <h4 class="dp-team-title"><?= site_e('مشاوران مراقبت معنوی') ?></h4>
          <p class="dp-team-desc"><?= site_e('پاسخ به چالش‌های وجودی، بازیابی آرامش قلبی، امیدبخشی و تقویت تاب‌آوری معنوی بیمار و همراهان.') ?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========================= -->
  <!-- 5. سامانه معرفی و رزومه کادر درمان -->
  <!-- ========================= -->
  <section class="dp-section dp-section--alt">
    <div class="dp-container">
      <div class="dp-resume-banner wow fadeInUp">
        <div class="dp-resume-content">
          <div class="dp-tag dp-tag--teal">
            <span class="dp-tag-icon"><?= iconoir('user', '', 15) ?></span>
            <span><?= site_e('سامانه معرفی و رزومه') ?></span>
          </div>
          <h2 class="dp-resume-title"><?= site_e('آشنایی با پزشکان، درمانگران و کادر تخصصی مکسا') ?></h2>
          <p class="dp-resume-desc">
            <?= site_e('تیم درمان مکسا متشکل از برجسته‌ترین متخصصان انکولوژی، طب تسکینی، پرستاران بالینی، روانشناسان سلامت، مددکاران و مشاوران است. با ورود به سامانه رزومه، می‌توانید سوابق علمی، مدارک دانشگاهی و حوزه‌های خدمت همکاران را مشاهده نمایید.') ?>
          </p>

          <div class="dp-resume-features">
            <div class="dp-resume-feature">
              <span class="dp-resume-feature-icon"><?= iconoir('graduation-cap', '', 18) ?></span>
              <span><?= site_e('سوابق علمی و مدارک تخصصی دانشگاهی') ?></span>
            </div>
            <div class="dp-resume-feature">
              <span class="dp-resume-feature-icon"><?= iconoir('hospital', '', 18) ?></span>
              <span><?= site_e('شعب بیمارستانی و شبکه مراقبت در منزل سراسر کشور') ?></span>
            </div>
            <div class="dp-resume-feature">
              <span class="dp-resume-feature-icon"><?= iconoir('stethoscope', '', 18) ?></span>
              <span><?= site_e('امکان جستجو و فیلتر بر اساس تخصص بالینی') ?></span>
            </div>
          </div>

          <div class="dp-resume-actions">
            <a href="/network.php?role=treatment" class="dp-btn dp-btn--primary">
              <span><?= site_e('مشاهده رزومه و سوابق کادر درمان') ?></span>
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
          </div>
        </div>

        <div class="dp-resume-visual">
          <div class="dp-resume-card">
            <div class="dp-resume-card-head">
              <div class="dp-avatars">
                <span class="dp-avatar"><?= iconoir('stethoscope', '', 16) ?></span>
                <span class="dp-avatar"><?= iconoir('healthcare', '', 16) ?></span>
                <span class="dp-avatar"><?= iconoir('brain', '', 16) ?></span>
                <span class="dp-avatar dp-avatar--plus">+</span>
              </div>
              <div class="dp-resume-card-badge">
                <strong><?= site_e('۱۰۰+ همکار') ?></strong>
                <small><?= site_e('کادر سلامت') ?></small>
              </div>
            </div>
            <div class="dp-resume-card-list">
              <div class="dp-resume-card-item">
                <span class="dp-dot"></span>
                <span><?= site_e('پزشکان متخصص و فلوشیپ طب تسکینی') ?></span>
              </div>
              <div class="dp-resume-card-item">
                <span class="dp-dot"></span>
                <span><?= site_e('پرستاران بالینی و مراقبت در منزل') ?></span>
              </div>
              <div class="dp-resume-card-item">
                <span class="dp-dot"></span>
                <span><?= site_e('روانشناسان، مددکاران و مشاوران سلامت') ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========================= -->
  <!-- 6. نقل‌قول و مأموریت -->
  <!-- ========================= -->
  <section class="dp-section">
    <div class="dp-container">
      <div class="dp-quote wow fadeInUp">
        <div class="dp-quote-mark"><?= iconoir('quote-message', '', 32) ?></div>
        <blockquote class="dp-quote-text">
          <?= site_e('«مکسا پلی استوار میان جامعه پزشکی و بیماران نیازمند؛ جایی که تخصص بالینی با ایثار و محبت درمی‌آمیزد تا هیچ بیماری در مواجهه با سرطان احساس تنهایی و درماندگی نکند.»') ?>
        </blockquote>
      </div>
    </div>
  </section>

</div>

<style>
/* ── design tokens ── */
.dp-page {
  --dp-teal: #008f8a;
  --dp-teal-dark: #00736f;
  --dp-teal-light: rgba(0, 143, 138, 0.08);
  --dp-gold: #faa61a;
  --dp-text: #1e293b;
  --dp-muted: #475569;
  --dp-border: #e2e8f0;
  --dp-bg: #ffffff;
  --dp-bg-alt: #f8fafc;
  --dp-radius: 22px;
  --dp-radius-sm: 14px;

  direction: var(--site-direction, rtl);
  font-family: 'Vazirmatn', Tahoma, sans-serif;
  color: var(--dp-text);
  line-height: 1.7;
}

.dp-section {
  padding: 60px 20px;
  background: var(--dp-bg);
}

.dp-section--alt {
  background: var(--dp-bg-alt);
}

.dp-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* ── header ── */
.dp-header {
  text-align: center;
  margin-bottom: 44px;
}

.dp-header-accent {
  display: inline-block;
  width: 48px;
  height: 4px;
  border-radius: 99px;
  background: var(--dp-teal);
  margin-bottom: 16px;
}

.dp-heading {
  font-size: 28px;
  font-weight: 900;
  color: var(--dp-text);
  margin: 0 0 12px;
  line-height: 1.4;
}

.dp-desc {
  font-size: 15px;
  color: var(--dp-muted);
  margin: 0 auto;
  max-width: 640px;
  line-height: 1.8;
}

.dp-header-split {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 20px;
  margin-bottom: 44px;
  flex-wrap: wrap;
}

.dp-header-split .dp-desc {
  margin: 0;
}

.dp-more-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--dp-teal);
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  padding: 8px 18px;
  background: var(--dp-teal-light);
  border-radius: 99px;
  transition: all 0.2s ease;
}

.dp-more-link:hover {
  background: var(--dp-teal);
  color: #fff;
}

/* ── hero ── */
.dp-hero {
  padding: 40px 20px 60px;
  background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
  border-bottom: 1px solid var(--dp-border);
}

.dp-hero-grid {
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 48px;
  align-items: center;
}

.dp-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: var(--dp-muted);
  margin-bottom: 20px;
}

.dp-breadcrumb a {
  color: var(--dp-muted);
  text-decoration: none;
  transition: color 0.2s;
}

.dp-breadcrumb a:hover {
  color: var(--dp-teal);
}

.dp-breadcrumb-sep {
  opacity: 0.5;
}

.dp-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #ffffff;
  border: 1px solid var(--dp-border);
  color: var(--dp-teal);
  padding: 6px 14px;
  border-radius: 99px;
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 16px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.dp-tag-icon {
  display: flex;
  align-items: center;
}

.dp-tag--teal {
  background: var(--dp-teal-light);
  border-color: rgba(0, 143, 138, 0.2);
}

.dp-hero-title {
  font-size: clamp(26px, 3.2vw, 36px);
  font-weight: 900;
  color: var(--dp-text);
  line-height: 1.35;
  margin: 0 0 16px;
}

.dp-hero-lead {
  font-size: 16px;
  color: var(--dp-muted);
  line-height: 1.85;
  margin: 0 0 28px;
  max-width: 580px;
}

.dp-hero-actions {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
}

.dp-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 99px;
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
  transition: all 0.25s cubic-bezier(0.16,1,0.3,1);
  box-sizing: border-box;
}

.dp-btn--primary {
  background: var(--dp-teal);
  color: #fff;
  box-shadow: 0 4px 14px rgba(0, 143, 138, 0.25);
}

.dp-btn--primary:hover {
  background: var(--dp-teal-dark);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 143, 138, 0.35);
  color: #fff;
}

.dp-btn--secondary {
  background: #ffffff;
  color: var(--dp-text);
  border: 1px solid var(--dp-border);
}

.dp-btn--secondary:hover {
  border-color: var(--dp-teal);
  color: var(--dp-teal);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}

.dp-hero-card {
  position: relative;
  border-radius: 26px;
  overflow: hidden;
  box-shadow: 0 20px 45px rgba(0, 143, 138, 0.12);
  border: 1px solid rgba(0, 143, 138, 0.15);
  background: #ffffff;
}

.dp-hero-img {
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
  display: block;
}

.dp-hero-stat {
  position: absolute;
  bottom: 18px;
  right: 18px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  padding: 10px 16px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  border: 1px solid rgba(255,255,255,0.8);
}

.dp-hero-stat-icon {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: var(--dp-teal-light);
  color: var(--dp-teal);
  display: flex;
  align-items: center;
  justify-content: center;
}

.dp-hero-stat strong {
  display: block;
  font-size: 14px;
  font-weight: 800;
  color: var(--dp-text);
}

.dp-hero-stat small {
  display: block;
  font-size: 11px;
  color: var(--dp-muted);
}

/* ── grids ── */
.dp-grid {
  display: grid;
  gap: 24px;
}

.dp-grid--3 {
  grid-template-columns: repeat(3, 1fr);
}

.dp-grid--4 {
  grid-template-columns: repeat(4, 1fr);
}

/* ── cards ── */
.dp-card-link {
  text-decoration: none;
  color: inherit;
  display: block;
  height: 100%;
}

.dp-card {
  background: var(--dp-bg);
  border: 1px solid var(--dp-border);
  border-radius: var(--dp-radius);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  height: 100%;
  box-shadow: 0 4px 14px rgba(0,0,0,0.04);
  transition: transform 0.3s cubic-bezier(0.16,1,0.3,1),
              box-shadow 0.3s cubic-bezier(0.16,1,0.3,1),
              border-color 0.3s ease;
}

.dp-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 36px rgba(0,0,0,0.08);
  border-color: rgba(0, 143, 138, 0.25);
  text-decoration: none;
}

.dp-card-img {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: #f1f5f9;
}

.dp-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.6s cubic-bezier(0.16,1,0.3,1);
}

.dp-card:hover .dp-card-img img {
  transform: scale(1.06);
}

.dp-badge {
  position: absolute;
  top: 14px;
  right: 14px;
  background: rgba(255,255,255,0.92);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  color: var(--dp-teal);
  padding: 4px 13px;
  font-size: 11px;
  font-weight: 800;
  border-radius: 20px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.06);
  z-index: 2;
  transition: all 0.3s ease;
}

.dp-card:hover .dp-badge {
  background: var(--dp-teal);
  color: #fff;
}

.dp-card-body {
  padding: 22px 24px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.dp-card-title {
  font-size: 17px;
  font-weight: 800;
  color: var(--dp-text);
  margin: 0 0 10px;
  line-height: 1.5;
}

.dp-card-desc {
  font-size: 13.5px;
  color: var(--dp-muted);
  line-height: 1.75;
  margin: 0 0 16px;
  flex-grow: 1;
}

.dp-card-cta {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--dp-teal);
  font-size: 13px;
  font-weight: 800;
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid var(--dp-border);
  transition: color 0.22s ease;
}

.dp-card-cta svg {
  transition: transform 0.25s cubic-bezier(0.16,1,0.3,1);
  color: var(--dp-gold);
}

.dp-card:hover .dp-card-cta {
  color: var(--dp-teal-dark);
}

.dp-card:hover .dp-card-cta svg {
  transform: translateX(-4px);
}

/* ── team cards (MDT) ── */
.dp-team-card {
  background: var(--dp-bg);
  border: 1px solid var(--dp-border);
  border-radius: var(--dp-radius);
  padding: 24px 20px;
  display: flex;
  flex-direction: column;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}

.dp-team-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.06);
  border-color: rgba(0, 143, 138, 0.25);
}

.dp-team-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: var(--dp-teal-light);
  color: var(--dp-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
  transition: background 0.25s ease, color 0.25s ease;
}

.dp-team-card:hover .dp-team-icon {
  background: var(--dp-teal);
  color: #fff;
}

.dp-team-title {
  font-size: 15px;
  font-weight: 800;
  color: var(--dp-text);
  margin: 0 0 8px;
  line-height: 1.45;
}

.dp-team-desc {
  font-size: 12.5px;
  color: var(--dp-muted);
  line-height: 1.7;
  margin: 0;
}

/* ── resume banner ── */
.dp-resume-banner {
  background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%);
  border: 1px solid rgba(0, 143, 138, 0.2);
  border-radius: 26px;
  padding: 44px;
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 40px;
  align-items: center;
  box-shadow: 0 10px 30px rgba(0, 143, 138, 0.06);
}

.dp-resume-title {
  font-size: 24px;
  font-weight: 900;
  color: var(--dp-text);
  margin: 0 0 12px;
  line-height: 1.4;
}

.dp-resume-desc {
  font-size: 14.5px;
  color: var(--dp-muted);
  line-height: 1.8;
  margin: 0 0 24px;
}

.dp-resume-features {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 28px;
}

.dp-resume-feature {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13.5px;
  font-weight: 600;
  color: var(--dp-text);
}

.dp-resume-feature-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: var(--dp-teal-light);
  color: var(--dp-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.dp-resume-card {
  background: #ffffff;
  border: 1px solid var(--dp-border);
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.05);
}

.dp-resume-card-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--dp-border);
  margin-bottom: 18px;
}

.dp-avatars {
  display: flex;
  align-items: center;
}

.dp-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f1f5f9;
  border: 2px solid #ffffff;
  margin-left: -10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--dp-teal);
  font-size: 12px;
  font-weight: 800;
}

.dp-avatar:last-child {
  margin-left: 0;
}

.dp-avatar--plus {
  background: var(--dp-teal);
  color: #fff;
}

.dp-resume-card-badge {
  text-align: left;
}

.dp-resume-card-badge strong {
  display: block;
  font-size: 14px;
  font-weight: 800;
  color: var(--dp-text);
}

.dp-resume-card-badge small {
  display: block;
  font-size: 11px;
  color: var(--dp-muted);
}

.dp-resume-card-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.dp-resume-card-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  color: var(--dp-text);
}

.dp-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--dp-teal);
  flex-shrink: 0;
}

/* ── empty state ── */
.dp-empty {
  grid-column: 1 / -1;
  background: var(--dp-bg);
  border: 1px dashed var(--dp-border);
  color: var(--dp-muted);
  border-radius: var(--dp-radius);
  padding: 48px 24px;
  text-align: center;
  font-size: 15px;
  font-weight: 500;
}

/* ── quote ── */
.dp-quote {
  background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);
  border-right: 4px solid var(--dp-teal);
  border-radius: 16px;
  padding: 28px 32px;
  display: flex;
  align-items: center;
  gap: 24px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.03);
}

.dp-quote-mark {
  color: var(--dp-teal);
  flex-shrink: 0;
  opacity: 0.8;
}

.dp-quote-text {
  margin: 0;
  font-size: 15.5px;
  font-weight: 600;
  color: var(--dp-text);
  line-height: 1.85;
}

/* ── responsive ── */
@media (max-width: 992px) {
  .dp-hero-grid {
    grid-template-columns: 1fr;
    gap: 36px;
  }
  .dp-hero-visual {
    max-width: 520px;
    margin: 0 auto;
  }
  .dp-grid--3 {
    grid-template-columns: repeat(2, 1fr);
  }
  .dp-grid--4 {
    grid-template-columns: repeat(2, 1fr);
  }
  .dp-resume-banner {
    grid-template-columns: 1fr;
    padding: 32px 24px;
  }
}

@media (max-width: 640px) {
  .dp-section {
    padding: 40px 16px;
  }
  .dp-hero {
    padding: 28px 16px 44px;
  }
  .dp-grid--3,
  .dp-grid--4 {
    grid-template-columns: 1fr;
  }
  .dp-heading {
    font-size: 24px;
  }
  .dp-hero-title {
    font-size: 24px;
  }
  .dp-quote {
    flex-direction: column;
    text-align: center;
    padding: 24px 20px;
  }
}
</style>
