<?php
/* ============================================================================
 *  فید عمومیِ «دوره‌ها» برای صفحه‌ی شعبه (یا ستاد) — خروجی JSON
 * ----------------------------------------------------------------------------
 *  این endpoint نباید پشت گاردِ احراز هویتِ پنل باشد؛ بازدیدکنندگانِ عمومی به آن
 *  نیاز دارند. فقط دوره‌های منتشرشده‌ی همان شعبه را برمی‌گرداند.
 *
 *  ایزولاسیون چندشعبه‌ای: با ?branch=<slug> فقط دوره‌های همان شعبه؛ بدون پارامتر یا
 *  slug ناشناخته → دوره‌های «ستاد مرکزی» (HQ، branch_id = 1). همان الگوی hero-list.php
 *  و courses.php. منطقِ scope در load_courses() (course-db.php) از قبل وجود دارد و
 *  این فایل فقط آن را به‌صورت فید در اختیار کامپوننتِ صفحه‌ی شعبه می‌گذارد.
 * ========================================================================== */
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/course-db.php';   // $pdo، $coursesSchemaReady، load_courses()

$limit = isset($_GET['limit']) ? max(1, min(12, (int)$_GET['limit'])) : 4;

// تعیین شعبه از روی slug (در صورت وجود) وگرنه HQ
$branchSlug = isset($_GET['branch']) ? preg_replace('/[^a-z0-9-]/', '', strtolower((string)$_GET['branch'])) : '';
$branchId   = 1;
if ($pdo && $branchSlug !== '') {
    try {
        $bs = $pdo->prepare("SELECT id FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
        $bs->execute([$branchSlug]);
        $brow = $bs->fetch(PDO::FETCH_ASSOC);
        if ($brow) { $branchId = (int)$brow['id']; }
    } catch (Throwable $e) { /* بی‌اثر؛ HQ می‌ماند */ }
}

// فقط دوره‌های منتشرشده‌ی همان شعبه (scope سمت سرور)
$rows = load_courses($pdo, $coursesSchemaReady, 'published', $branchId);
$rows = array_slice($rows, 0, $limit);

$items = array_map(static function ($c) {
    $price = (float)($c['price'] ?? 0);
    $disc  = ($c['discount_price'] ?? null) ? (float)$c['discount_price'] : 0;
    $isFree = !empty($c['is_free']) || $price === 0.0;
    return [
        'id'          => (int)($c['id'] ?? 0),
        'title'       => (string)($c['title'] ?? 'بدون عنوان'),
        'category'    => (string)($c['category'] ?? 'عمومی'),
        'instructor'  => (string)($c['instructor'] ?? 'مدرس مکسا'),
        'image'       => (string)($c['thumbnail'] ?? ''),
        'lessons'     => (int)($c['lessons'] ?? 0),
        'is_free'     => $isFree,
        'price'       => $price,
        'discount'    => ($disc > 0 && $disc < $price) ? $disc : 0,
        'branch_name' => (string)($c['branch_name'] ?? ''),
        'url'         => '/courses/' . (int)($c['id'] ?? 0),
    ];
}, $rows);

echo json_encode(['status' => 'ok', 'items' => array_values($items)], JSON_UNESCAPED_UNICODE);
