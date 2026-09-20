<?php
require_once __DIR__ . '/../../../core/component-lang.php';
/* ============================================================================
 *  کامپوننت عمومی «معرفی شعبه» — خیریه مکسا
 * ----------------------------------------------------------------------------
 *  نمایش اطلاعات جامع شعبه شامل:
 *    - متن و تاریخچه معرفی ثبت‌شده توسط مدیر شعبه
 *    - آدرس دقیق، موقعیت و شماره‌های تماس
 *    - گالری تصاویر اختصاصی شعبه با قابلیت لایت‌باکس تعاملی (Lightbox)
 * ========================================================================== */

// اتصال به دیتابیس در صورت نیاز
$biPdo = null;
if (isset($pdo) && $pdo instanceof PDO) {
    $biPdo = $pdo;
} else {
    try {
        if (file_exists(__DIR__ . '/../../../core/db-config.php')) {
            $dbConf = require __DIR__ . '/../../../core/db-config.php';
            $biPdo = new PDO(
                "mysql:host={$dbConf['host']};dbname={$dbConf['name']};charset={$dbConf['charset']}",
                $dbConf['user'],
                $dbConf['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        }
    } catch (Throwable $e) {}
}

// تشخیص اسلاگ شعبه
$biSlug = $branchSlug ?? ($_GET['slug'] ?? ($_GET['branch'] ?? ''));
$biBranch = null;
$biIntro = null;

if ($biPdo && $biSlug !== '') {
    try {
        $st = $biPdo->prepare("SELECT * FROM branches WHERE slug = ? LIMIT 1");
        $st->execute([$biSlug]);
        $biBranch = $st->fetch();

        if ($biBranch) {
            $stIntro = $biPdo->prepare("SELECT * FROM branch_intros WHERE branch_id = ? LIMIT 1");
            $stIntro->execute([$biBranch['id']]);
            $biIntro = $stIntro->fetch();
        }
    } catch (Throwable $e) {}
}

$branchDisplayName = $biBranch['name'] ?? ($branchName ?? 'مکسا');
$branchActualSlug  = $biBranch['slug'] ?? ($biSlug ?: 'home');
$introTitle        = !empty($biIntro['title']) ? $biIntro['title'] : ('آشنایی با شعبه ' . $branchDisplayName . ' مکسا');
$introText         = !empty($biIntro['intro_text']) ? $biIntro['intro_text'] : '';
$introAddress      = !empty($biIntro['address']) ? $biIntro['address'] : (!empty($biBranch['address']) ? $biBranch['address'] : 'آدرس این شعبه به‌زودی در سامانه درج خواهد شد.');
$introPhone        = !empty($biIntro['phone']) ? $biIntro['phone'] : (!empty($biBranch['phone']) ? $biBranch['phone'] : '۰۲۱-۸۸۸۸۰۰۰۰');
$introHours        = !empty($biIntro['working_hours']) ? $biIntro['working_hours'] : 'شنبه تا چهارشنبه: ۸:۰۰ الی ۱۶:۰۰';

$introImages = [];
if (!empty($biIntro['images'])) {
    $decoded = json_decode((string)$biIntro['images'], true);
    if (is_array($decoded)) {
        $introImages = array_values(array_filter($decoded, 'is_string'));
    }
}

function bi_esc($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>

<style>
  @font-face {
    font-family: 'Vazirmatn';
    src: url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations'),
         url('/webfont/Vazirmatn[wght].woff2') format('woff2');
    font-weight: 100 900;
    font-style: normal;
    font-display: swap;
  }

  .bi-root {
    --teal: #007b7a;
    --teal-dark: #005f5e;
    --teal-light: #10aeb8;
    --orange: #f5a623;
    --orange-hover: #e09415;
    --text: #2f3437;
    --muted: #6b7280;
    --border: #e5e7eb;
    --bg-page: #f8fafb;
    --card-bg: #ffffff;
    --radius-sm: 12px;
    --radius-md: 18px;
    --radius-lg: 24px;
    --shadow-soft: 0 4px 20px -2px rgba(0, 123, 122, 0.08), 0 2px 6px -1px rgba(0, 0, 0, 0.04);
    --shadow-card: 0 10px 30px -5px rgba(0, 0, 0, 0.06), 0 4px 10px -2px rgba(0, 0, 0, 0.03);
    --shadow-lg: 0 20px 40px -10px rgba(0, 60, 60, 0.15);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    
    font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    direction: rtl;
    background: var(--bg-page);
    color: var(--text);
    padding-bottom: 70px;
    overflow-x: hidden;
  }

  .bi-root * {
    box-sizing: border-box;
  }

  .bi-container {
    max-width: 1240px;
    margin: 0 auto;
    padding: 0 20px;
  }

  /* نوار مسیر نما (Breadcrumbs) */
  .bi-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 0 20px;
    font-size: 13px;
    color: var(--muted);
    flex-wrap: wrap;
  }
  .bi-breadcrumbs a {
    color: var(--muted);
    text-decoration: none;
    transition: color .2s ease;
  }
  .bi-breadcrumbs a:hover {
    color: var(--teal);
  }
  .bi-breadcrumbs .sep {
    color: #c4cacf;
    font-size: 11px;
  }
  .bi-breadcrumbs .current {
    color: var(--teal);
    font-weight: 700;
  }

  /* بنر هیرو شاخص معرفی شعبه */
  .bi-hero {
    position: relative;
    border-radius: var(--radius-lg);
    background: linear-gradient(135deg, #06393b 0%, #0a5655 50%, #084344 100%);
    color: #ffffff;
    padding: 46px 40px;
    margin-bottom: 34px;
    overflow: hidden;
    box-shadow: var(--shadow-lg);
  }
  .bi-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    opacity: .07;
    background-image: radial-gradient(#ffffff 1.2px, transparent 1.2px);
    background-size: 24px 24px;
    pointer-events: none;
  }
  .bi-hero::after {
    content: "";
    position: absolute;
    top: -50%;
    left: -20%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(245, 166, 35, 0.22), transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }
  .bi-hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
  }
  .bi-hero-info {
    display: flex;
    align-items: center;
    gap: 24px;
    flex: 1 1 500px;
  }
  .bi-hero-badge {
    width: 84px;
    height: 84px;
    border-radius: 22px;
    background: linear-gradient(145deg, rgba(255,255,255,0.18), rgba(255,255,255,0.06));
    border: 1.5px solid rgba(255,255,255,0.25);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: grid;
    place-items: center;
    font-size: 38px;
    font-weight: 900;
    color: #ffffff;
    flex-shrink: 0;
    box-shadow: 0 12px 24px rgba(0,0,0,0.2);
  }
  .bi-hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--orange);
    color: #062828;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 8px;
  }
  .bi-hero-title {
    font-size: 30px;
    font-weight: 900;
    margin: 0 0 6px;
    line-height: 1.3;
    letter-spacing: -0.5px;
  }
  .bi-hero-sub {
    margin: 0;
    color: rgba(255, 255, 255, 0.82);
    font-size: 14px;
    line-height: 1.7;
  }
  .bi-hero-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }
  .bi-btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 22px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.26);
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 700;
    text-decoration: none;
    transition: all .2s var(--ease);
    backdrop-filter: blur(6px);
  }
  .bi-btn-back:hover {
    background: #ffffff;
    color: var(--teal-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.18);
  }

  /* ساختار دو ستونه صفحه اصلی */
  .bi-main-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
  }
  @media (min-width: 980px) {
    .bi-main-layout {
      grid-template-columns: 1fr 360px;
    }
  }

  /* کارت‌های طراحی شیشه‌ای و سفید */
  .bi-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 32px;
    margin-bottom: 26px;
    box-shadow: var(--shadow-card);
    transition: border-color .2s ease;
  }
  .bi-card:hover {
    border-color: rgba(0, 123, 122, 0.25);
  }

  .bi-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 18px;
    margin-bottom: 24px;
    border-bottom: 1px solid var(--border);
  }
  .bi-card-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
    font-size: 20px;
    font-weight: 800;
    color: #1a2226;
  }
  .bi-card-title-icon {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: rgba(0, 123, 122, 0.1);
    color: var(--teal);
    display: grid;
    place-items: center;
    flex-shrink: 0;
  }

  /* متن معرفی و پاراگراف‌ها */
  .bi-intro-prose {
    font-size: 15.5px;
    line-height: 2.1;
    color: #374151;
    text-align: justify;
  }
  .bi-intro-prose p {
    margin-bottom: 18px;
  }
  .bi-intro-prose p:last-child {
    margin-bottom: 0;
  }
  .bi-intro-empty {
    text-align: center;
    padding: 36px 20px;
    color: var(--muted);
    background: #fdfefe;
    border-radius: 14px;
    border: 1.5px dashed #e2e8f0;
  }

  /* گالری تصاویر مدرن */
  .bi-gallery-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }
  .bi-gallery-counter {
    background: rgba(0, 123, 122, 0.08);
    color: var(--teal);
    font-size: 12.5px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 999px;
  }

  .bi-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 18px;
  }
  .bi-gallery-item {
    position: relative;
    aspect-ratio: 16/11;
    border-radius: 16px;
    overflow: hidden;
    background: #111827;
    cursor: pointer;
    box-shadow: var(--shadow-soft);
    border: 1px solid var(--border);
    transition: transform .3s var(--ease), box-shadow .3s var(--ease), border-color .3s;
  }
  .bi-gallery-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 30px -6px rgba(0, 123, 122, 0.2);
    border-color: var(--teal);
  }
  .bi-gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .5s var(--ease), opacity .3s;
  }
  .bi-gallery-item:hover img {
    transform: scale(1.08);
    opacity: 0.9;
  }
  .bi-gallery-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 40, 40, 0.75) 0%, transparent 60%);
    opacity: 0;
    transition: opacity .3s var(--ease);
    display: flex;
    align-items: flex-end;
    padding: 16px;
  }
  .bi-gallery-item:hover .bi-gallery-overlay {
    opacity: 1;
  }
  .bi-gallery-zoom-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.92);
    color: var(--teal-dark);
    font-size: 12px;
    font-weight: 800;
    padding: 5px 12px;
    border-radius: 999px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  }

  /* سایدبار اطلاعات و تماس */
  .bi-sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }
  .bi-sidecard {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 26px;
    box-shadow: var(--shadow-card);
  }
  .bi-sidecard h3 {
    margin: 0 0 18px;
    font-size: 16px;
    font-weight: 800;
    color: #1a2226;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .bi-info-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px dashed var(--border);
  }
  .bi-info-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
  }
  .bi-info-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(0, 123, 122, 0.08);
    color: var(--teal);
    display: grid;
    place-items: center;
    flex-shrink: 0;
    margin-top: 2px;
  }
  .bi-info-title {
    font-size: 12px;
    color: var(--muted);
    font-weight: 700;
    margin-bottom: 4px;
  }
  .bi-info-val {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.6;
  }
  .bi-info-val a {
    color: var(--teal);
    text-decoration: none;
    transition: color .2s;
  }
  .bi-info-val a:hover {
    text-decoration: underline;
  }

  .bi-copy-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    margin-top: 8px;
    transition: all .2s ease;
    font-family: inherit;
  }
  .bi-copy-btn:hover {
    background: var(--teal);
    color: #fff;
    border-color: var(--teal);
  }

  .bi-side-cta {
    background: linear-gradient(135deg, var(--teal), var(--teal-dark));
    color: #fff;
    border-radius: var(--radius-md);
    padding: 24px;
    text-align: center;
    box-shadow: 0 12px 28px -6px rgba(0, 123, 122, 0.4);
  }
  .bi-side-cta h4 {
    margin: 0 0 8px;
    font-size: 17px;
    font-weight: 800;
  }
  .bi-side-cta p {
    margin: 0 0 18px;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.88);
    line-height: 1.7;
  }
  .bi-side-cta-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 20px;
    background: var(--orange);
    color: #062828;
    font-weight: 800;
    font-size: 14px;
    border-radius: 12px;
    text-decoration: none;
    box-shadow: 0 8px 18px rgba(245, 166, 35, 0.35);
    transition: all .2s var(--ease);
  }
  .bi-side-cta-btn:hover {
    background: var(--orange-hover);
    transform: translateY(-2px);
  }

  /* ===== لایت‌باکس تعاملی (LIGHTBOX MODAL) ===== */
  .bi-lightbox {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: rgba(10, 20, 24, 0.88);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    opacity: 0;
    transition: opacity .3s var(--ease);
  }
  .bi-lightbox.show {
    display: flex;
    opacity: 1;
  }
  .bi-lightbox-c {
    position: relative;
    max-width: 90vw;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .bi-lightbox-img {
    max-width: 100%;
    max-height: 78vh;
    border-radius: 16px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.5);
    object-fit: contain;
    user-select: none;
    transition: transform .25s var(--ease);
  }
  .bi-lb-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #fff;
    display: grid;
    place-items: center;
    cursor: pointer;
    backdrop-filter: blur(8px);
    transition: all .2s var(--ease);
    z-index: 10;
  }
  .bi-lb-btn:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-50%) scale(1.08);
  }
  .bi-lb-btn.prev { right: -65px; }
  .bi-lb-btn.next { left: -65px; }
  
  .bi-lb-close {
    position: absolute;
    top: -45px;
    left: 0;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #fff;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    cursor: pointer;
    display: grid;
    place-items: center;
    font-size: 20px;
    transition: all .2s;
  }
  .bi-lb-close:hover {
    background: #e0556b;
    border-color: #e0556b;
  }

  .bi-lb-info {
    margin-top: 14px;
    color: rgba(255, 255, 255, 0.85);
    font-size: 13.5px;
    font-weight: 700;
  }

  @media (max-width: 768px) {
    .bi-hero {
      padding: 30px 20px;
    }
    .bi-hero-info {
      gap: 16px;
    }
    .bi-hero-badge {
      width: 64px;
      height: 64px;
      font-size: 28px;
    }
    .bi-hero-title {
      font-size: 22px;
    }
    .bi-card {
      padding: 22px 18px;
    }
    .bi-lb-btn.prev { right: 10px; }
    .bi-lb-btn.next { left: 10px; }
  }
