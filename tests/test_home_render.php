<?php
declare(strict_types=1);

chdir(__DIR__ . '/../public_html');
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../public_html';

require_once 'core/component-renderer.php';
require_once 'core/site-locale.php';

// Create SQLite in-memory DB for course and shop data
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Test rendering of courses-showcase
ob_start();
$coursesRendered = render_dashboard_component('courses-showcase', ['pdo' => $pdo]);
$coursesHtml = ob_get_clean();

if (!$coursesRendered) {
    echo "FAIL: courses-showcase component failed to render\n";
    exit(1);
}
if (!str_contains($coursesHtml, 'courses-showcase') && !str_contains($coursesHtml, 'دوره‌های آموزشی')) {
    echo "FAIL: courses-showcase component content mismatch\n";
    exit(1);
}
echo "PASS: courses-showcase rendered successfully (" . strlen($coursesHtml) . " bytes)\n";

// Test rendering of shop-showcase
ob_start();
$shopRendered = render_dashboard_component('shop-showcase', ['pdo' => $pdo]);
$shopHtml = ob_get_clean();

if (!$shopRendered) {
    echo "FAIL: shop-showcase component failed to render\n";
    exit(1);
}
if (!str_contains($shopHtml, 'maxa-shop-showcase-section') || !str_contains($shopHtml, 'فروشگاه خیریه مکسا')) {
    echo "FAIL: shop-showcase component content mismatch\n";
    exit(1);
}
echo "PASS: shop-showcase rendered successfully (" . strlen($shopHtml) . " bytes)\n";

// Test rendering of footer
ob_start();
$footerRendered = render_dashboard_component('footer', ['pdo' => $pdo]);
$footerHtml = ob_get_clean();

if (!$footerRendered) {
    echo "FAIL: footer component failed to render\n";
    exit(1);
}
if (!str_contains($footerHtml, 'footer') && !str_contains($footerHtml, 'مکسا')) {
    echo "FAIL: footer component content mismatch\n";
    exit(1);
}
echo "PASS: footer rendered successfully (" . strlen($footerHtml) . " bytes)\n";

// Test sequence simulating echo_components
$components = [
    'shop-showcase',
    'courses-showcase',
    'ourexcellence',
    'footer'
];

$renderedAll = '';
foreach ($components as $c) {
    ob_start();
    try {
        render_dashboard_component($c, ['pdo' => $pdo]);
    } catch (Throwable $e) {
        echo "<!-- Component render error: " . htmlspecialchars($e->getMessage()) . " -->";
    }
    $renderedAll .= ob_get_clean();
}

if (!str_contains($renderedAll, 'maxa-shop-showcase-section') || 
    !str_contains($renderedAll, 'courses-showcase') || 
    !str_contains($renderedAll, 'footer')) {
    echo "FAIL: Full component sequence does not contain all required sections\n";
    exit(1);
}

echo "PASS: Full home showcase sequence rendered including footer (" . strlen($renderedAll) . " bytes)\n";
echo "ALL TESTS PASSED!\n";
exit(0);
