<?php
require_once __DIR__ . "/config/database.php";

$slug = trim($_GET['page'] ?? 'home');

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug=? AND status='published'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    // لود کردن فایل اختصاصی 404 شما به جای چاپ متن
    include __DIR__ . "/404.html";
    exit;
}

$components = json_decode($page['components'], true);

if (is_array($components)) {
    foreach ($components as $c) {
        $file = __DIR__ . "/dashboard/components/$c/component.php";
        if (file_exists($file)) {
            include $file;
        }
    }
}