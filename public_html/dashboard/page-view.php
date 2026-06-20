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

    // 1-a) مسیر داخلیِ خبر: /{branch}/news/{news-slug}
    if ($sub !== '' && str_starts_with($sub, 'news/')) {
        $newsSlug = substr($sub, strlen('news/'));
        render_branch_news($pdo, $branchId, $newsSlug);
        exit;
    }

    // 1-b) مسیر داخلیِ صفحه‌ی دلخواهِ شعبه: /{branch}/{page-slug}
    if ($sub !== '') {
        render_page_by_slug($pdo, $branchId, $sub);
        exit;
    }

    // 1-c) خانه‌ی شعبه: /{branch}
    render_page_by_slug($pdo, $branchId, 'home');
    exit;
}

/* ---------- 2) صفحه‌ی مرکزی (branch_id = HQ) ---------- */
render_page_by_slug($pdo, $HQ_BRANCH, $slug);
exit;


/* ============================ توابع رندر ============================ */

function render_page_by_slug(PDO $pdo, int $branchId, string $slug): void
{
    $st = $pdo->prepare("SELECT * FROM pages WHERE branch_id = ? AND slug = ? AND status = 'published' LIMIT 1");
    $st->execute([$branchId, $slug]);
    $page = $st->fetch();

    if (!$page) {
        http_response_code(404);
        include $_SERVER['DOCUMENT_ROOT'] . '/404.html';
        exit;
    }

    $components = json_decode((string)$page['components'], true);
    if (!is_array($components)) { $components = []; }

    echo "<!DOCTYPE html>\n<html lang=\"fa\" dir=\"rtl\">\n<head>\n<meta charset=\"utf-8\">\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    echo '<title>' . htmlspecialchars((string)$page['title'], ENT_QUOTES, 'UTF-8') . "</title>\n</head>\n<body>\n";

    foreach ($components as $component) {
        $componentPath = __DIR__ . '/components/' . $component . '/component.php';
        if (is_string($component) && file_exists($componentPath)) {
            $code = file_get_contents($componentPath);
            $code = preg_replace_callback('/{{image(\d+)}}/', static function ($m) use ($component) {
                return '/dashboard/components/' . rawurlencode($component) . '/images/' . $m[1] . '.png';
            }, $code);
            echo $code;
        } else {
            echo '<!-- Component not found -->';
        }
    }

    echo "\n</body>\n</html>";
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
    echo "<!DOCTYPE html>\n<html lang=\"fa\" dir=\"rtl\">\n<head>\n<meta charset=\"utf-8\">\n";
    echo '<title>' . htmlspecialchars((string)$news['title'], ENT_QUOTES, 'UTF-8') . "</title>\n</head>\n<body>\n";
    echo '<article><h1>' . htmlspecialchars((string)$news['title'], ENT_QUOTES, 'UTF-8') . '</h1>';
    echo (string)$news['content'];
    echo "</article>\n</body>\n</html>";
}
