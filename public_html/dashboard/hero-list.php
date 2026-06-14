<?php
require_once __DIR__ . '/_guard.php';
dash_require('hero');
// فعال‌سازی نمایش خطاها برای دیباگ (بعد از تست نهایی اینها را پاک کنید)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    // 1. اتصال به دیتابیس با مسیر مطلق
    require_once "/home/erfantey/config/database.php";

    // 2. کوئری گرفتن از جدول hero_slides
    $stmt = $pdo->prepare("SELECT title, description, image, button_link FROM hero_slides WHERE status = 1 ORDER BY id DESC LIMIT 3");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. ارسال خروجی
    echo json_encode([
        "status" => "success", 
        "data" => $data
    ]);

} catch (Exception $e) {
    // اگر خطایی رخ داد، خروجی JSON با وضعیت خطا ارسال شود
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
