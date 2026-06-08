<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/../config/database.php";

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
   بررسی slug صحیح
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
    // دریافت IP کاربر
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $news_id = $news['id'];
    
    // بررسی آیا این IP در 20 دقیقه گذشته این خبر را دیده است
    $check_stmt = $pdo->prepare("
        SELECT id FROM news_view_logs 
        WHERE news_id = ? AND user_ip = ? 
        AND viewed_at > DATE_SUB(NOW(), INTERVAL 12 HOUR)
        LIMIT 1
    ");
    $check_stmt->execute([$news_id, $user_ip]);
    
    if (!$check_stmt->fetch()) {
        // این IP در 20 دقیقه گذشته این خبر را ندیده است
        
        // ثبت لاگ بازدید
        $log_stmt = $pdo->prepare("
            INSERT INTO news_view_logs (news_id, user_ip) 
            VALUES (?, ?)
        ");
        $log_stmt->execute([$news_id, $user_ip]);
        
        // افزایش شمارنده بازدید در جدول news
        $update_stmt = $pdo->prepare("
            UPDATE news SET viewed = viewed + 1 
            WHERE id = ?
        ");
        $update_stmt->execute([$news_id]);
        
        // به‌روزرسانی مقدار نمایشی
        $total_views++;
    }
    
} catch (Exception $e) {
    // در صورت خطا، فقط بازدید را ثبت نمی‌کنیم و ادامه می‌دهیم
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

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['title']) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #008276; /* سبز مکسا */
            --accent-color: #f9a825;  /* زرد مکسا */
            --bg-color: #f4f7f6;
            --card-bg: #ffffff;
            --text-main: #2c3e50;
            --text-muted: #7f8c8d;
            --border-color: #e0e0e0;
            --transition: all 0.3s ease;
        }

        [data-theme="dark"] {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-main: #e0e0e0;
            --text-muted: #b0b0b0;
            --border-color: #333333;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.8;
            margin: 0;
            padding: 0;
            transition: var(--transition);
        }

        /* دکمه شناور دارک مود */
        .theme-switch {
            position: fixed;
            top: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .theme-switch:hover {
            transform: scale(1.1);
            background-color: var(--accent-color);
        }

        .news-container {
            max-width: 850px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .news-article {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: var(--transition);
        }

        .news-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .news-category {
            background-color: var(--primary-color);
            color: white;
            padding: 5px 18px;
            font-size: 0.85rem;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .news-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .news-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .read-time-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 6px 12px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(0,130,118,0.12), rgba(249,168,37,0.12));
            border: 1px solid rgba(0,130,118,0.2);
            color: var(--primary-color);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .featured-image {
            margin: 30px 0;
            border-radius: 15px;
            overflow: hidden;
        }

        .featured-image img {
            width: 100%;
            height: auto;
            display: block;
        }

        .news-content {
            font-size: 1.1rem;
            text-align: justify;
            word-wrap: break-word;
        }

        .news-content h2 {
            color: var(--primary-color);
            border-right: 5px solid var(--accent-color);
            padding-right: 15px;
            margin-top: 40px;
        }

        .news-content img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 25px auto;
            display: block;
        }

        .news-footer {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid var(--border-color);
        }

        .news-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .news-tags a {
            text-decoration: none;
            background-color: var(--bg-color);
            color: var(--text-muted);
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .news-tags a:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* ریسپانسیو موبایل */
        @media (max-width: 768px) {
            .news-container { margin: 15px auto; }
            .news-article { padding: 25px 20px; border-radius: 0; }
            .news-title { font-size: 1.6rem; }
            .news-content { font-size: 1.05rem; }
            .theme-switch { width: 40px; height: 40px; top: 15px; left: 15px; }
        }
    </style>
</head>
<body>

    <button class="theme-switch" id="themeBtn" aria-label="تغییر حالت شب و روز">
        <span id="themeIcon">🌙</span>
    </button>

    <main class="news-container">
        <article class="news-article">

            <header class="news-header">
                <span class="news-category"><?= htmlspecialchars($news['category_name'] ?? 'اخبار') ?></span>
                <h1 class="news-title"><?= htmlspecialchars($news['title']) ?></h1>

                <div class="news-meta">
                    <span>نویسنده: <?= htmlspecialchars($news['author']) ?></span>
                    <span>|</span>
                    <span>تاریخ: <?= htmlspecialchars($publishDateFa) ?></span>
                    <span>|</span>
                    <span>بازدید: <?= htmlspecialchars($totalViewsFa) ?></span>
                </div>
                <div class="read-time-chip">
                    <span>⏱</span>
                    <span><?= htmlspecialchars($readTimeFa) ?> دقیقه مطالعه</span>
                </div>
            </header>

            <?php if ($featured_path && file_exists($_SERVER['DOCUMENT_ROOT'] . $featured_path)): ?>
            <figure class="featured-image">
                <img src="<?= htmlspecialchars($featured_path) ?>" alt="<?= htmlspecialchars($news['title']) ?>">
            </figure>
            <?php endif; ?>

            <section class="news-content">
                <?= nl2br($news['content']) ?>
            </section>

            <?php if (!empty($news['keywords'])): ?>
            <footer class="news-footer">
                <div class="news-tags">
                    <strong>تگ‌ها:</strong>
                    <?php 
                    $tags = explode(',', $news['keywords']);
                    foreach ($tags as $tag): 
                        $tag = trim($tag);
                        if (!empty($tag)):
                    ?>
                        <a href="#"><?= htmlspecialchars($tag) ?></a>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </footer>
            <?php endif; ?>

        </article>
    </main>

    <script>
        const themeBtn = document.getElementById('themeBtn');
        const themeIcon = document.getElementById('themeIcon');
        const currentTheme = localStorage.getItem('theme') || 'light';

        // تنظیم اولیه
        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeIcon.textContent = '☀️';
        }

        themeBtn.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'dark') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeIcon.textContent = '🌙';
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.textContent = '☀️';
            }
        });
    </script>
</body>
</html>
