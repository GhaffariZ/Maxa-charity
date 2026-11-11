<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

// 1. Data Layer Tests with SQLite Memory DB
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY)");
shop_ensure_schema($pdo);

$pdo->exec("INSERT INTO product_categories (title, slug) VALUES ('صنایع دستی', 'crafts')");
$pdo->exec("INSERT INTO product_categories (title, slug) VALUES ('ملزومات و ماگ', 'mugs')");
$catId = (int)$pdo->lastInsertId();

$pdo->exec("INSERT INTO products (title, slug, price, stock, status, category_id) VALUES ('کاسه سفالی کارگاه امید', 'pottery-bowl', 350000, 10, 'active', 1)");
$pdo->exec("INSERT INTO products (title, slug, price, stock, status, category_id) VALUES ('ماگ سرامیکی مهر', 'ceramic-mug', 180000, 5, 'active', $catId)");

$cats = shop_get_categories($pdo);
$prods = shop_get_products($pdo);

if (count($cats) < 2 || count($prods) < 2) {
    echo "FAIL: Storefront data fetching failed\n";
    exit(1);
}
if ($prods[1]['title'] !== 'کاسه سفالی کارگاه امید') { // DESC order by id
    echo "FAIL: Product title mismatch\n";
    exit(1);
}

// 2. Test File Existence
$shopFile = __DIR__ . '/../public_html/shop.php';
$showcaseFile = __DIR__ . '/../public_html/dashboard/components/shop-showcase.php';
$headerFile = __DIR__ . '/../public_html/dashboard/components/header/component.php';

if (!file_exists($shopFile)) {
    echo "FAIL: public_html/shop.php missing\n";
    exit(1);
}

if (!file_exists($showcaseFile)) {
    echo "FAIL: public_html/dashboard/components/shop-showcase.php missing\n";
    exit(1);
}

// 3. Header Link Test
$headerContent = file_get_contents($headerFile);
if (!str_contains($headerContent, 'shop.php')) {
    echo "FAIL: header component missing link to shop.php\n";
    exit(1);
}
if (!str_contains($headerContent, 'فروشگاه خیریه')) {
    echo "FAIL: header component missing link text 'فروشگاه خیریه'\n";
    exit(1);
}

// 4. Shop Page Content and Interface Tests
$shopContent = file_get_contents($shopFile);

// Check required JavaScript Cart API interfaces
$requiredJs = [
    'window.MaxaShopCart',
    'maxa_shop_cart',
    'items',
    'add:',
    'remove:',
    'updateQty:',
    'openDrawer:',
    'closeDrawer:',
    'render:',
    'escapeHtml',
];
foreach ($requiredJs as $token) {
    if (!str_contains($shopContent, $token)) {
        echo "FAIL: shop.php missing required cart API token: {$token}\n";
        exit(1);
    }
}

// Check Drawer Cart UI Elements and Notice
$requiredHtml = [
    'shop-hero',
    'shopFloatingCartBtn',
    'shopDrawer',
    'drawerCartItems',
    'drawerCheckoutBtn',
    '/shop-checkout.php',
    'هزینه ارسال به صورت پس‌کرایه است و زمان تحویل توسط مامور پست دریافت می‌شود',
    'shopQuickViewModal',
    'shopSearchInput',
    'shopCatPills',
    'shopFilterEmptyState',
];
foreach ($requiredHtml as $token) {
    if (!str_contains($shopContent, $token)) {
        echo "FAIL: shop.php missing required UI token: {$token}\n";
        exit(1);
    }
}

// 5. Test Showcase Component Content
$showcaseContent = file_get_contents($showcaseFile);
if (!str_contains($showcaseContent, '/shop.php')) {
    echo "FAIL: shop-showcase.php missing link to /shop.php\n";
    exit(1);
}
if (!str_contains($showcaseContent, 'مشاهده همه محصولات فروشگاه خیریه')) {
    echo "FAIL: shop-showcase.php missing CTA button text\n";
    exit(1);
}

// 6. Test Showcase Component Rendering with Mock PDO
ob_start();
try {
    include $showcaseFile;
    $renderedShowcase = ob_get_clean();
} catch (Throwable $e) {
    ob_end_clean();
    echo "FAIL: shop-showcase.php rendering threw exception: " . $e->getMessage() . "\n";
    exit(1);
}

if (!str_contains($renderedShowcase, 'کاسه سفالی کارگاه امید') || !str_contains($renderedShowcase, 'ماگ سرامیکی مهر')) {
    echo "FAIL: shop-showcase.php did not render active products\n";
    exit(1);
}

// 7. Test Shop Page Rendering with Mock PDO
ob_start();
try {
    include $shopFile;
    $renderedShop = ob_get_clean();
} catch (Throwable $e) {
    ob_end_clean();
    echo "FAIL: shop.php rendering threw exception: " . $e->getMessage() . "\n";
    exit(1);
}

if (!str_contains($renderedShop, 'کاسه سفالی کارگاه امید') || !str_contains($renderedShop, 'ماگ سرامیکی مهر')) {
    echo "FAIL: shop.php did not render active products in output\n";
    exit(1);
}
if (!str_contains($renderedShop, 'هزینه ارسال به صورت پس‌کرایه است')) {
    echo "FAIL: shop.php did not render shipping notice\n";
    exit(1);
}

echo "PASS: Shop storefront catalog data, files, and navigation ready.\n";
exit(0);
