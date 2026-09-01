<?php
require_once __DIR__ . "/config/database.php";

$slug = trim($_GET['page'] ?? 'home');

if ($slug === 'under-construction') {
    include __DIR__ . "/under-construction.html";
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
        // SECURITY: Validate component name to prevent path traversal
        if (!is_string($c) || !preg_match('/^[a-zA-Z0-9_-]+$/', $c)) {
            continue;
        }

        $base = __DIR__ . "/dashboard/components/$c/";

        // SECURITY: Prefer data.json (safe data-driven format) over component.php
        $dataFile = $base . 'data.json';
        if (file_exists($dataFile)) {
            $raw = file_get_contents($dataFile);
            $data = json_decode($raw, true);
            if (is_array($data) && isset($data['content']) && is_string($data['content'])) {
                echo $data['content'];
                continue;
            }
        }

        // Backward compatibility: fall back to component.php for existing components
        $phpFile = $base . 'component.php';
        if (file_exists($phpFile)) {
            include $phpFile;
        }
    }
}