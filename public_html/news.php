<?php
// ۱. اتصال به دیتابیس (اطلاعات از فایل کانفیگ خارج از گیت)
$DB   = require __DIR__ . '/core/db-config.php';
$host = $DB['host'];
$db   = $DB['name'];
$user = $DB['user'];
$pass = $DB['pass'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}

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
        ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
        ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
        (string)$text
    );
}

function formatJalaliDate($datetime) {
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

    return faNumbers(sprintf('%04d/%02d/%02d', $jy, $jm, $jd));
}

function buildLocalPlaceholderImage($text = 'بدون تصویر') {
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

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}

// واکشی دسته‌بندی‌ها به همراه تعداد مقالات منتشر شده هر کدام
$categories = [];
try {
    $catStmt = $pdo->query("
        SELECT c.id, c.name, COUNT(n.id) AS post_count
        FROM news_categories c
        LEFT JOIN news n ON n.category_id = c.id 
            AND n.status = 'published' 
            AND n.publish_date <= NOW()
            AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '')
        GROUP BY c.id, c.name
        ORDER BY c.name ASC
    ");
    $categories = $catStmt->fetchAll();
} catch (PDOException $e) {
    // در صورت وجود خطا در دیتابیس، بی‌صدا ادامه می‌دهیم
}

// واکشی ۴ خبر پربازدید جهت نمایش در سایدبار
$popularNews = [];
try {
    $popStmt = $pdo->query("
        SELECT n.*, c.name AS category_name
        FROM news n
        LEFT JOIN news_categories c ON c.id = n.category_id
        WHERE n.status = 'published'
          AND n.publish_date <= NOW()
          AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '')
        ORDER BY n.viewed DESC, n.publish_date DESC
        LIMIT 4
    ");
    $popularNews = $popStmt->fetchAll();
} catch (PDOException $e) {
    // در صورت وجود خطا در دیتابیس، بی‌صدا ادامه می‌دهیم
}

// فیلترها و مقادیر ارسالی جستجو و دسته‌بندی
$activeCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// ۱. کوئری شمارش کل اخبار منطبق بر فیلترها
$countQueryStr = "
    SELECT COUNT(n.id) AS total
    FROM news n
    LEFT JOIN news_categories c ON c.id = n.category_id
    WHERE n.status = 'published'
      AND n.publish_date <= NOW()
      AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '')
";

$countParams = [];
if ($activeCategory) {
    $countQueryStr .= " AND n.category_id = ?";
    $countParams[] = $activeCategory;
}
if ($searchQuery !== '') {
    $countQueryStr .= " AND (n.title LIKE ? OR n.content LIKE ?)";
    $countParams[] = '%' . $searchQuery . '%';
    $countParams[] = '%' . $searchQuery . '%';
}

try {
    $countStmt = $pdo->prepare($countQueryStr);
    $countStmt->execute($countParams);
    $totalNewsCount = (int)$countStmt->fetchColumn();
} catch (PDOException $e) {
    $totalNewsCount = 0;
}

// ۲. محاسبه تعداد صفحات و پارامترهای صفحه‌بندی
$perPage = 6;
$hasFeatured = (empty($searchQuery) && !$activeCategory);

if ($hasFeatured) {
    if ($totalNewsCount <= 1) {
        $totalPages = 1;
    } else {
        $totalPages = 1 + (int)ceil(($totalNewsCount - 7) / $perPage);
    }
    
    if ($currentPage === 1) {
        $limit = $perPage + 1; // ۷ پست (۱ شاخص + ۶ کارت)
        $offset = 0;
    } else {
        $limit = $perPage; // ۶ پست کارت
        $offset = 1 + ($currentPage - 1) * $perPage;
    }
} else {
    $totalPages = max(1, (int)ceil($totalNewsCount / $perPage));
    $limit = $perPage;
    $offset = ($currentPage - 1) * $perPage;
}

// تصحیح شماره صفحه در صورت بزرگتر بودن از کل صفحات
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
    if (!$hasFeatured) {
        $offset = ($currentPage - 1) * $perPage;
    } else {
        $offset = ($currentPage === 1) ? 0 : (1 + ($currentPage - 1) * $perPage);
    }
}

