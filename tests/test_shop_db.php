<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

// Setup SQLite in-memory test PDO
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Mock baseline orders table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `orders` (
        `id` INTEGER PRIMARY KEY AUTOINCREMENT,
        `user_id` INTEGER NULL,
        `tracking_code` VARCHAR(50) NULL,
        `total_price` DECIMAL(12,2) DEFAULT 0
    )
");

// Run schema migration
shop_ensure_schema($pdo);

// Assert categories table exists
$catStmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='product_categories'");
if (!$catStmt->fetch()) {
    echo "FAIL: product_categories table not created\n";
    exit(1);
}

// Assert products table exists
$prodStmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
if (!$prodStmt->fetch()) {
    echo "FAIL: products table not created\n";
    exit(1);
}

// Assert order_items table exists
$itemsStmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='order_items'");
if (!$itemsStmt->fetch()) {
    echo "FAIL: order_items table not created\n";
    exit(1);
}

// Assert orders columns added
$ordersCols = $pdo->query("PRAGMA table_info(orders)")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($ordersCols, 'name');
$expectedCols = ['order_type', 'payment_status', 'fulfillment_status', 'carrier', 'postal_tracking_code', 'recipient_name', 'recipient_phone', 'province', 'city', 'postal_code', 'address', 'customer_note', 'paid_at', 'shipped_at'];
foreach ($expectedCols as $col) {
    if (!in_array($col, $colNames, true)) {
        echo "FAIL: orders column {$col} not created\n";
        exit(1);
    }
}

// Test idempotency: running migration a second time should not fail
try {
    shop_ensure_schema($pdo);
} catch (Throwable $e) {
    echo "FAIL: shop_ensure_schema is not idempotent: " . $e->getMessage() . "\n";
    exit(1);
}

// Assert seeding and retrieval of categories
$pdo->exec("INSERT INTO product_categories (title, slug, sort_order, status) VALUES ('صنایع دستی', 'handicrafts', 1, 'active')");
$pdo->exec("INSERT INTO product_categories (title, slug, sort_order, status) VALUES ('پوشاک', 'apparel', 2, 'inactive')");
$cats = shop_get_categories($pdo);
if (count($cats) !== 1 || $cats[0]['slug'] !== 'handicrafts') {
    echo "FAIL: category retrieval failed or did not respect active status\n";
    exit(1);
}

// Assert product insertion and retrieval
$catId = (int)$cats[0]['id'];
$pdo->exec("INSERT INTO products (category_id, title, slug, description, price, stock, status) VALUES ({$catId}, 'گلدان میناکاری', 'minakari-vase', 'گلدان دست‌ساز زیبا', 150000, 10, 'active')");
$pdo->exec("INSERT INTO products (category_id, title, slug, description, price, stock, status) VALUES ({$catId}, 'پیش‌نویس محصول', 'draft-product', 'توضیحات پیش‌نویس', 50000, 5, 'draft')");

$products = shop_get_products($pdo);
if (count($products) !== 1 || $products[0]['slug'] !== 'minakari-vase') {
    echo "FAIL: shop_get_products failed to exclude drafts\n";
    exit(1);
}

// Assert category join in product retrieval
if (!isset($products[0]['category_title']) || $products[0]['category_title'] !== 'صنایع دستی') {
    echo "FAIL: product category_title not joined properly\n";
    exit(1);
}

// Test search filter
$searchResults = shop_get_products($pdo, ['search' => 'میناکاری']);
if (count($searchResults) !== 1) {
    echo "FAIL: search filter failed\n";
    exit(1);
}

// Test category_id filter
$emptyCategoryResults = shop_get_products($pdo, ['category_id' => 999]);
if (count($emptyCategoryResults) !== 0) {
    echo "FAIL: category_id filter failed\n";
    exit(1);
}

// Test shop_get_product_by_id
$productId = (int)$products[0]['id'];
$single = shop_get_product_by_id($pdo, $productId);
if (!$single || $single['title'] !== 'گلدان میناکاری' || $single['category_title'] !== 'صنایع دستی') {
    echo "FAIL: shop_get_product_by_id failed\n";
    exit(1);
}

$nonExistent = shop_get_product_by_id($pdo, 999999);
if ($nonExistent !== null) {
    echo "FAIL: shop_get_product_by_id for invalid id should return null\n";
    exit(1);
}

echo "PASS: Database schema and helper functions verified.\n";
exit(0);
