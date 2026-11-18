<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY, user_id INTEGER, tracking_code VARCHAR(50), total_price DECIMAL(12,2))");
shop_ensure_schema($pdo);

// Seed 1 shop order and 1 stand order
$pdo->exec("INSERT INTO orders (id, user_id, order_type, payment_status, fulfillment_status, total_price, recipient_name) VALUES (1, 10, 'product', 'paid', 'pending', 350000, 'مهسا امینی')");
$pdo->exec("INSERT INTO order_items (order_id, product_id, product_title, quantity, unit_price, total_price) VALUES (1, 1, 'ماگ خیریه', 1, 350000, 350000)");

$pdo->exec("INSERT INTO orders (id, user_id, order_type, payment_status, fulfillment_status, total_price) VALUES (2, 20, 'stand', 'paid', 'pending', 1500000)");

// Test fulfillment update for shop order
$stmt = $pdo->prepare("
    UPDATE orders 
    SET fulfillment_status = ?, carrier = ?, postal_tracking_code = ?, shipped_at = CURRENT_TIMESTAMP 
    WHERE id = ?
");
$stmt->execute(['shipped', 'پست پیشتاز', '123456789012345678901234', 1]);

$check1 = $pdo->query("SELECT fulfillment_status, carrier, postal_tracking_code, shipped_at FROM orders WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

if ($check1['fulfillment_status'] !== 'shipped' || $check1['carrier'] !== 'پست پیشتاز' || $check1['postal_tracking_code'] !== '123456789012345678901234') {
    echo "FAIL: Shop order fulfillment update failed\n";
    exit(1);
}

// Test fulfillment update for stand order
$stmt->execute(['processing', 'پیک موسسه', 'LOCAL-TRK-99', 2]);
$check2 = $pdo->query("SELECT fulfillment_status, carrier, postal_tracking_code FROM orders WHERE id = 2")->fetch(PDO::FETCH_ASSOC);
if ($check2['fulfillment_status'] !== 'processing' || $check2['carrier'] !== 'پیک موسسه') {
    echo "FAIL: Stand order fulfillment update failed\n";
    exit(1);
}

// Test fetching order_items for product order
$itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->execute([1]);
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
if (count($items) !== 1 || $items[0]['product_title'] !== 'ماگ خیریه') {
    echo "FAIL: Order items retrieval failed\n";
    exit(1);
}

// Test query filtering by order_type
$prodOrders = $pdo->query("SELECT * FROM orders WHERE order_type = 'product'")->fetchAll(PDO::FETCH_ASSOC);
if (count($prodOrders) !== 1 || (int)$prodOrders[0]['id'] !== 1) {
    echo "FAIL: Product orders filter query failed\n";
    exit(1);
}

$standOrders = $pdo->query("SELECT * FROM orders WHERE order_type = 'stand' OR order_type IS NULL OR order_type = ''")->fetchAll(PDO::FETCH_ASSOC);
if (count($standOrders) !== 1 || (int)$standOrders[0]['id'] !== 2) {
    echo "FAIL: Stand orders filter query failed\n";
    exit(1);
}

// Test query filtering by fulfillment_status
$shippedOrders = $pdo->query("SELECT * FROM orders WHERE fulfillment_status = 'shipped'")->fetchAll(PDO::FETCH_ASSOC);
if (count($shippedOrders) !== 1 || (int)$shippedOrders[0]['id'] !== 1) {
    echo "FAIL: Shipped status filter query failed\n";
    exit(1);
}

// Check orders.php file contains fulfillment action handler, access checks, and modal
$ordersPhp = file_get_contents(__DIR__ . '/../public_html/dashboard/orders.php');

$requiredSnippets = [
    'update_fulfillment' => 'missing update_fulfillment action',
    'postal_tracking_code' => 'missing postal_tracking_code handling',
    'shop_ensure_schema' => 'missing shop_ensure_schema invocation',
    'dash_can(\'stands\')' => 'missing dash_can stands permission check',
    'dash_can(\'pages\')' => 'missing dash_can pages permission check',
    'csrf_check()' => 'missing csrf_check verification',
    'btn-manage-fulfillment' => 'missing management button',
    'fulfillment-modal' => 'missing fulfillment modal dialog',
    'btn-copy-code' => 'missing postal tracking 1-click copy action',
    'orders-nav-tabs' => 'missing order type tabs navigation',
    'badge-ful-' => 'missing fulfillment status badges',
];

foreach ($requiredSnippets as $snippet => $errMsg) {
    if (!str_contains($ordersPhp, $snippet)) {
        echo "FAIL: orders.php {$errMsg}\n";
        exit(1);
    }
}

echo "PASS: Admin fulfillment update mechanics verified.\n";
exit(0);
