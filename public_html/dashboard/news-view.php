<?php
require_once __DIR__ . '/../core/site-locale.php';
// اصلاح نحوه اتصال به دیتابیس برای سازگاری در توسعه محلی و پروداکشن
if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/../config/database.php")) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/database.php";
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/html-sanitizer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/component-renderer.php';

/* -----------------------------------------
   دریافت id و slug از آدرس /id/slug/
   ------------------------------------------- */

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriPath = urldecode($uriPath ?: '/');
$parts = array_values(array_filter(explode('/', trim($uriPath, '/')), 'strlen'));

$id = null;
$slug = null;

if (!empty($parts)) {
    if (is_numeric($parts[0] ?? null)) {
        $id = $parts[0];
        $slug = $parts[1] ?? '';
    } elseif (($parts[0] ?? '') === 'news-view.php' && is_numeric($parts[1] ?? null)) {
        $id = $parts[1];
        $slug = $parts[2] ?? '';
    } elseif (is_numeric($_GET['id'] ?? null)) {
        $id = $_GET['id'];
        $slug = $_GET['slug'] ?? '';
    }
}

if ($id === null && is_numeric($_GET['id'] ?? null)) {
    $id = $_GET['id'];
    $slug = $_GET['slug'] ?? '';
}

if (!is_numeric($id)) {
    die("شناسه نامعتبر است");
}

/* -----------------------------------------
   واکشی خبر از دیتابیس
   ------------------------------------------- */

$stmt = $pdo->prepare("
    SELECT n.*, c.name AS category_name
    FROM news n
    LEFT JOIN news_categories c ON c.id = n.category_id
    WHERE n.id = ?
");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    die("خبر یافت نشد");
}

