<?php
// فید عمومی اسلایدهای هیرو برای صفحه‌ی اصلی سایت (یا صفحه‌ی شعبه).
// این endpoint نباید پشت گاردِ احراز هویتِ پنل باشد، چون بازدیدکنندگانِ
// عمومی (که وارد پنل نشده‌اند) به آن نیاز دارند. فقط داده‌ی فعال (status=1)
// و فقط-خواندنی برگردانده می‌شود.
//
// ایزولاسیون چندشعبه‌ای: با پارامتر ?branch=<slug> هیروهای همان شعبه برگردانده
// می‌شوند؛ بدون پارامتر یا اگر slug ناشناخته بود، هیروهای «ستاد مرکزی» (HQ).

header('Content-Type: application/json; charset=utf-8');

try {
    // اتصال به دیتابیس از طریق loaderِ امن (نه مسیرِ مطلقِ سرور)
    $DB = require __DIR__ . '/../core/db-config.php';
    $pdo = new PDO(
        "mysql:host={$DB['host']};dbname={$DB['name']};charset={$DB['charset']}",
        $DB['user'], $DB['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    // تعیین شعبه: از روی slug (در صورت وجود) وگرنه HQ
    $slug = isset($_GET['branch']) ? preg_replace('/[^a-z0-9-]/', '', strtolower((string)$_GET['branch'])) : '';
    $branchId = 1; // پیش‌فرض: ستاد مرکزی
    if ($slug !== '') {
        $bs = $pdo->prepare("SELECT id FROM branches WHERE slug = ? AND status = 'active' LIMIT 1");
        $bs->execute([$slug]);
        $row = $bs->fetch();
        if ($row) { $branchId = (int)$row['id']; }
    }

    // هیروهای فعالِ همان شعبه
    $stmt = $pdo->prepare(
        "SELECT title, description, image, button_link
           FROM hero_slides
          WHERE status = 1 AND branch_id = ?
          ORDER BY id DESC LIMIT 3"
    );
    $stmt->execute([$branchId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => 'خطا در دریافت هیروها'], JSON_UNESCAPED_UNICODE);
}
