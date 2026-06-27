<?php
/* ============================================================================
 *  فید عمومیِ کمپین‌ها برای کامپوننت‌های سایت (بازدیدکنندگانِ عمومی).
 *  این endpoint عمومی است (بدونِ گاردِ پنل) و فقط داده‌ی فعالِ فقط-خواندنی می‌دهد.
 *  ایزولاسیون: با ?branch=<slug> کمپین‌های همان شعبه؛ بدون آن، «ستاد مرکزی» (HQ).
 *  هر کمپین نامِ شعبه‌اش را همراه دارد تا روی کارت، تگِ شعبه نمایش داده شود.
 * ========================================================================== */
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

try {
    $DB = require __DIR__ . '/../core/db-config.php';
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']};charset={$DB['charset']}",
        $DB['user'], $DB['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    // تعیین شعبه از روی slug (وگرنه HQ = 1)
    $slug = isset($_GET['branch']) ? preg_replace('/[^a-z0-9-]/', '', strtolower((string)$_GET['branch'])) : '';
    $branchId = 1;
    if ($slug !== '') {
        $bs = $pdo->prepare("SELECT id FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
        $bs->execute([$slug]);
        $row = $bs->fetch();
        if ($row) { $branchId = (int)$row['id']; }
    }

    $stmt = $pdo->prepare(
        "SELECT c.id, c.title, c.description, c.category, c.target_amount, c.collected_amount,
                c.image_url, c.is_active, b.name AS branch_name, b.slug AS branch_slug
           FROM campaigns c LEFT JOIN branches b ON b.id = c.branch_id
          WHERE c.is_active = 1 AND c.collected_amount < c.target_amount AND c.branch_id = ?
          ORDER BY c.id DESC"
    );
    $stmt->execute([$branchId]);
    $campaigns = $stmt->fetchAll();

    if (count($campaigns) > 0) {
        echo json_encode(["status" => "success", "data" => $campaigns], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "empty", "message" => "کمپینی برای نمایش یافت نشد.", "data" => []], JSON_UNESCAPED_UNICODE);
    }

} catch (Throwable $e) {
    echo json_encode(["status" => "error", "message" => "خطای دیتابیس"], JSON_UNESCAPED_UNICODE);
}
