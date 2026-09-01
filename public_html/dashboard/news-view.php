<?php
// اصلاح نحوه اتصال به دیتابیس برای سازگاری در توسعه محلی و پروداکشن
if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/../config/database.php")) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/database.php";
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/core/html-sanitizer.php';

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
if (!empty($news['featured_image']) && !empty($news['news_code'])) {
  $featured_path = "/uploads/news/{$news['news_code']}/" . $news['featured_image'];
}
$publishDateFa = formatJalaliDateTimeFa($news['publish_date'] ?? null);
$totalViewsFa = faNumbers(number_format($total_views));
$readTimeValue = isset($news['read_time']) ? (int)$news['read_time'] : 1;
if ($readTimeValue < 1) { $readTimeValue = 1; }
$readTimeFa = faNumbers($readTimeValue);

// واکشی ۴ خبر آخر (غیر از خبر فعلی)
$latestNews = [];
try {
    $latStmt = $pdo->prepare("
        SELECT n.*, c.name AS category_name
        FROM news n
        LEFT JOIN news_categories c ON c.id = n.category_id
        WHERE n.status = 'published'
          AND n.publish_date <= NOW()
          AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '')
          AND n.id <> ?
        ORDER BY n.publish_date DESC
        LIMIT 3
    ");
    $latStmt->execute([$id]);
    $latestNews = $latStmt->fetchAll(PDO::FETCH_ASSOC);
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
    $footerFile = __DIR__ . '/components/footer/component.php';
    if (file_exists($footerFile)) {
        $code = file_get_contents($footerFile);
        $code = str_replace('{{image1}}', '/dashboard/components/footer/images/1.png', $code);
        return $code;
    }
    return '';
}

// بارگذاری هدر سراسری
$pageTitle = htmlspecialchars($news['title']) . ' — آخرین اخبار مکسا';
require_once __DIR__ . '/components/header/component.php';
?>

