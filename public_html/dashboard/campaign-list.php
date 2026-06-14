<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0); // جلوگیری از خراب شدن خروجی JSON

$DB = require __DIR__ . '/../core/db-config.php';
$host = $DB['host'];
$dbname = $DB['name'];
$user = $DB['user'];
$pass = $DB['pass'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // واکشی ستون‌ها بر اساس ساختار جدول campaigns شما
    // کمپین‌هایی که به هدف خود رسیده‌اند (تکمیل‌شده) دیگر برای کاربران نمایش داده نمی‌شوند
    $stmt = $pdo->prepare("SELECT id, title, description, category, target_amount, collected_amount, image_url, is_active FROM campaigns WHERE is_active = 1 AND collected_amount < target_amount ORDER BY id DESC");
    $stmt->execute();
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // اگر رکوردی پیدا شد
    if (count($campaigns) > 0) {
        echo json_encode(["status" => "success", "data" => $campaigns], JSON_UNESCAPED_UNICODE);
    } else {
        // برای دیباگ: اگر وصل شد ولی رکوردی با شرط is_active = 1 پیدا نکرد
        echo json_encode(["status" => "empty", "message" => "اتصال موفق بود اما هیچ کمپینی با وضعیت فعال (is_active = 1) یافت نشد.", "data" => []]);
    }

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "خطای دیتابیس: " . $e->getMessage()]);
}
?>