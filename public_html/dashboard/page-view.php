<?php
/* ============================================================================
 *  نمایش صفحه‌ی عمومی (مرکزی یا شعبه) — با ایزولاسیون branch_id
 * ----------------------------------------------------------------------------
 *  ترتیب resolveِ مسیر (طبق تصمیم نهایی پروژه — بدون پیشوند /branch/):
 *    1) اگر {slug} با یک branches.slug بخورد → صفحه‌ی home همان شعبه رندر می‌شود.
 *    2) در غیر این صورت → منطق قبلیِ صفحاتِ مرکزی (branch_id = 1).
 *
 *  مسیرهای داخلیِ شعبه نیز پشتیبانی می‌شوند:
 *    /{branch-slug}/news/{news-slug}   و   /{branch-slug}/{page-slug}
 *  که از طریق پارامتر sub منتقل می‌شوند.
 * ========================================================================== */

require_once __DIR__ . '/../core/dashboard-auth.php';   // فقط برای dash_pdo() و ابزارها
require_once __DIR__ . '/../core/html-sanitizer.php';
$pdo = dash_pdo();

$HQ_BRANCH = 1;

$slug = trim((string)($_GET['slug'] ?? ''));
$sub  = trim((string)($_GET['sub'] ?? ''), '/');   // مسیر داخلیِ شعبه (اختیاری)

if ($slug === '') {
    http_response_code(404);
    include $_SERVER['DOCUMENT_ROOT'] . '/404.html';
    exit;
}

