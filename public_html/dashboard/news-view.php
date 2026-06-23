<?php
// اصلاح نحوه اتصال به دیتابیس برای سازگاری در توسعه محلی و پروداکشن
if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/../config/database.php")) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/core/database.php";
}

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
        LIMIT 4
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
        $socialIcons = '
          <a href="https://instagram.com/macsacharity" target="_blank" rel="noopener" aria-label="اینستاگرام"><i class="fab fa-instagram" style="font-size: 20px; color: white;"></i></a>
          <a href="#" aria-label="ایتا"><i class="fa-solid fa-paper-plane" style="font-size: 18px; color: white;"></i></a>
          <a href="#" aria-label="بله"><i class="fa-solid fa-comment" style="font-size: 18px; color: white;"></i></a>
          <a href="https://wa.me/982191092030" target="_blank" rel="noopener" aria-label="واتساپ"><i class="fab fa-whatsapp" style="font-size: 20px; color: white;"></i></a>
        ';
        $code = preg_replace('/<div class="gf-social">.*?<\/div>/s', '<div class="gf-social">' . $socialIcons . '</div>', $code);
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
        border-radius: 12px;
        margin: 28px auto;
        display: block;
        box-shadow: 0 8px 20px rgba(0,0,0,0.04);
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

    /* ویجت اهدای کمک (نیکوکاری) */
    .sidebar-donate-widget {
        background: linear-gradient(135deg, var(--news-primary) 0%, var(--news-primary-hover) 100%);
        color: #ffffff;
        border-radius: 20px;
        padding: 28px 24px;
        box-shadow: 0 10px 25px rgba(8, 153, 169, 0.2);
        text-align: center;
    }
    .sidebar-donate-widget h4 {
        font-size: 17px;
        font-weight: 900;
        margin: 0 0 12px 0;
        color: #ffffff;
    }
    .sidebar-donate-widget p {
        font-size: 13px;
        line-height: 1.8;
        color: rgba(255,255,255,0.92);
        margin: 0 0 20px 0;
        text-align: justify;
    }
    .sidebar-donate-btn {
        display: block;
        background: var(--news-accent);
        color: #1a1a1a !important;
        text-decoration: none;
        font-weight: 800;
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 13.5px;
        box-shadow: 0 4px 15px rgba(245, 166, 35, 0.35);
        transition: transform 0.25s ease, box-shadow 0.25s ease, background-color 0.2s;
    }
    .sidebar-donate-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 166, 35, 0.5);
        background: #ffc24d;
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
                <?= nl2br($news['content']) ?>
            </section>

            <!-- تگ‌ها -->
            <?php if (!empty($news['keywords'])): ?>
                <footer class="article-tags">
                    <strong>برچسب‌ها:</strong>
                    <?php 
                    $tags = explode(',', $news['keywords']);
                    foreach ($tags as $tag): 
                        $tag = trim($tag);
                        if (!empty($tag)):
                    ?>
                        <a href="/news.php?q=<?= urlencode($tag) ?>" class="tag-chip"><?= htmlspecialchars($tag) ?></a>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
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

            <!-- ویجت جدیدترین اخبار -->
            <?php if (count($latestNews) > 0): ?>
                <div class="sidebar-widget">
                    <h4 class="widget-title">جدیدترین اخبار</h4>
                    <div class="widget-pop-list">
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
                            // استخراج بخش تاریخ بدون ساعت
                            $latDateParts = explode(' - ', $latDate);
                            $latDateOnly = $latDateParts[0] ?? $latDate;
                        ?>
                            <a href="<?= htmlspecialchars($latUrl) ?>" class="widget-pop-item">
                                <div class="widget-pop-thumb">
                                    <img src="<?= htmlspecialchars($latImage) ?>" alt="<?= htmlspecialchars($lat['title']) ?>" loading="lazy">
                                </div>
                                <div class="widget-pop-info">
                                    <h5 class="widget-pop-title"><?= htmlspecialchars($lat['title']) ?></h5>
                                    <span class="widget-pop-date"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($latDateOnly) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ویجت نیکوکاری (فراخوان اهدا) -->
            <div class="sidebar-donate-widget">
                <h4>یاری‌گر بیماران مبتلا به سرطان باشیم</h4>
                <p>مؤسسه نیکوکاری مکسا به عنوان اولین ارائه‌دهنده خدمات حمایتی و مراقبت‌های تسکینی رایگان به بیماران مبتلا به سرطان در ایران، با تکیه بر کمک‌های مردمی فعالیت می‌کند.</p>
                <a href="/publicparticipation.html" class="sidebar-donate-btn">حمایت آنلاین از بیماران</a>
            </div>

        </aside>
    </div>
</div>

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
</script>

<?php
// بارگذاری فوتر سراسری اصلاح‌شده
echo getFooterHTML();
?>
</body>
</html>
