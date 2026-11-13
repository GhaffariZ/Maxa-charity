<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/core/site-locale.php';
require_once dirname(__DIR__, 2) . '/course-db.php';

// Safe database connection
$coursesList = [];
try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        $cfgPath = dirname(__DIR__, 2) . '/core/db-config.php';
        if (file_exists($cfgPath)) {
            $DB = require $cfgPath;
            $host = $DB['host'] ?? 'localhost';
            $name = $DB['name'] ?? '';
            $user = $DB['user'] ?? '';
            $pass = $DB['pass'] ?? '';
            $charset = $DB['charset'] ?? 'utf8mb4';
            $pdo = new PDO("mysql:host={$host};dbname={$name};charset={$charset}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
    }
    if (isset($pdo) && ($pdo instanceof PDO)) {
        $coursesList = load_courses($pdo, true, 'published');
    }
} catch (Throwable $e) {
    $coursesList = [];
}

if (empty($coursesList)) {
    $coursesList = maxa_demo_courses();
}

$showcaseCourses = array_slice($coursesList, 0, 3);
?>

<section class="maxa-courses-showcase-section" aria-labelledby="courses-showcase-title">
  <style>
    .maxa-courses-showcase-section {
      position: relative;
      background: #f8fafc;
      padding: 72px 20px 80px;
      direction: var(--site-direction, rtl);
      font-family: 'Vazirmatn', sans-serif;
      overflow: hidden;
      border-top: 1px solid #e2e8f0;
      border-bottom: 1px solid #e2e8f0;
    }

    .maxa-courses-showcase-section::before {
      content: "";
      position: absolute;
      top: -120px;
      left: 10%;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, rgba(0, 143, 138, 0.06) 0%, transparent 70%);
      pointer-events: none;
    }

    .csc-container {
      width: min(var(--cta-container, 1380px), 100%);
      margin-inline: auto;
      box-sizing: border-box;
      position: relative;
      z-index: 1;
    }

    /* HEADER */
    .csc-header {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 40px;
    }

    .csc-header-text {
      max-width: 680px;
    }

    .csc-pill {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 14px;
      border-radius: 999px;
      background: #e6f6f5;
      color: #008f8a;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 12px;
      border: 1px solid rgba(0, 143, 138, 0.18);
    }

    .csc-pill-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #008f8a;
      box-shadow: 0 0 8px rgba(0, 143, 138, 0.6);
    }

    .csc-title {
      font-size: clamp(22px, 3.2vw, 32px);
      font-weight: 900;
      color: #0f172a;
      line-height: 1.35;
      margin: 0 0 10px;
    }

    .csc-subtitle {
      font-size: 15px;
      line-height: 1.75;
      color: #64748b;
      margin: 0;
    }

    .csc-all-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      background: #ffffff;
      color: #008f8a;
      border: 1.5px solid #008f8a;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 0 4px 12px rgba(0, 143, 138, 0.08);
      white-space: nowrap;
    }

    .csc-all-btn:hover {
      background: #008f8a;
      color: #ffffff;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 143, 138, 0.2);
    }

    .csc-all-btn svg {
      transition: transform 0.2s ease;
    }

    .csc-all-btn:hover svg {
      transform: translateX(-4px);
    }

    /* GRID */
    .csc-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 24px;
    }

    @media (max-width: 1024px) {
      .csc-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 640px) {
      .csc-grid {
        grid-template-columns: 1fr;
      }
      .csc-header {
        flex-direction: column;
        align-items: flex-start;
      }
    }

    /* CARD */
    .csc-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
      transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.22s ease;
      position: relative;
    }

    .csc-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 32px rgba(15, 23, 42, 0.09);
      border-color: #cbd5e1;
    }

    .csc-card-accent {
      height: 4px;
      width: 100%;
      background: linear-gradient(90deg, #008f8a, #05a8a2);
    }

    .csc-card-body {
      padding: 24px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }

    .csc-card-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 14px;
    }

    .csc-card-cat {
      font-size: 12px;
      font-weight: 700;
      color: #008f8a;
      background: #e6f6f5;
      padding: 4px 10px;
      border-radius: 6px;
    }

    .csc-card-rating {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 12.5px;
      font-weight: 700;
      color: #b45309;
      background: #fef3c7;
      padding: 3px 8px;
      border-radius: 6px;
    }

    .csc-card-title {
      font-size: 17px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.45;
      margin: 0 0 10px;
      min-height: 48px;
    }

    .csc-card-title a {
      color: inherit;
      text-decoration: none;
      transition: color 0.18s ease;
    }

    .csc-card-title a:hover {
      color: #008f8a;
    }

    .csc-card-desc {
      font-size: 13.5px;
      line-height: 1.65;
      color: #64748b;
      margin: 0 0 20px;
      flex: 1;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .csc-card-instructor {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #475569;
      padding-top: 14px;
      border-top: 1px solid #f1f5f9;
      margin-bottom: 16px;
    }

    .csc-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding-top: 14px;
      border-top: 1px solid #f1f5f9;
    }

    .csc-price-col {
      display: flex;
      flex-direction: column;
    }

    .csc-price-amount {
      font-size: 17px;
      font-weight: 900;
      color: #0f172a;
    }

    .csc-price-unit {
      font-size: 11.5px;
      font-weight: 600;
      color: #94a3b8;
    }

    .csc-card-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 9px 18px;
      background: #008f8a;
      color: #ffffff;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 700;
      text-decoration: none;
      transition: background 0.18s ease, transform 0.18s ease;
    }

    .csc-card-btn:hover {
      background: #057a76;
      transform: translateY(-1px);
    }
  </style>

  <div class="csc-container">
    <div class="csc-header">
      <div class="csc-header-text">
        <span class="csc-pill">
          <span class="csc-pill-dot" aria-hidden="true"></span>
          <?= site_e('آکادمی آموزش‌های تخصصی و عمومی مکسا') ?>
        </span>
        <h2 id="courses-showcase-title" class="csc-title"><?= site_e('دوره‌های آموزشی و توانمندسازی سلامت و نیکوکاری') ?></h2>
        <p class="csc-subtitle"><?= site_e('آموزش مهارت‌های مراقبت تسکینی، سلامت روان، پیشگیری و ارتقای کیفیت زندگی بیماران و خانواده‌ها') ?></p>
      </div>

      <a href="/courses" class="csc-all-btn">
        <span><?= site_e('مشاهده همه دوره‌ها') ?></span>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
      </a>
    </div>

    <div class="csc-grid">
      <?php foreach ($showcaseCourses as $c): ?>
        <?php
          $cId = (int)($c['id'] ?? 0);
          $cTitle = htmlspecialchars($c['title'] ?? '');
          $cSub = htmlspecialchars($c['subtitle'] ?? '');
          $cCat = htmlspecialchars($c['category'] ?? 'عمومی');
          $cInstructor = htmlspecialchars($c['instructor'] ?? 'استاد مکسا');
          $cRating = fa_digits(number_format((float)($c['rating'] ?? 4.8), 1));
          $cPrice = (float)($c['discount_price'] ?? $c['price'] ?? 0);
          $isFree = !empty($c['is_free']) || $cPrice <= 0;
          $cAccent = !empty($c['accent']) ? htmlspecialchars($c['accent']) : '#008f8a';
        ?>
        <article class="csc-card">
          <div class="csc-card-accent" style="background: <?= $cAccent ?>"></div>
          <div class="csc-card-body">
            <div class="csc-card-meta">
              <span class="csc-card-cat"><?= $cCat ?></span>
              <span class="csc-card-rating">★ <?= $cRating ?></span>
            </div>

            <h3 class="csc-card-title">
              <a href="/courses/<?= $cId ?>"><?= $cTitle ?></a>
            </h3>

            <p class="csc-card-desc"><?= $cSub ?: site_e('دوره آموزشی تخصصی آکادمی مکسا برای یادگیری مهارت‌های کاربردی.') ?></p>

            <div class="csc-card-instructor">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <span><?= $cInstructor ?></span>
            </div>

            <div class="csc-card-footer">
              <div class="csc-price-col">
                <?php if ($isFree): ?>
                  <span class="csc-price-amount" style="color: #10b981;"><?= site_e('رایگان') ?></span>
                <?php else: ?>
                  <span class="csc-price-amount"><?= fa_digits(number_format($cPrice)) ?></span>
                  <span class="csc-price-unit"><?= site_e('تومان') ?></span>
                <?php endif; ?>
              </div>

              <a href="/courses/<?= $cId ?>" class="csc-card-btn">
                <span><?= site_e('مشاهده دوره') ?></span>
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