/* ---------- 1) آیا slug یک شعبه است؟ ---------- */
$st = $pdo->prepare("SELECT * FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
$st->execute([$slug]);
$branch = $st->fetch();

if ($branch) {
    $branchId = (int)$branch['id'];
    $branchSlug = (string)$branch['slug'];

    // 1-a) مسیر داخلیِ خبر: /{branch}/news/{news-slug}
    if ($sub !== '' && str_starts_with($sub, 'news/')) {
        $newsSlug = substr($sub, strlen('news/'));
        render_branch_news($pdo, $branchId, $newsSlug);
        exit;
    }

    $branchName = (string)($branch['name'] ?? '');

    // 1-a2) شبکه‌ی همکارانِ شعبه: /{branch}/network → کامپوننتِ همکاران، scope‌شده به همان شعبه
    if ($sub === 'network') {
        render_branch_components($pdo, ['header', 'personal-resume-list', 'footer'], $branchSlug, $branchName, 'شبکه همکاران ' . $branchName);
        exit;
    }

    // 1-b) مسیر داخلیِ صفحه‌ی دلخواهِ شعبه: /{branch}/{page-slug}
    if ($sub !== '') {
        render_page_by_slug($pdo, $branchId, $sub, $branchSlug, $branchName);
        exit;
    }

    // 1-c) خانه‌ی شعبه: /{branch}
    render_page_by_slug($pdo, $branchId, 'home', $branchSlug, $branchName);
    exit;
}

/* ---------- 2) صفحه‌ی مرکزی (branch_id = HQ) ---------- */
render_page_by_slug($pdo, $HQ_BRANCH, $slug, '', '');
exit;


/* ============================ توابع رندر ============================ */

function render_page_by_slug(PDO $pdo, int $branchId, string $slug, string $branchSlug = '', string $branchName = ''): void
{
    // ۱) جستجوی صفحه با branch_id و slug
    $st = $pdo->prepare("SELECT * FROM pages WHERE branch_id = ? AND slug = ? AND status = 'published' LIMIT 1");
    $st->execute([$branchId, $slug]);
    $page = $st->fetch();

    // ۲) اگر صفحه برای این شعبه پیدا نشد و این یک شعبه است، جستجو با slug شعبه (مانند ikhc)
    if (!$page && $branchSlug !== '') {
        $st2 = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published' LIMIT 1");
        $st2->execute([$branchSlug]);
        $page = $st2->fetch();
    }

    // ۳) اگر برای شعبه صفحه‌ای در جدول pages تعریف نشده باشد، از قالب پیش‌فرض شعبه استفاده می‌کنیم
    if (!$page) {
        if ($branchSlug !== '') {
            $branchTitle = $branchName !== '' ? 'شعبه ' . $branchName : 'شعبه مکسا';
            render_branch_components($pdo, ['header', 'branch-home', 'branch-partners', 'footer'], $branchSlug, $branchName, $branchTitle);
            return;
        }

        // ۴) اگر کامپوننتی به همین نام در مسیر کامپوننت‌ها وجود داشته باشد، آن را رندر می‌کنیم
        $directComponent = __DIR__ . '/components/' . $slug . '/component.php';
        if (file_exists($directComponent)) {
            $titles = [
                'headdirectors'       => 'شورای عالی مکسا',
                'directors'           => 'هیئت مدیره مکسا',
                'doctorspage'         => 'کادر درمان و متخصصان مکسا',
                'history'             => 'تاریخچه و نحوه تاسیس',
                'mission-vision'      => 'ماموریت و چشم انداز',
                'association'         => 'اساسنامه',
                'organizationalchart' => 'چارت سازمانی',
            ];
            $autoTitle = $titles[$slug] ?? 'مکسا';
            render_branch_components($pdo, ['topbar', 'header', $slug, 'footer'], '', '', $autoTitle);
            return;
        }

        http_response_code(404);
        $notFoundFile = dirname(__DIR__) . '/404.html';
        if (file_exists($notFoundFile)) {
            include $notFoundFile;
        } elseif (isset($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/404.html')) {
            include $_SERVER['DOCUMENT_ROOT'] . '/404.html';
        } else {
            echo '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><title>صفحه پیدا نشد</title></head><body><h1>۴۰۴ | صفحه مورد نظر یافت نشد</h1></body></html>';
        }
        exit;
    }

    $components = json_decode((string)$page['components'], true);
    if (!is_array($components)) { $components = []; }

    $pageTitle = htmlspecialchars((string)$page['title'], ENT_QUOTES, 'UTF-8');

    $hasHeader = false;
    foreach ($components as $c) {
        if (in_array(trim((string)$c), ['header', 'topbar'], true)) {
            $hasHeader = true;
            break;
        }
    }

    if (!$hasHeader) {
        echo "<!DOCTYPE html>\n<html lang=\"fa\" dir=\"rtl\">\n<head>\n<meta charset=\"utf-8\">\n";
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        echo '<title>' . $pageTitle . "</title>\n";
        echo "<style>*{box-sizing:border-box}html,body{margin:0;padding:0}body{overflow-x:hidden}</style>\n";
        echo "</head>\n<body>\n";
    }

    // شعبه‌ی جاری برای کامپوننت‌ها
    echo '<script>window.__MAXA_BRANCH__=' . json_encode($branchSlug, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
       . ';window.__MAXA_BRANCH_NAME__=' . json_encode($branchName, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ";</script>\n";

    echo_components($components, $pageTitle);

    if (!$hasHeader) {
        echo "\n</body>\n</html>";
    }
}

/**
 * رندرِ یک فهرستِ صریح از کامپوننت‌ها (بدونِ نیاز به ردیفِ pages) — برای مسیرهای
 * ویژه‌ی شعبه یا حالت فال‌بکِ هوشمند
 */
function render_branch_components(PDO $pdo, array $components, string $branchSlug, string $branchName, string $title): void
{
    $pageTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

    $hasHeader = false;
    foreach ($components as $c) {
        if (in_array(trim((string)$c), ['header', 'topbar'], true)) {
            $hasHeader = true;
            break;
        }
    }

    if (!$hasHeader) {
        echo "<!DOCTYPE html>\n<html lang=\"fa\" dir=\"rtl\">\n<head>\n<meta charset=\"utf-8\">\n";
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        echo '<title>' . $pageTitle . "</title>\n";
        echo "<style>*{box-sizing:border-box}html,body{margin:0;padding:0}body{overflow-x:hidden}</style>\n";
        echo "</head>\n<body>\n";
    }

    echo '<script>window.__MAXA_BRANCH__=' . json_encode($branchSlug, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
       . ';window.__MAXA_BRANCH_NAME__=' . json_encode($branchName, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ";</script>\n";

    echo_components($components, $pageTitle);

    if (!$hasHeader) {
        echo "\n</body>\n</html>";
    }
}

/** حلقه‌ی مشترکِ رندر کردنِ کامپوننت‌ها (با اجرای صحیح PHP و جایگزینی {{imageN}}). */
function echo_components(array $components, string $pageTitle = 'مکسا'): void
{
    global $pdo;
    foreach ($components as $component) {
        $cleanComponent = trim((string)$component);
        $componentPath = __DIR__ . '/components/' . $cleanComponent . '/component.php';

        // حل نام‌های چندکلمه‌ای مانند "heroindex ikhc"
        if (!file_exists($componentPath) && strpos($cleanComponent, ' ') !== false) {
            $parts = explode(' ', $cleanComponent);
            if (file_exists(__DIR__ . '/components/' . $parts[1] . $parts[0] . '/component.php')) {
                $cleanComponent = $parts[1] . $parts[0];
                $componentPath = __DIR__ . '/components/' . $cleanComponent . '/component.php';
            } elseif (file_exists(__DIR__ . '/components/' . $parts[0] . '/component.php')) {
                $cleanComponent = $parts[0];
                $componentPath = __DIR__ . '/components/' . $cleanComponent . '/component.php';
            }
        }

        if (file_exists($componentPath)) {
            ob_start();
            include $componentPath;
            $code = ob_get_clean();

            $code = preg_replace_callback('/{{image(\d+)}}/', static function ($m) use ($cleanComponent) {
                return '/dashboard/components/' . rawurlencode($cleanComponent) . '/images/' . $m[1] . '.png';
            }, $code);
            echo $code;
        } else {
            echo '<!-- Component not found: ' . htmlspecialchars($cleanComponent, ENT_QUOTES, 'UTF-8') . ' -->';
        }
    }
}

function render_branch_news(PDO $pdo, int $branchId, string $newsSlug): void
{
    // خبرِ متعلق به همین شعبه (ایزولاسیون). فیلدِ slug در news نیست؛ از news_code/id استفاده می‌کنیم.
    $st = $pdo->prepare("SELECT * FROM news WHERE branch_id = ? AND (news_code = ? OR id = ?) AND status = 'published' LIMIT 1");
    $st->execute([$branchId, $newsSlug, (int)$newsSlug]);
    $news = $st->fetch();

    if (!$news) {
        http_response_code(404);
        include $_SERVER['DOCUMENT_ROOT'] . '/404.html';
        exit;
    }

    $e = static fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $title   = (string)($news['title'] ?? '');
    $content = (string)($news['content'] ?? '');
    $author  = (string)($news['author'] ?? '');
    $pubDate = (string)($news['publish_date'] ?? '');
    $code    = (string)($news['news_code'] ?? '');
    $featured = (string)($news['featured_image'] ?? '');
    $images  = json_decode((string)($news['images'] ?? ''), true) ?: [];
    $video   = (string)($news['video'] ?? '');
    $folder  = '/uploads/news/' . rawurlencode($code) . '/';
    $excerpt = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($content))), 0, 150);

    echo "<!DOCTYPE html>\n<html lang=\"fa\" dir=\"rtl\">\n<head>\n<meta charset=\"utf-8\">\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    echo '<meta name="description" content="' . $e($excerpt) . '">' . "\n";
    echo '<title>' . $e($title) . "</title>\n";
    echo <<<CSS
<style>
@font-face{font-family:'Vazirmatn';src:url('/webfont/Vazirmatn[wght].woff2') format('woff2-variations');font-weight:100 900;font-style:normal;font-display:swap}
*{box-sizing:border-box}html,body{margin:0;padding:0}
body{font-family:'Vazirmatn',Tahoma,sans-serif;background:#f3f7f7;color:#2f3437;line-height:1.9}
.na-hero{position:relative;background:linear-gradient(135deg,#063a3c 0%,#0a5c5b 60%,#063a3c 100%);color:#fff;padding:54px 20px 90px}
.na-wrap{max-width:860px;margin:0 auto;padding:0 20px}
.na-back{display:inline-flex;align-items:center;gap:7px;color:rgba(255,255,255,.85);font-size:13px;font-weight:600;text-decoration:none;margin-bottom:18px}
.na-back:hover{color:#fff}
.na-tag{display:inline-block;background:#f5a623;color:#1a1a1a;font-size:12px;font-weight:700;padding:5px 13px;border-radius:999px;margin-bottom:14px}
.na-hero h1{margin:0;font-size:30px;line-height:1.4;font-weight:800}
.na-meta{margin-top:14px;color:rgba(255,255,255,.8);font-size:13px;display:flex;gap:14px;flex-wrap:wrap}
.na-card{max-width:860px;margin:-60px auto 50px;background:#fff;border:1px solid #e6e8ea;border-radius:20px;
  box-shadow:0 18px 44px -14px rgba(0,102,101,.18);overflow:hidden}
.na-cover{width:100%;aspect-ratio:16/9;object-fit:cover;display:block;background:#eef1f2}
.na-body{padding:30px 30px 36px;font-size:16px}
.na-body img{max-width:100%;height:auto;border-radius:12px;margin:14px 0}
.na-body h2,.na-body h3{color:#063a3c}
.na-gallery{display:flex;gap:10px;flex-wrap:wrap;margin:22px 0}
.na-gallery img{width:150px;height:110px;object-fit:cover;border-radius:10px;cursor:pointer;transition:transform .3s}
.na-gallery img:hover{transform:scale(1.05)}
.na-video{margin-top:20px}.na-video video{width:100%;border-radius:12px}
@media(max-width:600px){.na-hero h1{font-size:23px}.na-body{padding:22px 18px 26px}}
</style>
CSS;
    echo "\n</head>\n<body>\n";

    echo '<section class="na-hero"><div class="na-wrap">';
    echo '<a class="na-back" href="javascript:history.length>1?history.back():location.assign(\'/\')">'
       . '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>'
       . 'بازگشت</a>';
    if (!empty($news['tag_id'])) {
        // برچسبِ خبر (در صورت وجود)
        $tg = $pdo->prepare('SELECT name_fa FROM news_tags WHERE id = ? LIMIT 1');
        $tg->execute([(int)$news['tag_id']]);
        $tagName = (string)($tg->fetchColumn() ?: '');
        if ($tagName !== '') { echo '<span class="na-tag">' . $e($tagName) . '</span>'; }
    }
    echo '<h1>' . $e($title) . '</h1>';
    echo '<div class="na-meta">';
    if ($author !== '')  { echo '<span>نویسنده: ' . $e($author) . '</span>'; }
    if ($pubDate !== '') { echo '<span>تاریخ: ' . $e($pubDate) . '</span>'; }
    echo '</div></div></section>';

    echo '<article class="na-card">';
    if ($featured !== '') {
        echo '<img class="na-cover" src="' . $e($folder . rawurlencode($featured)) . '" alt="' . $e($title) . '">';
    }
    echo '<div class="na-body">' . HtmlSanitizer::sanitize($content);

    if ($images) {
        echo '<div class="na-gallery">';
        foreach ($images as $img) {
            echo '<img src="' . $e($folder . rawurlencode((string)$img)) . '" alt="">';
        }
        echo '</div>';
    }
    if ($video !== '') {
        echo '<div class="na-video"><video controls><source src="' . $e($folder . rawurlencode($video)) . '" type="video/mp4">مرورگر شما ویدیو را پشتیبانی نمی‌کند.</video></div>';
    }
    echo '</div></article>';
    echo "\n</body>\n</html>";
}