<!-- استایل‌های اختصاصی و پریمیوم صفحه نمایش خبر -->
<style>
    @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

    :root {
        --news-primary: #0899A9;      /* سبز برند مکسا */
        --news-accent: #f5a623;       /* نارنجی/خردلی برند مکسا */
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
        max-width: var(--cta-container, 1440px);
        margin: 0 auto;
        padding: 48px 20px 80px;
        direction: rtl;
    }

    /* بردکرامب */
    .news-detail-breadcrumbs {
        font-size: 13.5px;
        color: var(--news-text-muted);
        margin-bottom: 24px;
        text-align: right;
    }
    .news-detail-breadcrumbs a {
        color: var(--news-primary);
        text-decoration: none;
        font-weight: 700;
    }
    .news-detail-breadcrumbs a:hover {
        color: var(--news-accent);
    }

    /* ساختار دو ستونه اصلی جزئیات خبر */
    .news-detail-layout {
        display: grid;
        grid-template-columns: 2.8fr 1.2fr;
        gap: 36px;
    }

    /* ستون راست: بدنه و محتوای خبر */
    .article-container {
        background: var(--news-card-bg);
        border: 1px solid var(--news-border);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.015);
    }
    .article-header {
        text-align: right;
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
    .article-cover-image {
        width: 100%;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 32px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
    }
    .article-cover-image img {
        width: 100%;
        height: auto;
        display: block;
    }
    .article-content {
        font-size: 17px;
        line-height: 2.05;
        color: rgba(47, 52, 55, 0.9);
        text-align: justify;
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
        text-align: right;
    }
    .article-content figure.align-left,
    .article-content .article-img-wrap.align-left,
    .article-content img.align-left {
        float: left !important;
        margin: 8px 24px 20px 0 !important;
        display: block !important;
        clear: left !important;
        text-align: left;
    }
    .article-content figure.align-center,
    .article-content .article-img-wrap.align-center,
    .article-content img.align-center {
        display: block !important;
        margin: 28px auto !important;
        text-align: center !important;
        float: none !important;
        clear: both !important;
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
        text-align: right;
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

    /* سایدبار سمت چپ */
    .news-detail-sidebar {
        display: flex;
        flex-direction: column;
        gap: 28px;
    }

    /* استایل ویجت‌های سایدبار */
    .sidebar-widget {
        background: var(--news-card-bg);
        border: 1px solid var(--news-border);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.01);
    }
    .widget-title {
        font-size: 15px;
        font-weight: 900;
        color: var(--news-text);
        margin: 0 0 20px 0;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--news-border);
        position: relative;
        text-align: right;
    }
    .widget-title::after {
        content: '';
        position: absolute;
        bottom: -2px;
        right: 0;
        width: 45px;
        height: 2px;
        background: var(--news-primary);
    }

    /* ویجت به اشتراک گذاری */
    .share-buttons-row {
        display: flex;
        gap: 12px;
        justify-content: flex-start;
    }
    .share-icon-btn {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 17px;
        text-decoration: none;
        border: 0;
        outline: none;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }
    .share-icon-btn:hover {
        transform: translateY(-3px);
        opacity: 0.9;
        color: #ffffff;
    }
    .share-telegram { background: #0088cc; }
    .share-whatsapp { background: #25d366; }
    .share-twitter { background: #000000; }
    .share-copy-link { background: var(--news-primary); cursor: pointer; position: relative; }
    
    /* تولتیپ کپی لینک */
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
    .copy-tooltip-alert.active {
        opacity: 1;
    }

    /* ویجت جدیدترین اخبار */
    .widget-pop-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .widget-pop-item {
        display: flex;
        gap: 12px;
        align-items: center;
        text-decoration: none;
        min-width: 0;
        text-align: right;
    }
    .widget-pop-thumb {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        overflow: hidden;
        background: #f1f3f5;
        flex-shrink: 0;
    }
    .widget-pop-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .widget-pop-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .widget-pop-title {
        font-size: 13px;
        font-weight: 750;
        line-height: 1.5;
        color: var(--news-text);
        margin: 0 0 4px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.25s ease;
    }
    .widget-pop-item:hover .widget-pop-title {
        color: var(--news-primary);
    }
    .widget-pop-date {
        font-size: 11px;
        color: var(--news-text-muted);
    }

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
        direction: rtl;
        margin-top: 48px;
    }
    .related-news-section-inner {
        max-width: var(--cta-container, 1440px);
        margin: 0 auto;
        padding: 0 20px;
        text-align: right;
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
        .news-detail-layout {
            grid-template-columns: 1fr;
            gap: 28px;
        }
        .news-detail-wrapper {
            padding: 32px 16px 60px;
        }
        .article-container {
            padding: 24px;
        }
    }
</style>

<div class="cta-navbar-spacer"></div>

<div class="news-detail-wrapper">
    <!-- بردکرامب -->
    <nav class="news-detail-breadcrumbs" aria-label="مسیر راهنما">
        <a href="/home">خانه</a> / 
        <a href="/news.php">اخبار</a> / 
        <span><?= htmlspecialchars($news['category_name'] ?? 'خبر') ?></span>
    </nav>

    <!-- ساختار دو ستونه جزئیات -->
    <div class="news-detail-layout">
        
        <!-- ستون راست: متن خبر -->
        <article class="article-container">
            <header class="article-header">
                <span class="article-category"><?= htmlspecialchars($news['category_name'] ?? 'اخبار') ?></span>
                <h1 class="article-title"><?= htmlspecialchars($news['title']) ?></h1>

                <div class="article-meta">
                    <span><i class="far fa-user"></i> نویسنده: <?= htmlspecialchars($news['author'] ?: 'روابط عمومی مکسا') ?></span>
                    <span><i class="far fa-calendar-alt"></i> تاریخ: <?= htmlspecialchars($publishDateFa) ?></span>
                    <span><i class="far fa-eye"></i> بازدید: <?= htmlspecialchars($totalViewsFa) ?></span>
                    <span><i class="far fa-clock"></i> زمان مطالعه: <?= htmlspecialchars($readTimeFa) ?> دقیقه</span>
                </div>
            </header>

            <?php if ($featured_path && file_exists($_SERVER['DOCUMENT_ROOT'] . $featured_path)): ?>
                <figure class="article-cover-image">
                    <img src="<?= htmlspecialchars($featured_path) ?>" alt="<?= htmlspecialchars($news['title']) ?>">
                </figure>
            <?php endif; ?>

            <section class="article-content">
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
                    <strong>برچسب‌ها:</strong>
                    <?php foreach ($all_news_tags as $tag): ?>
                        <a href="/news.php?q=<?= urlencode($tag) ?>" class="tag-chip"><?= htmlspecialchars($tag) ?></a>
                    <?php endforeach; ?>
                </footer>
            <?php endif; ?>
        </article>

        <!-- ستون چپ: سایدبار -->
        <aside class="news-detail-sidebar">
            
            <!-- ویجت اشتراک گذاری -->
            <div class="sidebar-widget">
                <h4 class="widget-title">اشتراک‌گذاری خبر</h4>
                <div class="share-buttons-row">
                    <?php 
                    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $shareText = urlencode($news['title']);
                    ?>
                    <a href="https://t.me/share/url?url=<?= urlencode($currentUrl) ?>&text=<?= $shareText ?>" target="_blank" rel="noopener" class="share-icon-btn share-telegram" aria-label="اشتراک‌گذاری در تلگرام"><i class="fab fa-telegram-plane"></i></a>
                    <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= urlencode($currentUrl) ?>" target="_blank" rel="noopener" class="share-icon-btn share-whatsapp" aria-label="اشتراک‌گذاری در واتساپ"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode($currentUrl) ?>&text=<?= $shareText ?>" target="_blank" rel="noopener" class="share-icon-btn share-twitter" aria-label="اشتراک‌گذاری در توییتر"><i class="fa-brands fa-x-twitter"></i></a>
                    <button type="button" class="share-icon-btn share-copy-link" id="copyLinkBtn" data-url="<?= htmlspecialchars($currentUrl) ?>" aria-label="کپی لینک خبر">
                        <i class="far fa-copy"></i>
                        <span class="copy-tooltip-alert" id="copyTooltip">لینک کپی شد!</span>
                    </button>
                </div>
            </div>

            <!-- ویجت عناوین این مطلب (TOC) -->
            <div class="sidebar-widget" id="toc-widget">
                <h4 class="widget-title">عناوین این مطلب</h4>
                <ul class="toc-list" id="toc-list"></ul>
            </div>

        </aside>
    </div>
</div>

<!-- اخبار مرتبط در خارج از کانتینر اصلی، بالای فوتر با پس‌زمینه نرم -->
<?php if (count($latestNews) > 0): ?>
    <section class="related-news-section-outer">
        <div class="related-news-section-inner">
            <h3 class="related-title">اخبار مرتبط</h3>
            <div class="related-grid">
                <?php foreach ($latestNews as $lat): 
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
    if (headings.length === 0) {
        if (tocWidget) tocWidget.style.display = 'none';
        return;
    }

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
});
</script>

<?php
// بارگذاری فوتر سراسری اصلاح‌شده
echo getFooterHTML();
?>
</body>
</html>
