<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY, user_id INTEGER, tracking_code VARCHAR(50), total_price DECIMAL(12,2))");
shop_ensure_schema($pdo);

// Seed 1 shop order and 1 stand order
$pdo->exec("INSERT INTO orders (id, user_id, order_type, payment_status, fulfillment_status, carrier, postal_tracking_code, total_price, recipient_name) VALUES (1, 5, 'product', 'paid', 'shipped', 'پست پیشتاز', '987654321', 450000, 'سارا محمدی')");
$pdo->exec("INSERT INTO order_items (order_id, product_id, product_title, quantity, unit_price, total_price) VALUES (1, 1, 'عروسک دست‌ساز خیریه', 1, 450000, 450000)");

$pdo->exec("INSERT INTO orders (id, user_id, order_type, payment_status, fulfillment_status, total_price) VALUES (2, 5, 'stand', 'paid', 'processing', 200000)");

// Verify retrieval query with order_items
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([5]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($orders) !== 2) {
    echo "FAIL: Expected 2 orders, got " . count($orders) . "\n";
    exit(1);
}

$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([1]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

if (count($items) !== 1 || $items[0]['product_title'] !== 'عروسک دست‌ساز خیریه') {
    echo "FAIL: Item association failed\n";
    exit(1);
}

echo "PASS: API order items and fulfillment data query verified.\n";
exit(0);