</style>

<div class="bi-root" id="biRoot">
  <div class="bi-container">

    <!-- نوار مسیر نما -->
    <nav class="bi-breadcrumbs" aria-label="راهنمای مسیر">
      <a href="/home">صفحه اصلی</a>
      <span class="sep">‹</span>
      <a href="/branches.php">شعب مکسا</a>
      <span class="sep">‹</span>
      <a href="/<?= bi_esc($branchActualSlug) ?>">شعبه <?= bi_esc($branchDisplayName) ?></a>
      <span class="sep">‹</span>
      <span class="current">معرفی شعبه</span>
    </nav>

    <!-- هیرو شاخص بالای صفحه -->
    <header class="bi-hero">
      <div class="bi-hero-content">
        <div class="bi-hero-info">
          <div class="bi-hero-badge">
            <?= bi_esc(mb_substr($branchDisplayName, 0, 1)) ?>
          </div>
          <div>
            <span class="bi-hero-kicker">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
              معرفی رسمی شعبه
            </span>
            <h1 class="bi-hero-title"><?= bi_esc($introTitle) ?></h1>
            <p class="bi-hero-sub">شناخت امکانات، رسالت، موقعیت و خدمات مراقبت‌های تسکینی شعبه <?= bi_esc($branchDisplayName) ?> خیریه مکسا</p>
          </div>
        </div>

        <div class="bi-hero-actions">
          <a href="/<?= bi_esc($branchActualSlug) ?>" class="bi-btn-back">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 19 12 12 5"/></svg>
            <span>بازگشت به خانه شعبه</span>
          </a>
        </div>
      </div>
    </header>

    <!-- چیدمان اصلی محتوا و سایدبار -->
    <div class="bi-main-layout">

      <!-- ستون سمت راست: متن معرفی + گالری تصاویر -->
      <main>
        
        <!-- کارت متن معرفی -->
        <article class="bi-card">
          <div class="bi-card-head">
            <h2 class="bi-card-title">
              <div class="bi-card-title-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              </div>
              <span>درباره شعبه <?= bi_esc($branchDisplayName) ?></span>
            </h2>
          </div>

          <div class="bi-intro-prose">
            <?php if (!empty($introText)): ?>
              <?= nl2br(bi_esc($introText)) ?>
            <?php else: ?>
              <div class="bi-intro-empty">
                <p>محتوای تفصیلی و تاریخچه شعبه <?= bi_esc($branchDisplayName) ?> به‌زودی توسط مدیریت این شعبه تکمیل خواهد شد.</p>
                <p style="font-size: 13.5px; margin-top: 6px;">این شعبه کلیه خدمات حمایتی، تسکینی، پزشکی و مشاوره‌ای مکسا را به بیماران مبتلا به سرطان و خانواده‌های گرامی آنان ارائه می‌نماید.</p>
              </div>
            <?php endif; ?>
          </div>
        </article>

        <!-- کارت گالری تصاویر اختصاصی -->
        <section class="bi-card">
          <div class="bi-card-head">
            <h2 class="bi-card-title">
              <div class="bi-card-title-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              </div>
              <span>گالری تصاویر شعبه</span>
            </h2>
            <?php if (!empty($introImages)): ?>
              <span class="bi-gallery-counter"><?= count($introImages) ?> تصویر اختصاصی</span>
            <?php endif; ?>
          </div>

          <?php if (!empty($introImages)): ?>
            <div class="bi-gallery-grid" id="biGalleryGrid">
              <?php foreach ($introImages as $idx => $imgSrc): ?>
                <div class="bi-gallery-item" data-index="<?= $idx ?>" onclick="openLightbox(<?= $idx ?>)">
                  <img src="<?= bi_esc($imgSrc) ?>" alt="تصویر شعبه <?= bi_esc($branchDisplayName) ?>" loading="lazy">
                  <div class="bi-gallery-overlay">
                    <span class="bi-gallery-zoom-badge">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                      مشاهده اندازه بزرگ
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="bi-intro-empty">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 8px; color: var(--teal); opacity: .7;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <p>تصاویر اختصاصی این شعبه به‌زودی بارگذاری خواهد شد.</p>
            </div>
          <?php endif; ?>
        </section>

      </main>

      <!-- ستون سمت چپ: سایدبار اطلاعات تماس و آدرس -->
      <aside class="bi-sidebar">

        <!-- کارت آدرس و موقعیت -->
        <div class="bi-sidecard">
          <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            اطلاعات نشانی و موقعیت
          </h3>

          <div class="bi-info-item">
            <div class="bi-info-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <div class="bi-info-title">آدرس فیزیکی شعبه</div>
              <div class="bi-info-val" id="branchAddressText"><?= bi_esc($introAddress) ?></div>
              <button type="button" class="bi-copy-btn" onclick="copyAddress()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                <span>کپی آدرس</span>
              </button>
            </div>
          </div>
        </div>

        <!-- کارت ارتباط و ساعت کاری -->
        <div class="bi-sidecard">
          <h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            راه‌های ارتباطی و ساعات پذیرش
          </h3>

          <div class="bi-info-item">
            <div class="bi-info-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
              <div class="bi-info-title">تلفن تماس مستقیم</div>
              <div class="bi-info-val" dir="ltr">
                <a href="tel:<?= preg_replace('/[^0-9+]/', '', $introPhone) ?>"><?= bi_esc($introPhone) ?></a>
              </div>
            </div>
          </div>

          <div class="bi-info-item">
            <div class="bi-info-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
              <div class="bi-info-title">ساعات کاری و پذیرش</div>
              <div class="bi-info-val"><?= bi_esc($introHours) ?></div>
            </div>
          </div>
        </div>

        <!-- دعوت به همراهی و کمک -->
        <div class="bi-side-cta">
          <h4>همراهی با بیماران این شعبه</h4>
          <p>با نیت‌های خیر و حمایت‌های خود می‌توانید در تامین دارو و خدمات تسکینی بیماران شعبه <?= bi_esc($branchDisplayName) ?> سهیم باشید.</p>
          <a href="/onlinedonation" class="bi-side-cta-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/></svg>
            <span>حمایت و کمک آنلاین</span>
          </a>
        </div>

      </aside>

    </div>

  </div>
