<?php
require_once __DIR__ . "/core/database.php";
require_once __DIR__ . "/core/icons.php";
require_once __DIR__ . '/core/component-renderer.php';

$slug = trim($_GET['page'] ?? 'home');

if ($slug === 'under-construction') {
    include __DIR__ . "/under-construction.html";
    exit;
}

if ($slug === 'macsa-story') {
    include __DIR__ . "/macsa-story.php";
    exit;
}

if ($slug === 'network' || $slug === 'personal-resume-list') {
    include __DIR__ . "/network.php";
    exit;
}

if ($slug === 'stand-order' || $slug === 'stand-sell-section') {
    include __DIR__ . "/stand-order.php";
    exit;
}

if ($slug === 'branch-intro' || $slug === 'branch-about') {
    include __DIR__ . "/branch-intro.php";
    exit;
}

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
        render_dashboard_component((string)$c, get_defined_vars());
    }
}