// ۳. دریافت لیست اخبار برای صفحه جاری
$queryStr = "
    SELECT n.*, c.name AS category_name
    FROM news n
    LEFT JOIN news_categories c ON c.id = n.category_id
    WHERE n.status = 'published'
      AND n.publish_date <= NOW()
      AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '')
";

$queryParams = [];
if ($activeCategory) {
    $queryStr .= " AND n.category_id = ?";
    $queryParams[] = $activeCategory;
}
if ($searchQuery !== '') {
    $queryStr .= " AND (n.title LIKE ? OR n.content LIKE ?)";
    $queryParams[] = '%' . $searchQuery . '%';
    $queryParams[] = '%' . $searchQuery . '%';
}

$queryStr .= " ORDER BY n.publish_date DESC LIMIT $limit OFFSET $offset";

try {
    $stmt = $pdo->prepare($queryStr);
    $stmt->execute($queryParams);
    $allNews = $stmt->fetchAll();
} catch (PDOException $e) {
    die("خطا در بارگذاری اخبار: " . $e->getMessage());
}

// ۴. تفکیک پست ویژه در صفحه اول
$featuredNews = null;
$gridNews = $allNews;

if ($hasFeatured && $currentPage === 1 && count($allNews) > 0) {
    $featuredNews = $allNews[0];
    $gridNews = array_slice($allNews, 1);
}

function getNewsImageUrl($row, $basePath) {
    if (!empty($row['featured_image']) && !empty($row['news_code'])) {
        return $basePath . '/uploads/news/' . rawurlencode($row['news_code']) . '/' . rawurlencode($row['featured_image']);
    } else {
        return buildLocalPlaceholderImage('بدون تصویر');
    }
}