// دریافت برچسب‌های چندگانه خبر
$db_tags = [];
try {
    $stmt_db_tags = $pdo->prepare("
        SELECT t.name 
        FROM news_tags t 
        JOIN news_tags_map m ON t.id = m.tag_id 
        WHERE m.news_id = ?
    ");
    $stmt_db_tags->execute([$id]);
    $db_tags = $stmt_db_tags->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $db_tags = [];
}

/* -----------------------------------------
   بررسی slug صحیح و ریدایرکت ۳۰۱ برای سئو
   ------------------------------------------- */

function slugify($text) {
    $text = trim($text);
    $text = preg_replace('/[^a-zA-Z0-9آ-ی\-]+/u', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function gregorianToJalali($gy, $gm, $gd) {
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

    if ($gy > 1600) {
        $jy = 979;
        $gy -= 1600;
    } else {
        $jy = 0;
        $gy -= 621;
    }

    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

    $days = (365 * $gy)
        + intval(($gy2 + 3) / 4)
        - intval(($gy2 + 99) / 100)
        + intval(($gy2 + 399) / 400)
        - 80
        + $gd
        + $g_d_m[$gm - 1];

    $jy += 33 * intval($days / 12053);
    $days %= 12053;
    $jy += 4 * intval($days / 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += intval(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + intval($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intval(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [$jy, $jm, $jd];
}

function faNumbers($text) {
    return str_replace(
        ['0','1','2','3','4','5','6','7','8','9'],
        ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
        (string)$text
    );
}

function formatJalaliDateTimeFa($datetime) {
    if (empty($datetime)) {
        return '---';
    }

    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return '---';
    }

    $gy = (int)date('Y', $timestamp);
    $gm = (int)date('m', $timestamp);
    $gd = (int)date('d', $timestamp);
    [$jy, $jm, $jd] = gregorianToJalali($gy, $gm, $gd);
    $hour = date('H', $timestamp);
    $minute = date('i', $timestamp);

    return faNumbers(sprintf('%04d/%02d/%02d - %s:%s', $jy, $jm, $jd, $hour, $minute));
}

$correctSlug = slugify($news['title']);

if ($slug === '' || $slug !== $correctSlug) {
    header("Location: /$id/$correctSlug/", true, 301);
    exit;
}

/* -----------------------------------------
   ثبت بازدید مستقیم در دیتابیس
   ------------------------------------------- */

$total_views = isset($news['viewed']) ? (int)$news['viewed'] : 0;

try {
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $news_id = $news['id'];
    
    $check_stmt = $pdo->prepare("
        SELECT id FROM news_view_logs 
        WHERE news_id = ? AND user_ip = ? 
        AND viewed_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)
        LIMIT 1
    ");
    $check_stmt->execute([$news_id, $user_ip]);
    
    if (!$check_stmt->fetch()) {
        $log_stmt = $pdo->prepare("
            INSERT INTO news_view_logs (news_id, user_ip) 
            VALUES (?, ?)
        ");
        $log_stmt->execute([$news_id, $user_ip]);
        
        $update_stmt = $pdo->prepare("
            UPDATE news SET viewed = viewed + 1 
            WHERE id = ?
        ");
        $update_stmt->execute([$news_id]);
        
        $total_views++;
    }
} catch (Exception $e) {
    error_log("Error logging view: " . $e->getMessage());
}

$featured_path = '';
$has_featured_image = false;
if (!empty($news['featured_image']) && !empty($news['news_code'])) {
  $featured_path = "/uploads/news/{$news['news_code']}/" . $news['featured_image'];
  if (file_exists($_SERVER['DOCUMENT_ROOT'] . $featured_path) || file_exists(__DIR__ . '/..' . $featured_path)) {
    $has_featured_image = true;
  }
}
$publishDateFa = formatJalaliDateTimeFa($news['publish_date'] ?? null);
$totalViewsFa = faNumbers(number_format($total_views));
$readTimeValue = isset($news['read_time']) ? (int)$news['read_time'] : 1;
if ($readTimeValue < 1) { $readTimeValue = 1; }
$readTimeFa = faNumbers($readTimeValue);

// سه خبر جدید از همان دسته، غیر از خبر فعلی
$relatedNews = [];
try {
    $latStmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name
        FROM news n
        LEFT JOIN news_categories c ON c.id = n.category_id
        WHERE n.status = 'published'
          AND n.publish_date <= NOW()
          AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '')
          AND n.id <> ?
          AND n.category_id = ?
        ORDER BY n.publish_date DESC, n.id DESC
        LIMIT 3
    ");
    $latStmt->execute([$id, $news['category_id'] ?? null]);
    $relatedNews = $latStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // ignore
}

// تابع کمکی ایجاد عکس لوکال در صورت نبود تصویر شاخص
function buildLocalPlaceholderImageDetail($text = 'بدون تصویر') {
    $safeText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675">'
        . '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0%" stop-color="#e8ecef"/><stop offset="100%" stop-color="#d5dce1"/>'
        . '</linearGradient></defs>'
        . '<rect width="1200" height="675" fill="url(#g)"/>'
        . '<g fill="#6b7280" font-family="Vazirmatn, Tahoma, sans-serif" text-anchor="middle">'
        . '<text x="600" y="330" font-size="54" font-weight="700">' . $safeText . '</text>'
        . '<text x="600" y="390" font-size="28">No Image</text>'
        . '</g></svg>';
    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
}

function getFooterHTML() {
    return dashboard_component_html('footer', get_defined_vars());
}

// بارگذاری هدر سراسری
$pageTitle = htmlspecialchars($news['title']) . ' — آخرین اخبار مکسا';
render_dashboard_component('header', get_defined_vars());
?>

<!-- لود Font Awesome با پالیسی بدون رهگیری جهت پیشگیری از هشدار مرورگر -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- استایل‌های اختصاصی و پریمیوم صفحه نمایش خبر -->
<style>
    :root {
        --news-primary: #008f8a;      /* سبز برند مکسا */
        --news-accent: #faa61a;       /* نارنجی/خردلی برند مکسا */
        --news-bg: #f8fafc;
        --news-card-bg: #ffffff;
        --news-text: #2f3437;
        --news-text-muted: #8b8f96;
        --news-border: #eef0f2;
    }

    body {
        background-color: var(--news-bg) !important;
        color: var(--news-text);
        font-family: 'Vazirmatn', sans-serif !important;
    }

    .cta-navbar-spacer {
        height: var(--cta-nav-h, 78px);
    }

    .news-detail-wrapper {
        max-width: 1240px;
        margin: 0 auto;
        padding: 48px 20px 80px;
        direction: var(--site-direction, rtl);
    }

    /* بردکرامب */
    .news-detail-breadcrumbs {
        font-size: 13.5px;
        color: var(--news-text-muted);
        margin-bottom: 24px;
        text-align: start;
    }
    .news-detail-breadcrumbs a {
        color: var(--news-primary);
        text-decoration: none;
        font-weight: 700;
    }
    .news-detail-breadcrumbs a:hover {
        color: var(--news-accent);
    }

    /* خبر تمام عرض، با ستون خواندنی محدود در داخل آن */
    .news-detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
    }

    /* ستون راست: بدنه و محتوای خبر */
    .article-container {
        min-width: 0;
        background: var(--news-card-bg);
        border: 1px solid var(--news-border);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.015);
        display: flex;
        flex-direction: column;
    }

    /* بنر عکس شاخص همراه با گرادیان مشکی و تیتر روی عکس */
    .article-hero-banner {
        position: relative;
        width: 100%;
        min-height: clamp(340px, 32vw, 460px);
        background-color: #0c121c;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    .article-hero-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .article-hero-banner:hover .article-hero-img {
        transform: scale(1.02);
    }
    .article-hero-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            180deg,
            rgba(0, 0, 0, 0.05) 0%,
            rgba(0, 0, 0, 0.25) 30%,
            rgba(12, 18, 28, 0.78) 65%,
            rgba(12, 18, 28, 0.96) 100%
        );
        pointer-events: none;
        z-index: 1;
    }
    .article-hero-content {
        position: relative;
        z-index: 2;
        padding: 40px 40px 32px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        text-align: start;
    }
    .article-hero-category {
        align-self: flex-start;
        background: rgba(8, 153, 169, 0.92);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: #ffffff;
        padding: 6px 16px;
        font-size: 12.5px;
        font-weight: 800;
        border-radius: 99px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .article-hero-title {
        color: #ffffff;
        font-size: clamp(22px, 3.2vw, 32px);
        font-weight: 900;
        line-height: 1.55;
        margin: 0;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
        letter-spacing: -0.02em;
    }
    .article-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.9);
        border-top: 1px solid rgba(255, 255, 255, 0.18);
        padding-top: 14px;
        margin-top: 4px;
    }
    .article-hero-meta span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
    }
    .article-hero-meta i {
        color: var(--news-accent);
        font-size: 14px;
    }

    /* بدنه مقاله */
    .article-body-wrapper {
        box-sizing: border-box;
        width: 100%;
        max-width: 940px;
        margin-inline: auto;
        padding: 40px;
    }
    .article-header {
        text-align: start;
        margin-bottom: 32px;
    }
    .article-category {
        background: rgba(8, 153, 169, 0.08);
        color: var(--news-primary);
        padding: 6px 16px;
        font-size: 12.5px;
        font-weight: 800;
        border-radius: 99px;
        display: inline-block;
        margin-bottom: 16px;
    }
    .article-title {
        font-size: clamp(22px, 3.5vw, 30px);
        font-weight: 900;
        line-height: 1.5;
        color: var(--news-text);
        margin: 0 0 20px 0;
    }
    .article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        font-size: 13px;
        color: var(--news-text-muted);
        border-bottom: 1px solid var(--news-border);
        padding-bottom: 20px;
    }
    .article-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .article-meta i {
        color: var(--news-accent);
    }
    .article-content {
        font-size: 17px;
        line-height: 2.05;
        color: rgba(47, 52, 55, 0.9);
        text-align: justify;
        overflow-wrap: anywhere;
    }
    .article-content::after {
        content: "";
        display: table;
        clear: both;
    }
    .article-content p {
        margin-bottom: 24px;
    }
    .article-content h2, .article-content h3 {
        color: var(--news-primary);
        margin-top: 40px;
        margin-bottom: 20px;
        font-weight: 850;
        border-right: 4px solid var(--news-accent);
        padding-right: 14px;
        font-size: clamp(18px, 2.5vw, 22px);
    }
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.04);
        vertical-align: middle;
    }
    .article-content figure,
    .article-content .article-img-wrap {
        display: block;
        max-width: 100%;
        margin: 24px auto;
        position: relative;
        box-sizing: border-box;
        clear: both;
    }
    .article-content figure.align-right,
    .article-content .article-img-wrap.align-right,
    .article-content img.align-right {
        float: right !important;
        margin: 8px 0 20px 24px !important;
        display: block !important;
        clear: right !important;
        text-align: start;
        width: 50%;
        max-width: 100%;
    }
    .article-content figure.align-left,
    .article-content .article-img-wrap.align-left,
    .article-content img.align-left {
        float: left !important;
        margin: 8px 24px 20px 0 !important;
        display: block !important;
        clear: left !important;
        text-align: left;
        width: 50%;
        max-width: 100%;
    }
    .article-content figure.align-center,
    .article-content .article-img-wrap.align-center,
    .article-content img.align-center {
        display: block !important;
        margin: 28px auto !important;
        text-align: center !important;
        float: none !important;
        clear: both !important;
        width: 60%;
        max-width: 100%;
    }
    .article-content figure.align-full,
    .article-content .article-img-wrap.align-full,
    .article-content img.align-full {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 32px 0 !important;
        float: none !important;
        clear: both !important;
        text-align: center;
    }
    .article-content figcaption,
    .article-content .img-caption {
        margin-top: 8px;
        font-size: 13.5px;
        color: var(--news-text-muted, #718096);
        text-align: center;
        line-height: 1.6;
        padding: 4px 8px;
        font-weight: 500;
    }
    .article-content .gallery-row {
        display: flex;
        gap: 14px;
        margin: 24px 0;
        flex-wrap: wrap;
        clear: both;
    }
    .article-content .gallery-row img {
        flex: 1;
        min-width: 28%;
        max-width: 100%;
        border-radius: 10px;
        object-fit: cover;
    }
    @media (max-width: 768px) {
        .article-content figure.align-right,
        .article-content figure.align-left,
        .article-content .article-img-wrap.align-right,
        .article-content .article-img-wrap.align-left,
        .article-content img.align-right,
        .article-content img.align-left {
            float: none !important;
            margin: 20px auto !important;
            display: block !important;
            max-width: 100% !important;
            width: 100% !important;
            text-align: center !important;
        }
    }

    /* بخش تگ‌ها */
    .article-tags {
        margin-top: 40px;
        padding-top: 24px;
        border-top: 1px solid var(--news-border);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        text-align: start;
    }
    .article-tags strong {
        font-size: 13.5px;
        color: var(--news-text);
        margin-left: 6px;
    }
    .tag-chip {
        background: #f8fafc;
        color: var(--news-text-muted);
        padding: 5px 14px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid var(--news-border);
        transition: all 0.2s ease;
    }
    .tag-chip:hover {
        background: var(--news-primary);
        color: #ffffff;
        border-color: var(--news-primary);
    }

    /* اشتراک‌گذاری در انتهای خبر، هماهنگ با آیکن‌های فوتر */
    .article-share {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--news-border);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }
    .article-share-title {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        color: var(--news-text);
    }
    .share-buttons-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .share-icon-btn {
        box-sizing: border-box;
        width: 46px;
        height: 46px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        color: #ffffff;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,0.25);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.2), 0 6px 14px rgba(0,80,85,0.1);
        cursor: pointer;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .share-icon-btn svg {
        display: block;
        width: 22px;
        height: 22px;
        fill: currentColor;
        transition: transform 0.25s ease;
    }
    .share-icon-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(0,80,85,0.18);
        color: #ffffff;
    }
    .share-icon-btn:hover svg { transform: scale(1.1); }
    .share-icon-btn:active { transform: translateY(0); }
    .share-icon-btn:focus-visible {
        outline: 3px solid var(--news-primary);
        outline-offset: 4px;
    }
    .share-telegram { background: linear-gradient(145deg, #32b5ec, #0088cc); }
    .share-whatsapp { background: linear-gradient(145deg, #25d366, #128c4b); }
    .share-twitter { background: #171c20; }
    .share-copy-link { background: var(--news-primary); position: relative; }
    .share-copy-link svg { fill: none; stroke: currentColor; stroke-width: 1.8; }
    @media (prefers-reduced-motion: reduce) {
        .share-icon-btn, .share-icon-btn svg { transition: none; }
        .share-icon-btn:hover, .share-icon-btn:hover svg { transform: none; }
    }
    .copy-tooltip-alert {
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        background: #222222;
        color: #ffffff;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11.5px;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        font-family: 'Vazirmatn', sans-serif;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .copy-tooltip-alert.active { opacity: 1; }
    .article-toc {
        margin-bottom: 32px;
        padding: 16px 20px;
        border-radius: 12px;
        background: var(--news-bg);
    }
    .article-toc summary {
        cursor: pointer;
        font-size: 14px;
        font-weight: 800;
        color: var(--news-primary);
    }
    .article-toc[open] summary { margin-bottom: 16px; }

    /* ویجت فهرست عناوین خبر (TOC) */
    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .toc-item {
        position: relative;
    }
    .toc-h2 {
        padding-right: 0;
    }
    .toc-h3 {
        padding-right: 14px;
    }
    .toc-link {
        color: var(--news-text);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 600;
        display: block;
        line-height: 1.6;
        padding-right: 10px;
        border-right: 2px solid var(--news-border);
        transition: all 0.2s ease;
    }
    .toc-link:hover {
        color: var(--news-primary);
        border-right-color: var(--news-primary);
        padding-right: 14px;
    }

    /* بخش اخبار مرتبط جدید - خارج از کانتینر اصلی بالای فوتر */
    .related-news-section-outer {
        background-color: #f3f7f8; /* رنگ پس‌زمینه نرم و ملایم */
        border-top: 1px solid var(--news-border);
        border-bottom: 1px solid var(--news-border);
        padding: 56px 0;
        width: 100%;
        direction: var(--site-direction, rtl);
        margin-top: 48px;
    }
    .related-news-section-inner {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 20px;
        text-align: start;
    }
    .related-title {
        font-size: 18px;
        font-weight: 900;
        color: var(--news-text);
        margin: 0 0 28px 0;
        position: relative;
        display: inline-block;
        padding-bottom: 8px;
    }
    .related-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 35px;
        height: 3px;
        background: var(--news-primary);
        border-radius: 99px;
    }
    .related-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }
    @media (max-width: 768px) {
        .related-grid {
            grid-template-columns: 1fr;
        }
    }
    .related-card {
        background: var(--news-card-bg);
        border: 1px solid var(--news-border);
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.015);
        transition: all 0.25s ease;
    }
    .related-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(0,0,0,0.05);
        border-color: var(--news-primary);
    }
    .related-img-box {
        aspect-ratio: 16 / 9;
        overflow: hidden;
        background: #f1f3f5;
        position: relative;
    }
    .related-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .related-card:hover .related-img {
        transform: scale(1.05);
    }
    .related-info {
        padding: 16px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    .related-post-title {
        font-size: 13.5px;
        font-weight: 800;
        line-height: 1.5;
        color: var(--news-text);
        margin: 0 0 8px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.25s ease;
    }
    .related-card:hover .related-post-title {
        color: var(--news-primary);
    }
    .related-meta {
        font-size: 11px;
        color: var(--news-text-muted);
        margin-top: auto;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .related-meta i {
        color: var(--news-accent);
    }

    @media (max-width: 1024px) {
        .news-detail-wrapper {
            padding: 32px 16px 60px;
        }
        .article-body-wrapper {
            padding: 28px 20px;
        }
    }

    @media (max-width: 768px) {
        .article-hero-banner {
            min-height: 320px;
        }
        .article-hero-content {
            padding: 24px 20px 20px;
            gap: 10px;
        }
        .article-hero-meta {
            gap: 12px;
            font-size: 12px;
            padding-top: 10px;
        }
    }
</style>

<div class="news-detail-wrapper">
    <!-- بردکرامب -->
    <nav class="news-detail-breadcrumbs" aria-label="<?= site_e('مسیر راهنما') ?>">
        <a href="/home"><?= site_e('خانه') ?></a> /
        <a href="/news.php"><?= site_e('اخبار') ?></a> /
        <span><?= htmlspecialchars($news['category_name'] ?? 'خبر') ?></span>
    </nav>

    <!-- خبر عریض با عرض متن متناسب برای مطالعه -->
    <div class="news-detail-layout">
        
        <!-- متن خبر -->
        <article class="article-container <?= $has_featured_image ? 'has-hero' : 'no-hero' ?>">
            <?php if ($has_featured_image): ?>
                <header class="article-hero-banner">
                    <img src="<?= htmlspecialchars($featured_path) ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="article-hero-img">
                    <div class="article-hero-gradient"></div>
                    <div class="article-hero-content">
                        <?php if (!empty($news['category_name'])): ?>
                            <span class="article-hero-category"><?= htmlspecialchars($news['category_name']) ?></span>
                        <?php endif; ?>

                        <h1 class="article-hero-title" lang="fa" dir="rtl" data-site-dynamic><?= htmlspecialchars($news['title']) ?></h1>

                        <div class="article-hero-meta">
                            <span><i class="far fa-user"></i> <?= site_e('نویسنده:') ?> <?= htmlspecialchars($news['author'] ?: 'روابط عمومی مکسا') ?></span>
                            <span><i class="far fa-calendar-alt"></i> <?= site_e('تاریخ:') ?> <?= htmlspecialchars($publishDateFa) ?></span>
                            <span><i class="far fa-eye"></i> <?= site_e('بازدید:') ?> <?= htmlspecialchars($totalViewsFa) ?></span>
                            <span><i class="far fa-clock"></i> <?= site_e('زمان مطالعه:') ?> <?= htmlspecialchars($readTimeFa) ?> <?= site_e('دقیقه') ?></span>
                        </div>
                    </div>
                </header>
            <?php endif; ?>

            <div class="article-body-wrapper">
                <?php if (!$has_featured_image): ?>
                    <header class="article-header">
                        <span class="article-category"><?= htmlspecialchars($news['category_name'] ?? 'اخبار') ?></span>
                        <h1 class="article-title" lang="fa" dir="rtl" data-site-dynamic><?= htmlspecialchars($news['title']) ?></h1>

                        <div class="article-meta">
                            <span><i class="far fa-user"></i> <?= site_e('نویسنده:') ?> <?= htmlspecialchars($news['author'] ?: 'روابط عمومی مکسا') ?></span>
                            <span><i class="far fa-calendar-alt"></i> <?= site_e('تاریخ:') ?> <?= htmlspecialchars($publishDateFa) ?></span>
                            <span><i class="far fa-eye"></i> <?= site_e('بازدید:') ?> <?= htmlspecialchars($totalViewsFa) ?></span>
                            <span><i class="far fa-clock"></i> <?= site_e('زمان مطالعه:') ?> <?= htmlspecialchars($readTimeFa) ?> <?= site_e('دقیقه') ?></span>
                        </div>
                    </header>
                <?php endif; ?>

                <details class="article-toc" id="toc-widget" hidden>
                    <summary><?= site_e('عناوین این مطلب') ?></summary>
                    <ul class="toc-list" id="toc-list"></ul>
                </details>

                <section class="article-content" lang="fa" dir="rtl" data-site-dynamic>
                    <?= HtmlSanitizer::sanitize($news['content']) ?>
                </section>

                <!-- تگ‌ها -->
                <?php 
                $custom_tags = !empty($news['keywords']) ? explode(',', $news['keywords']) : [];
                $all_news_tags = array_unique(array_merge($db_tags, $custom_tags));
                $all_news_tags = array_filter(array_map('trim', $all_news_tags));
                if (!empty($all_news_tags)): 
                ?>
                    <footer class="article-tags">
                        <strong><?= site_e('برچسب‌ها:') ?></strong>
                        <?php foreach ($all_news_tags as $tag): ?>
                            <a href="/news.php?q=<?= urlencode($tag) ?>" class="tag-chip"><?= htmlspecialchars($tag) ?></a>
                        <?php endforeach; ?>
                    </footer>
                <?php endif; ?>

                <section class="article-share" aria-labelledby="article-share-title">
                    <h2 class="article-share-title" id="article-share-title"><?= site_e('اشتراک‌گذاری خبر') ?></h2>
                    <div class="share-buttons-row">
                        <?php
                        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                        $shareText = urlencode($news['title']);
                        ?>
                        <a href="https://t.me/share/url?url=<?= urlencode($currentUrl) ?>&text=<?= $shareText ?>" target="_blank" rel="noopener noreferrer" class="share-icon-btn share-telegram" aria-label="<?= site_e('اشتراک‌گذاری در تلگرام') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.78 15.65 9.4 21c.55 0 .79-.24 1.08-.53l2.6-2.48 5.39 3.94c.99.55 1.69.26 1.96-.91l3.55-16.63c.32-1.47-.53-2.05-1.5-1.69L1.64 10.64c-1.43.55-1.41 1.34-.25 1.7l5.36 1.67L19.2 6.22c.59-.39 1.12-.18.68.21Z"/></svg></a>
                        <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener noreferrer" class="share-icon-btn share-whatsapp" aria-label="<?= site_e('اشتراک‌گذاری در واتساپ') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.52 3.48A11.91 11.91 0 0 0 12.05 0C5.47 0 .11 5.35.1 11.94c0 2.1.55 4.15 1.6 5.96L0 24l6.25-1.64a11.94 11.94 0 0 0 5.79 1.47h.01c6.58 0 11.94-5.35 11.95-11.94a11.86 11.86 0 0 0-3.48-8.41Zm-8.47 18.34h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.71.97.99-3.62-.24-.37a9.87 9.87 0 0 1-1.51-5.27c0-5.48 4.46-9.93 9.95-9.93a9.86 9.86 0 0 1 7.03 2.92 9.87 9.87 0 0 1 2.91 7.03c0 5.48-4.46 9.94-9.94 9.94Zm5.45-7.44c-.3-.15-1.77-.87-2.04-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.18.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.8-1.49-1.79-1.67-2.09-.17-.3-.02-.46.13-.61.14-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.49 0 1.47 1.07 2.89 1.22 3.09.15.2 2.1 3.2 5.08 4.49.71.31 1.27.49 1.7.63.71.22 1.36.19 1.87.11.57-.08 1.77-.72 2.02-1.42.25-.7.25-1.29.17-1.42-.07-.12-.27-.2-.57-.35Z"/></svg></a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode($currentUrl) ?>&text=<?= $shareText ?>" target="_blank" rel="noopener noreferrer" class="share-icon-btn share-twitter" aria-label="<?= site_e('اشتراک‌گذاری در ایکس (توییتر)') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.64 7.584H.47l8.6-9.835L0 1.153h7.594l5.243 6.932 6.064-6.932Zm-1.29 19.49h2.039L6.487 3.24H4.3l13.312 17.403Z"/></svg></a>
                        <button type="button" class="share-icon-btn share-copy-link" id="copyLinkBtn" data-url="<?= htmlspecialchars($currentUrl) ?>" aria-label="<?= site_e('کپی لینک خبر') ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="8" y="3" width="12" height="14" rx="2"/><path d="M16 17v3a1 1 0 0 1-1 1H5a2 2 0 0 1-2-2V9a1 1 0 0 1 1-1h4"/></svg>
                            <span class="copy-tooltip-alert" id="copyTooltip" role="status"><?= site_e('لینک کپی شد!') ?></span>
                        </button>
                    </div>
                </section>
            </div>
        </article>
    </div>
</div>

<!-- اخبار مرتبط در خارج از کانتینر اصلی، بالای فوتر با پس‌زمینه نرم -->
<?php if (count($relatedNews) > 0): ?>
    <section class="related-news-section-outer">
        <div class="related-news-section-inner">
            <h3 class="related-title"><?= site_e('اخبار مرتبط') ?></h3>
            <div class="related-grid">
                <?php foreach ($relatedNews as $lat):
                    $newsSlug = slugify($lat['title'] ?? '');
                    $latUrl = '/' . (int)$lat['id'] . '/' . rawurlencode($newsSlug) . '/';
                    $latImage = '';
                    if (!empty($lat['featured_image']) && !empty($lat['news_code'])) {
                        $latImage = "/uploads/news/{$lat['news_code']}/" . $lat['featured_image'];
                    } else {
                        $latImage = buildLocalPlaceholderImageDetail('بدون تصویر');
                    }
                    $latDate = formatJalaliDateTimeFa($lat['publish_date'] ?? null);
                    $latDateParts = explode(' - ', $latDate);
                    $latDateOnly = $latDateParts[0] ?? $latDate;
                ?>
                    <a href="<?= htmlspecialchars($latUrl) ?>" class="related-card">
                        <div class="related-img-box">
                            <img src="<?= htmlspecialchars($latImage) ?>" alt="<?= htmlspecialchars($lat['title']) ?>" class="related-img" loading="lazy">
                        </div>
                        <div class="related-info">
                            <h4 class="related-post-title"><?= htmlspecialchars($lat['title']) ?></h4>
                            <div class="related-meta">
                                <i class="far fa-calendar-alt"></i>
                                <span><?= htmlspecialchars($latDateOnly) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<script>
// اسکریپت کپی لینک با فیدبک تولتیپ
const copyBtn = document.getElementById('copyLinkBtn');
const tooltip = document.getElementById('copyTooltip');

if (copyBtn && tooltip) {
    copyBtn.addEventListener('click', () => {
        const urlToCopy = copyBtn.getAttribute('data-url');
        
        navigator.clipboard.writeText(urlToCopy).then(() => {
            tooltip.classList.add('active');
            
            setTimeout(() => {
                tooltip.classList.remove('active');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });
}

// اسکریپت استخراج عناوین خبر و ساخت فهرست مطالب (TOC)
document.addEventListener('DOMContentLoaded', () => {
    const articleContent = document.querySelector('.article-content');
    const tocList = document.getElementById('toc-list');
    const tocWidget = document.getElementById('toc-widget');
    if (!articleContent || !tocList) return;

    const headings = articleContent.querySelectorAll('h2, h3');
    if (tocWidget) tocWidget.hidden = headings.length === 0;

    headings.forEach((heading, index) => {
        const id = 'heading-' + index;
        heading.id = id;
        
        const li = document.createElement('li');
        li.className = 'toc-item toc-' + heading.tagName.toLowerCase();
        
        const a = document.createElement('a');
        a.href = '#' + id;
        a.className = 'toc-link';
        a.textContent = heading.textContent;
        
        a.addEventListener('click', (e) => {
            e.preventDefault();
            heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.pushState(null, null, '#' + id);
        });

        li.appendChild(a);
        tocList.appendChild(li);
    });

    // مدیریت نرم خطای لود تصاویر مقاله (جلوگیری از نمایش کادر شکسته در صورت نبود عکس فیزیکی روی سرور)
    articleContent.querySelectorAll('img').forEach((img) => {
        img.addEventListener('error', function() {
            const figure = this.closest('figure') || this.closest('.article-img-wrap');
            if (figure) {
                figure.style.display = 'none';
            } else {
                this.style.display = 'none';
            }
        });
    });
});
</script>

<?php
// بارگذاری فوتر سراسری اصلاح‌شده
echo getFooterHTML();
?>
</body>
</html>