</div>

<!-- مودال لایت‌باکس بزرگ‌نمایی تصاویر -->
<div class="bi-lightbox" id="biLightbox" role="dialog" aria-modal="true">
  <div class="bi-lightbox-c">
    <button type="button" class="bi-lb-close" onclick="closeLightbox()" aria-label="بستن">&times;</button>
    <button type="button" class="bi-lb-btn prev" onclick="changeLightbox(-1)" aria-label="تصویر قبلی">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
    <img id="biLightboxImg" class="bi-lightbox-img" src="" alt="نمای بزرگ تصویر شعبه">
    <button type="button" class="bi-lb-btn next" onclick="changeLightbox(1)" aria-label="تصویر بعدی">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="bi-lb-info" id="biLightboxInfo">تصویر ۱ از ۴</div>
  </div>
</div>

<script>
(function() {
  const images = <?= json_encode($introImages, JSON_UNESCAPED_UNICODE) ?>;
  let currentIndex = 0;

  const lbModal = document.getElementById('biLightbox');
  const lbImg   = document.getElementById('biLightboxImg');
  const lbInfo  = document.getElementById('biLightboxInfo');

  window.openLightbox = function(index) {
    if (!images || images.length === 0) return;
    currentIndex = (index + images.length) % images.length;
    updateLightbox();
    lbModal.style.display = 'flex';
    void lbModal.offsetHeight;
    lbModal.classList.add('show');
    document.body.style.overflow = 'hidden';
  };

  window.closeLightbox = function() {
    lbModal.classList.remove('show');
    document.body.style.overflow = '';
    setTimeout(() => {
      if (!lbModal.classList.contains('show')) {
        lbModal.style.display = 'none';
      }
    }, 300);
  };

  window.changeLightbox = function(step) {
    if (!images || images.length === 0) return;
    currentIndex = (currentIndex + step + images.length) % images.length;
    updateLightbox();
  };

  function updateLightbox() {
    if (images[currentIndex]) {
      lbImg.src = images[currentIndex];
      const faCur = String(currentIndex + 1).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
      const faTot = String(images.length).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
      lbInfo.textContent = 'تصویر ' + faCur + ' از ' + faTot;
    }
  }

  // بستن با کلیک روی پس‌زمینه
  lbModal.addEventListener('click', function(e) {
    if (e.target === lbModal) {
      closeLightbox();
    }
  });

  // کلیدهای کیبورد
  document.addEventListener('keydown', function(e) {
    if (!lbModal.classList.contains('show')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') changeLightbox(1);
    if (e.key === 'ArrowRight') changeLightbox(-1);
  });

  // کپی کردن آدرس
  window.copyAddress = function() {
    const text = document.getElementById('branchAddressText').innerText;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text).then(() => {
        alert('آدرس شعبه در کلیپ‌بورد کپی شد.');
      }).catch(() => {});
    }
  };
})();
</script>