function getFooterHTML() {
    $footerFile = __DIR__ . '/dashboard/components/footer/component.php';
    if (file_exists($footerFile)) {
        $code = file_get_contents($footerFile);
        // جایگزینی تصویر لوگوی فوتر
        $code = str_replace('{{image1}}', '/dashboard/components/footer/images/1.png', $code);
        // اصلاح آیکون‌های شبکه‌های اجتماعی خراب
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
$pageTitle = 'آخرین اخبار و رویدادها — مکسا';
require_once __DIR__ . '/dashboard/components/header/component.php';
?>

<!-- استایل اختصاصی، واکنش‌گرا و پریمیوم صفحه اخبار -->
<style>
    :root {
        --news-primary: #0899A9;      /* سبز کله‌غازی برند مکسا */
        --news-primary-hover: #067d8a;
        --news-accent: #f5a623;       /* خردلی/نارنجی برند مکسا */
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

    /* فاصله‌گذار برای جلوگیری از رفتن محتوا زیر هدر چسبان */
    .cta-navbar-spacer {
        height: var(--cta-nav-h, 78px);
    }

    .news-wrapper {
        max-width: var(--cta-container, 1440px);
        margin: 0 auto;
        padding: 48px 20px 80px;
        direction: rtl;
    }

    /* بخش خوش‌آمدگویی هدر صفحه */
    .news-intro-header {
        text-align: right;
        margin-bottom: 40px;
        position: relative;
    }
    .news-intro-header h1 {
        font-size: clamp(28px, 4vw, 36px);
        font-weight: 900;
        color: var(--news-text);
        margin: 0 0 10px 0;
    }
    .news-intro-header p {
        font-size: 16px;
        color: var(--news-text-muted);
        margin: 0;
    }

    /* کارت خبر ویژه (شاخص) */
    .news-featured-hero {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        background: var(--news-card-bg);
        border: 1px solid var(--news-border);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        margin-bottom: 48px;
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .news-featured-hero:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.07);
        border-color: var(--news-primary);
    }
    .hero-media-wrapper {
        position: relative;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background: #f1f3f5;
    }
    .hero-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .news-featured-hero:hover .hero-img {
        transform: scale(1.04);
    }
    .hero-cat-tag {
        position: absolute;
        top: 20px;
        right: 20px;
        background: var(--news-primary);
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 12.5px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(8, 153, 169, 0.35);
    }
    .hero-info-wrapper {
        padding: 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: right;
    }
    .hero-post-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 13px;
        color: var(--news-text-muted);
        margin-bottom: 16px;
    }
    .hero-post-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .hero-post-meta i {
        color: var(--news-accent);
    }
    .hero-post-title {
        font-size: 32px;
        font-weight: 900;
        line-height: 1.45;
        color: var(--news-text);
        margin: 0 0 16px;
        transition: color 0.25s ease;
    }
    .news-featured-hero:hover .hero-post-title {
        color: var(--news-primary);
    }
    .hero-post-excerpt {
        font-size: 14.5px;
        color: rgba(47, 52, 55, 0.85);
        line-height: 2;
        margin-bottom: 24px;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .hero-post-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--news-border);
        padding-top: 20px;
        margin-top: auto;
    }
    .hero-read-time {
        font-size: 13px;
        color: var(--news-text-muted);
    }
    .hero-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--news-primary);
        font-weight: 800;
        font-size: 14.5px;
        text-decoration: none;
        transition: color 0.25s ease;
    }
    .hero-more-btn i {
        font-size: 12px;
        transition: transform 0.25s ease;
    }
    .hero-more-btn:hover {
        color: var(--news-accent);
    }
    .hero-more-btn:hover i {
        transform: translateX(-4px);
    }

    /* ساختار دو ستونه صفحه اصلی */
    .news-content-layout {
        display: grid;
        grid-template-columns: 2.8fr 1.2fr;
        gap: 36px;
    }

    /* سایدبار سمت چپ */
    .news-sidebar {
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
        font-size: 16px;
        font-weight: 900;
        color: var(--news-text);
        margin: 0 0 20px 0;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--news-border);
        position: relative;
    }
    .widget-title::after {
        content: '';
        position: absolute;
        bottom: -2px;
        right: 0;
        width: 50px;
        height: 2px;
        background: var(--news-primary);
    }

    /* ویجت جستجو */
    .widget-search-form {
        display: flex;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        transition: border-color 0.25s ease;
    }
    .widget-search-form:focus-within {
        border-color: var(--news-primary);
    }
    .widget-search-input {
        width: 100%;
        background: transparent;
        border: 0;
        outline: none;
        padding: 12px 16px;
        font-family: inherit;
        font-size: 13.5px;
        color: var(--news-text);
    }
    .widget-search-btn {
        background: var(--news-primary);
        border: 0;
        color: #ffffff;
        padding: 0 16px;
        cursor: pointer;
        transition: background 0.25s ease;
    }
    .widget-search-btn:hover {
        background: var(--news-primary-hover);
    }

    /* ویجت دسته‌بندی‌ها */
    .widget-cat-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .widget-cat-link {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        background: #f8fafc;
        border-radius: 10px;
        color: var(--news-text);
        text-decoration: none;
        font-size: 13.5px;
        font-weight: 700;
        transition: all 0.25s ease;
    }
    .widget-cat-link:hover {
        background: rgba(8, 153, 169, 0.08);
        color: var(--news-primary);
        padding-right: 18px;
    }
    .widget-cat-link.active {
        background: var(--news-primary);
        color: #ffffff;
    }
    .widget-cat-count {
        background: rgba(0,0,0,0.05);
        color: var(--news-text-muted);
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 99px;
    }
    .widget-cat-link.active .widget-cat-count {
        background: rgba(255,255,255,0.22);
        color: #ffffff;
    }

    /* ویجت پربازدیدترین‌ها */
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
    }
    .widget-pop-thumb {
        width: 64px;
        height: 64px;
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
        font-size: 13.5px;
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

    /* گرید اصلی اخبار کارت‌ها */
    .news-grid-column {
        display: flex;
        flex-direction: column;
        gap: 28px;
    }
    .news-grid-cards {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 28px;
    }

    /* استایل کارت‌های لیست خبر */
    .news-article-card {
        display: flex;
        flex-direction: column;
        background: var(--news-card-bg);
        border: 1px solid var(--news-border);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.015);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
    }
    .news-article-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.07);
        border-color: var(--news-primary);
    }
    .card-media-wrapper {
        position: relative;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        background: #f1f3f5;
    }
    .card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .news-article-card:hover .card-img {
        transform: scale(1.05);
    }
    .card-cat-tag {
        position: absolute;
        top: 16px;
        right: 16px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        color: var(--news-primary);
        padding: 4px 12px;
        border-radius: 99px;
        font-size: 11.5px;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    .card-body-wrapper {
        padding: 24px;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        text-align: right;
    }
    .card-post-meta {
        display: flex;
        gap: 14px;
        font-size: 12px;
        color: var(--news-text-muted);
        margin-bottom: 12px;
    }
    .card-post-meta span {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .card-post-meta i {
        color: var(--news-accent);
    }
    .card-post-title {
        font-size: 17.5px;
        font-weight: 800;
        line-height: 1.5;
        color: var(--news-text);
        margin: 0 0 12px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.25s ease;
    }
    .news-article-card:hover .card-post-title {
        color: var(--news-primary);
    }
    .card-post-excerpt {
        font-size: 13.5px;
        color: rgba(47, 52, 55, 0.8);
        line-height: 1.85;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .card-post-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--news-border);
        padding-top: 16px;
        margin-top: auto;
        font-size: 12px;
    }
    .card-read-time {
        color: var(--news-text-muted);
    }
    .card-more-btn {
        color: var(--news-primary);
        font-weight: 750;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: color 0.25s ease;
    }
    .card-more-btn i {
        font-size: 10px;
        color: var(--news-accent);
        transition: transform 0.25s ease;
    }
    .card-more-btn:hover {
        color: var(--news-accent);
    }
    .card-more-btn:hover i {
        transform: translateX(-3px);
    }

    /* حالت عدم وجود خبر */
    .news-empty-state {
        text-align: center;
        padding: 60px 24px;
        background: var(--news-card-bg);
        border: 1px dashed var(--news-border);
        border-radius: 20px;
        color: var(--news-text-muted);
        font-size: 15px;
        grid-column: 1 / -1;
    }

    /* انیمیشن و فید اسکلت لودینگ */
    .skeleton-grid-layout {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 28px;
    }
    .skeleton-loader-card {
        border: 1px solid var(--news-border);
        border-radius: 20px;
        background: var(--news-card-bg);
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.01);
    }
    .skeleton-loader-image,
    .skeleton-loader-line {
        background: linear-gradient(90deg, #eceff1 25%, #f5f7f8 37%, #eceff1 63%);
        background-size: 400% 100%;
        animation: skelPulse 1.4s ease-in-out infinite;
    }
    .skeleton-loader-image { aspect-ratio: 16 / 9; }
    .skeleton-loader-body { padding: 24px; text-align: right; }
    .skeleton-loader-line { height: 12px; border-radius: 8px; margin-bottom: 12px; }
    .skeleton-loader-line.w-80 { width: 80%; }
    .skeleton-loader-line.w-60 { width: 60%; }
    .skeleton-loader-line.w-40 { width: 40%; }
    .skeleton-loader-line.w-30 { width: 30%; margin-bottom: 0; }

    .news-grid-wrapper-container { transition: opacity 0.3s ease; }
    .news-grid-hidden { opacity: 0; height: 0; overflow: hidden; }
    .skeleton-hidden { display: none !important; }

    @keyframes skelPulse {
        0% { background-position: 100% 50%; }
        100% { background-position: 0 50%; }
    }

    /* واکنش‌گرایی صفحات */
    @media (max-width: 1100px) {
        .news-content-layout {
            grid-template-columns: 1fr;
            gap: 28px;
        }
        .news-featured-hero {
            grid-template-columns: 1fr;
        }
        .hero-media-wrapper {
            aspect-ratio: 16 / 9;
        }
        .hero-info-wrapper {
            padding: 30px;
        }
    }
    @media (max-width: 640px) {
        .news-grid-cards, .skeleton-grid-layout {
            grid-template-columns: 1fr;
        }
        .news-wrapper {
            padding: 32px 16px 60px;
        }
        .hero-post-title {
            font-size: 24px;
        }
        .hero-info-wrapper {
            padding: 20px;
        }
        .card-body-wrapper {
            padding: 16px;
        }
    }

    /* استایل صفحه‌بندی (Pagination) */
    .news-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 12px;
        margin-top: 48px;
        direction: rtl;
    }
    .pagination-numbers {
        display: flex;
        gap: 6px;
    }
    .pagination-number {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--news-card-bg);
        border: 1px solid var(--news-border);
        color: var(--news-text);
        font-weight: 700;
        font-size: 14.5px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .pagination-number:hover {
        border-color: var(--news-primary);
        color: var(--news-primary);
        background: rgba(8, 153, 169, 0.04);
    }
    .pagination-number.active {
        background: var(--news-primary);
        border-color: var(--news-primary);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(8, 153, 169, 0.25);
    }
    .pagination-arrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 40px;
        padding: 0 16px;
        background: var(--news-card-bg);
        border: 1px solid var(--news-border);
        color: var(--news-text);
        font-weight: 700;
        font-size: 13.5px;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .pagination-arrow:hover {
        border-color: var(--news-primary);
        color: var(--news-primary);
        background: rgba(8, 153, 169, 0.04);
    }
    .pagination-prev i {
        font-size: 10px;
    }
    .pagination-next i {
        font-size: 10px;
    }
