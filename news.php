<?php
// ۱. اتصال به دیتابیس
$host = 'localhost';
$db   = 'erfantey_macsacharity';
$user = 'erfantey_fantasticfour';
$pass = 'Diamond19971376@macsa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // خواندن اخبار قابل نمایش (منتشرشده + زمان انتشار رسیده + بدون ریجکت)
    $stmt = $pdo->query("
        SELECT n.*, c.name AS category_name
        FROM news n
        LEFT JOIN news_categories c ON c.id = n.category_id
        WHERE n.status = 'published'
          AND n.publish_date <= NOW()
          AND (n.reject_reason IS NULL OR TRIM(n.reject_reason) = '')
        ORDER BY n.publish_date DESC
    ");
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
        $text
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
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>آخرین اخبار</title>
    <!-- استایل محلی (جایگزین Tailwind CDN) -->
    <link rel="stylesheet" href="/tailwind-news.css">
    <!-- لود کردن FontAwesome برای آیکون‌ها -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* تنظیم فونت فارسی دلخواه (مثلا وزیرمتن) */
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700;900&display=swap');
        body { font-family: 'Vazirmatn', sans-serif; }

        .skeleton-grid {
            display: grid;
            gap: 2rem;
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        @media (min-width: 640px) {
            .skeleton-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (min-width: 1024px) {
            .skeleton-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }

        .skeleton-card {
            border: 1px solid #e6e8ea;
            border-radius: 1rem;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .skeleton-image,
        .skeleton-line {
            background: linear-gradient(90deg, #eceff1 25%, #f5f7f8 37%, #eceff1 63%);
            background-size: 400% 100%;
            animation: skelPulse 1.3s ease-in-out infinite;
        }

        .skeleton-image { aspect-ratio: 16 / 9; }
        .skeleton-body { padding: 1.5rem; }
        .skeleton-line { height: 12px; border-radius: 8px; margin-bottom: 10px; }
        .skeleton-line.w80 { width: 80%; }
        .skeleton-line.w60 { width: 60%; }
        .skeleton-line.w40 { width: 40%; margin-bottom: 0; }

        .news-grid-wrap { transition: opacity .3s ease; }
        .news-grid-hidden { opacity: 0; }
        .skeleton-hidden { display: none; }

        @keyframes skelPulse {
            0% { background-position: 100% 50%; }
            100% { background-position: 0 50%; }
        }
    </style>
</head>
<body class="bg-gray-50">

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    
    <!-- بخش هدر -->
    <div class="mb-12 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
        <div class="text-right">
            <h2 class="mb-2 text-3xl font-extrabold tracking-tight text-[#2f3437] sm:text-4xl">آخرین اخبار</h2>
            <p class="text-lg text-[#9d9d9d]">با آخرین داستان‌های سراسر جهان همراه باشید.</p>
        </div>
    </div>

    <div id="news-skeleton" class="skeleton-grid">
        <?php for ($i = 0; $i < 6; $i++): ?>
            <article class="skeleton-card">
                <div class="skeleton-image"></div>
                <div class="skeleton-body">
                    <div class="skeleton-line w40"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line w80"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line w60"></div>
                </div>
            </article>
        <?php endfor; ?>
    </div>

    <!-- گرید اخبار -->
    <div id="news-grid" class="news-grid-wrap news-grid-hidden grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <?php 
        // ۲. حلقه برای نمایش تک‌تک اخبار دیتابیس
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
            // ساخت خلاصه متن (۱۲۰ حرف اول بدون تگ‌های HTML)
            $excerpt = mb_substr(strip_tags($row['content']), 0, 120) . '...';
            // بررسی وجود عکس
            if (!empty($row['featured_image']) && !empty($row['news_code'])) {
                $imageUrl = $basePath . '/uploads/news/' . rawurlencode($row['news_code']) . '/' . rawurlencode($row['featured_image']);
            } else {
                $imageUrl = buildLocalPlaceholderImage('بدون تصویر');
            }
            $author = !empty($row['author']) ? $row['author'] : 'ناشناس';
            $categoryName = !empty($row['category_name']) ? $row['category_name'] : 'خبر';
            $newsSlug = slugify($row['title'] ?? '');
            $newsUrl = '/' . (int)$row['id'] . '/' . rawurlencode($newsSlug) . '/';
            $publishDateJalali = formatJalaliDate($row['publish_date'] ?? '');
        ?>
        
        <article class="group relative flex flex-col overflow-hidden rounded-2xl bg-[#ffffff] shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl border border-[#e6e8ea]">
            
            <!-- عکس خبر -->
            <div class="relative aspect-video overflow-hidden bg-gray-100">
                <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($row['title']) ?>" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                <div class="absolute top-4 right-4">
                    <span class="rounded-full bg-[#ffffff]/90 backdrop-blur-sm px-3 py-1 text-xs font-semibold text-[#007b7a] shadow-sm">
                        <?= htmlspecialchars($categoryName) ?>
                    </span>
                </div>
            </div>

            <!-- محتوای کارت -->
            <div class="flex flex-1 flex-col p-6 text-right">
                
                <!-- تاریخ و نویسنده -->
                <div class="mb-3 flex items-center justify-start gap-4 text-xs text-[#9d9d9d]">
                    <div class="flex items-center gap-1">
                        <i class="far fa-calendar-alt"></i>
                        <span><?= htmlspecialchars($publishDateJalali) ?></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <i class="far fa-user"></i>
                        <span><?= htmlspecialchars($author) ?></span>
                    </div>
                </div>

                <!-- تیتر -->
                <h3 class="mb-3 text-xl font-bold text-[#2f3437] group-hover:text-[#007b7a] transition-colors line-clamp-2">
                    <?= htmlspecialchars($row['title']) ?>
                </h3>

                <!-- خلاصه خبر -->
                <p class="mb-6 line-clamp-3 text-sm leading-relaxed text-[#2f3437]/80">
                    <?= htmlspecialchars($excerpt) ?>
                </p>

                <!-- فوتر کارت (دکمه ادامه مطلب) -->
                <div class="mt-auto flex items-center justify-between border-t border-[#e6e8ea] pt-4">
                    <span class="text-xs font-medium text-[#9d9d9d]"><?= htmlspecialchars($row['read_time']) ?> دقیقه مطالعه</span>
                    <!-- لینک به صفحه جزئیات خبر -->
                    <a href="<?= htmlspecialchars($newsUrl) ?>" class="flex items-center gap-1 text-sm font-semibold text-[#007b7a] group/btn">
                        ادامه مطلب
                        <i class="fas fa-chevron-left text-[#f4a61e] text-xs transition-transform group-hover/btn:-translate-x-1"></i>
                    </a>
                </div>
            </div>
            
        </article>
        
        <?php endwhile; ?>
    </div>
    
</section>

<script>
const skeletonStartAt = Date.now();
const minSkeletonMs = 420;

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

</body>
</html>