</style>

<div class="cta-navbar-spacer"></div>

<main class="news-wrapper">
    <!-- بخش خوش‌آمدگویی -->
    <div class="news-intro-header">
        <h1>آخرین اخبار و رویدادهای مکسا</h1>
        <p>با آخرین گزارش‌ها، دستاوردها و داستان‌های امیدبخش مرکز مکسا همراه باشید.</p>
    </div>

    <!-- بخش فیلترهای فعال (در صورت اعمال) -->
    <?php if ($searchQuery !== '' || $activeCategory): ?>
        <div style="margin-bottom: 24px; font-size: 14.5px; color: var(--news-text-muted); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; background: #fff; padding: 12px 20px; border-radius: 12px; border: 1px solid var(--news-border);">
            <div>
                نتایج جستجو 
                <?php if ($searchQuery !== ''): ?> برای «<strong><?= htmlspecialchars($searchQuery) ?></strong>»<?php endif; ?>
                <?php if ($activeCategory): ?>
                    <?php 
                    $activeCatName = '';
                    foreach ($categories as $cat) {
                        if ((int)$cat['id'] === $activeCategory) {
                            $activeCatName = $cat['name'];
                            break;
                        }
                    }
                    ?>
                    در دسته «<strong><?= htmlspecialchars($activeCatName) ?></strong>»
                <?php endif; ?>
                — <?= faNumbers(count($allNews)) ?> مورد یافت شد.
            </div>
            <a href="news.php" style="color: var(--news-primary); font-weight: 700; text-decoration: none; font-size: 13.5px;"><i class="fa-solid fa-rotate-left"></i> حذف فیلترها</a>
        </div>
    <?php endif; ?>

    <!-- بخش خبر ویژه (شاخص) در صورتی که فیلتری اعمال نشده باشد -->
    <?php if ($featuredNews): 
        $excerpt = !empty($featuredNews['subtitle']) ? $featuredNews['subtitle'] : (mb_substr(strip_tags($featuredNews['content']), 0, 200) . '...');
        $imageUrl = getNewsImageUrl($featuredNews, $basePath);
        $author = !empty($featuredNews['author']) ? $featuredNews['author'] : 'روابط عمومی مکسا';
        $categoryName = !empty($featuredNews['category_name']) ? $featuredNews['category_name'] : 'خبر';
        $newsSlug = slugify($featuredNews['title'] ?? '');
        $newsUrl = '/' . (int)$featuredNews['id'] . '/' . rawurlencode($newsSlug) . '/';
        $publishDateJalali = formatJalaliDate($featuredNews['publish_date'] ?? '');
        $readTimeFa = faNumbers(!empty($featuredNews['read_time']) ? $featuredNews['read_time'] : 1);
    ?>
        <article class="news-featured-hero">
            <div class="hero-media-wrapper">
                <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($featuredNews['title']) ?>" class="hero-img" loading="lazy" />
                <span class="hero-cat-tag"><?= htmlspecialchars($categoryName) ?></span>
            </div>
            <div class="hero-info-wrapper">
                <div class="hero-post-meta">
                    <span><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($publishDateJalali) ?></span>
                    <span><i class="far fa-user"></i> <?= htmlspecialchars($author) ?></span>
                </div>
                <h2 class="hero-post-title"><?= htmlspecialchars($featuredNews['title']) ?></h2>
                <p class="hero-post-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                
                <div class="hero-post-footer">
                    <span class="hero-read-time">⏱ <?= $readTimeFa ?> دقیقه مطالعه</span>
                    <a href="<?= htmlspecialchars($newsUrl) ?>" class="hero-more-btn">
                        ادامه مطلب
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </div>
            </div>
        </article>
    <?php endif; ?>

    <!-- ساختار محتوایی اصلی (دو ستونه) -->
    <div class="news-content-layout">
        
        <!-- ستون سمت راست: لیست خبرها -->
        <div class="news-grid-column">
            
            <!-- اسکلت لودینگ پیش‌فرض -->
            <div id="news-skeleton" class="skeleton-grid-layout">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <article class="skeleton-loader-card">
                        <div class="skeleton-loader-image"></div>
                        <div class="skeleton-loader-body">
                            <div class="skeleton-loader-line w-40"></div>
                            <div class="skeleton-loader-line"></div>
                            <div class="skeleton-loader-line w-80"></div>
                            <div class="skeleton-loader-line w-60"></div>
                            <div class="skeleton-loader-line w-30"></div>
                        </div>
                    </article>
                <?php endfor; ?>
            </div>

            <!-- گرید اصلی کارت‌ها -->
            <div id="news-grid" class="news-grid-wrapper-container news-grid-hidden">
                <?php if (count($gridNews) > 0): ?>
                    <div class="news-grid-cards">
                        <?php foreach ($gridNews as $row): 
                            $excerpt = !empty($row['subtitle']) ? $row['subtitle'] : (mb_substr(strip_tags($row['content']), 0, 110) . '...');
                            $imageUrl = getNewsImageUrl($row, $basePath);
                            $author = !empty($row['author']) ? $row['author'] : 'روابط عمومی مکسا';
                            $categoryName = !empty($row['category_name']) ? $row['category_name'] : 'خبر';
                            $newsSlug = slugify($row['title'] ?? '');
                            $newsUrl = '/' . (int)$row['id'] . '/' . rawurlencode($newsSlug) . '/';
                            $publishDateJalali = formatJalaliDate($row['publish_date'] ?? '');
                            $readTimeFa = faNumbers(!empty($row['read_time']) ? $row['read_time'] : 1);
                        ?>
                            <article class="news-article-card">
                                <div class="card-media-wrapper">
                                    <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="card-img" loading="lazy" />
                                    <span class="card-cat-tag"><?= htmlspecialchars($categoryName) ?></span>
                                </div>
                                <div class="card-body-wrapper">
                                    <div class="card-post-meta">
                                        <span><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($publishDateJalali) ?></span>
                                        <span><i class="far fa-user"></i> <?= htmlspecialchars($author) ?></span>
                                    </div>
                                    <h3 class="card-post-title"><?= htmlspecialchars($row['title']) ?></h3>
                                    <p class="card-post-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                                    
                                    <div class="card-post-footer">
                                        <span class="card-read-time">⏱ <?= $readTimeFa ?> دقیقه مطالعه</span>
                                        <a href="<?= htmlspecialchars($newsUrl) ?>" class="card-more-btn">
                                            مشاهده خبر
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="news-empty-state">
                        <div style="font-size: 32px; margin-bottom: 12px;">🔍</div>
                        موردی در این دسته‌بندی یا جستجو پیدا نشد.
                    </div>
                <?php endif; ?>
            </div>

            <!-- بخش صفحه‌بندی (Pagination) -->
            <?php if ($totalPages > 1): ?>
                <div class="news-pagination">
                    <!-- دکمه قبلی -->
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?><?= ($activeCategory) ? '&category=' . $activeCategory : '' ?><?= ($searchQuery !== '') ? '&q=' . urlencode($searchQuery) : '' ?>" class="pagination-arrow pagination-prev">
                            <i class="fa-solid fa-chevron-right"></i> قبلی
                        </a>
                    <?php endif; ?>

                    <div class="pagination-numbers">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $currentPage): ?>
                                <span class="pagination-number active"><?= faNumbers($i) ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= ($activeCategory) ? '&category=' . $activeCategory : '' ?><?= ($searchQuery !== '') ? '&q=' . urlencode($searchQuery) : '' ?>" class="pagination-number"><?= faNumbers($i) ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>

                    <!-- دکمه بعدی -->
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?><?= ($activeCategory) ? '&category=' . $activeCategory : '' ?><?= ($searchQuery !== '') ? '&q=' . urlencode($searchQuery) : '' ?>" class="pagination-arrow pagination-next">
                            بعدی <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- ستون سمت چپ: سایدبار -->
        <aside class="news-sidebar">
            
            <!-- ویجت جستجو -->
            <div class="sidebar-widget">
                <h4 class="widget-title">جستجو در اخبار</h4>
                <form class="widget-search-form" method="get" action="news.php">
                    <?php if ($activeCategory): ?>
                        <input type="hidden" name="category" value="<?= $activeCategory ?>">
                    <?php endif; ?>
                    <input type="text" name="q" class="widget-search-input" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="کلمه کلیدی مورد نظر..." required>
                    <button type="submit" class="widget-search-btn" aria-label="جستجو"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>

            <!-- ویجت دسته‌بندی‌ها -->
            <?php if (count($categories) > 0): ?>
                <div class="sidebar-widget">
                    <h4 class="widget-title">دسته‌بندی‌های خبری</h4>
                    <ul class="widget-cat-list">
                        <li>
                            <a href="news.php<?= ($searchQuery !== '') ? '?q=' . urlencode($searchQuery) : '' ?>" class="widget-cat-link <?= !$activeCategory ? 'active' : '' ?>">
                                <span>همه اخبار</span>
                                <span class="widget-cat-count"><?= faNumbers(array_sum(array_column($categories, 'post_count'))) ?></span>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="news.php?category=<?= (int)$cat['id'] ?><?= ($searchQuery !== '') ? '&q=' . urlencode($searchQuery) : '' ?>" class="widget-cat-link <?= ($activeCategory === (int)$cat['id']) ? 'active' : '' ?>">
                                    <span><?= htmlspecialchars($cat['name']) ?></span>
                                    <span class="widget-cat-count"><?= faNumbers($cat['post_count']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ویجت پربازدیدترین‌ها -->
            <?php if (count($popularNews) > 0): ?>
                <div class="sidebar-widget">
                    <h4 class="widget-title">محبوب‌ترین اخبار</h4>
                    <div class="widget-pop-list">
                        <?php foreach ($popularNews as $pop): 
                            $popUrl = 'news.php?category=' . (int)$pop['category_id'];
                            $newsSlug = slugify($pop['title'] ?? '');
                            $newsUrl = '/' . (int)$pop['id'] . '/' . rawurlencode($newsSlug) . '/';
                            $popImage = getNewsImageUrl($pop, $basePath);
                            $popDate = formatJalaliDate($pop['publish_date'] ?? '');
                        ?>
                            <a href="<?= htmlspecialchars($newsUrl) ?>" class="widget-pop-item">
                                <div class="widget-pop-thumb">
                                    <img src="<?= htmlspecialchars($popImage) ?>" alt="<?= htmlspecialchars($pop['title']) ?>" loading="lazy">
                                </div>
                                <div class="widget-pop-info">
                                    <h5 class="widget-pop-title"><?= htmlspecialchars($pop['title']) ?></h5>
                                    <span class="widget-pop-date"><i class="far fa-calendar-alt"></i> <?= htmlspecialchars($popDate) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </aside>

    </div>
</main>

<script>
// افکت محو شدن اسکلت لودینگ و ظاهر شدن کارت‌ها با زمان معقول
const skeletonStartAt = Date.now();
const minSkeletonMs = 450;

window.addEventListener('load', () => {
    const skeleton = document.getElementById('news-skeleton');
    const newsGrid = document.getElementById('news-grid');
    const elapsed = Date.now() - skeletonStartAt;
    const wait = Math.max(0, minSkeletonMs - elapsed);

    setTimeout(() => {
        if (newsGrid) newsGrid.classList.remove('news-grid-hidden');
        if (skeleton) skeleton.classList.add('skeleton-hidden');
    }, wait);
});
</script>

<?php
// بارگذاری فوتر سراسری اصلاح‌شده
echo getFooterHTML();
?>
</body>
</html>
