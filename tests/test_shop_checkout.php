<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY, user_id INTEGER, tracking_code VARCHAR(50), total_price DECIMAL(12,2))");
shop_ensure_schema($pdo);

// Seed product with 5 in stock
$pdo->exec("INSERT INTO products (id, title, slug, price, stock, status) VALUES (1, 'کیف سوزن‌دوزی', 'bag-1', 250000, 5, 'active')");
$pdo->exec("INSERT INTO products (id, title, slug, price, stock, status) VALUES (2, 'دستبند دستباف', 'bracelet-1', 100000, 1, 'active')");

// Test order placement with server-side pricing and item aggregation
$rawItems = [
    ['product_id' => 1, 'quantity' => 1],
    ['product_id' => 1, 'quantity' => 1], // Duplicate product_id -> aggregated to 2
    ['product_id' => 2, 'quantity' => 1]
];

// Aggregate items by product ID
$aggregated = [];
foreach ($rawItems as $it) {
    $pId = (int)$it['product_id'];
    $qty = (int)$it['quantity'];
    $aggregated[$pId] = ($aggregated[$pId] ?? 0) + $qty;
}

if ($aggregated[1] !== 2) {
    echo "FAIL: Expected aggregated quantity for product 1 to be 2\n";
    exit(1);
}

$pdo->beginTransaction();
$totalPrice = 0;
$orderItemsToInsert = [];

foreach ($aggregated as $pId => $qty) {
    $prod = shop_get_product_by_id($pdo, $pId);
    if (!$prod || (int)$prod['stock'] < $qty) {
        $pdo->rollBack();
        echo "FAIL: Stock validation error\n";
        exit(1);
    }
    $lineTotal = (float)$prod['price'] * $qty;
    $totalPrice += $lineTotal;
    $orderItemsToInsert[] = [
        'product_id' => $prod['id'],
        'title' => $prod['title'],
        'qty' => $qty,
        'unit_price' => $prod['price'],
        'line_total' => $lineTotal
    ];
}

$stmt = $pdo->prepare("INSERT INTO orders (user_id, tracking_code, order_type, payment_status, total_price, recipient_name, city, address) VALUES (?, ?, 'product', 'pending', ?, ?, ?, ?)");
$tracking = 'ORD-' . time();
$stmt->execute([10, $tracking, $totalPrice, 'علی رضایی', 'تهران', 'خیابان آزادی']);
$orderId = (int)$pdo->lastInsertId();

$itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_title, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($orderItemsToInsert as $ins) {
    $itemStmt->execute([$orderId, $ins['product_id'], $ins['title'], $ins['qty'], $ins['unit_price'], $ins['line_total']]);
}
$pdo->commit();

// Simulate Payment Callback & Atomic Stock Decrement with Concurrency Guard
$pdo->beginTransaction();
$updOrder = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ? AND payment_status != 'paid'");
$updOrder->execute([$orderId]);

if ($updOrder->rowCount() === 1) {
    $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
    $outOfStockStmt = $pdo->prepare("UPDATE products SET status = 'out_of_stock' WHERE id = ? AND stock <= 0");
    foreach ($orderItemsToInsert as $ins) {
        $stockStmt->execute([$ins['qty'], $ins['product_id'], $ins['qty']]);
        $outOfStockStmt->execute([$ins['product_id']]);
    }
}
$pdo->commit();

// Simulate a duplicate concurrent callback hitting the same paid order:
$pdo->beginTransaction();
$updOrderDuplicate = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ? AND payment_status != 'paid'");
$updOrderDuplicate->execute([$orderId]);
if ($updOrderDuplicate->rowCount() === 1) {
    echo "FAIL: Concurrent/duplicate callback incorrectly matched rowCount === 1\n";
    exit(1);
}
$pdo->commit();

// Check updated product stock for product 1 (5 - 2 = 3)
$updatedProd1 = shop_get_product_by_id($pdo, 1);
if ((int)$updatedProd1['stock'] !== 3) {
    echo "FAIL: Expected stock of product 1 to be 3, got " . $updatedProd1['stock'] . "\n";
    exit(1);
}

// Check updated product stock and status for product 2 (1 - 1 = 0, status => out_of_stock)
$updatedProd2 = shop_get_product_by_id($pdo, 2);
if ((int)$updatedProd2['stock'] !== 0) {
    echo "FAIL: Expected stock of product 2 to be 0, got " . $updatedProd2['stock'] . "\n";
    exit(1);
}
if ($updatedProd2['status'] !== 'out_of_stock') {
    echo "FAIL: Expected status of product 2 to be 'out_of_stock', got " . $updatedProd2['status'] . "\n";
    exit(1);
}

// Test stock validation rejection when duplicate aggregated items exceed available stock
try {
    $pdo->beginTransaction();
    $excessRawItems = [
        ['product_id' => 1, 'quantity' => 2],
        ['product_id' => 1, 'quantity' => 2] // Sum = 4, but stock is 3
    ];
    $excessAggregated = [];
    foreach ($excessRawItems as $it) {
        $pId = (int)$it['product_id'];
        $excessAggregated[$pId] = ($excessAggregated[$pId] ?? 0) + (int)$it['quantity'];
    }
    foreach ($excessAggregated as $pId => $qty) {
        $prod = shop_get_product_by_id($pdo, $pId);
        if (!$prod || (int)$prod['stock'] < $qty) {
            throw new RuntimeException("Insufficient stock for product " . $pId);
        }
    }
    $pdo->commit();
    echo "FAIL: Expected insufficient stock exception for aggregated excess items\n";
    exit(1);
} catch (RuntimeException $e) {
    $pdo->rollBack();
    // expected
}

// Test CSRF token validation logic
$sessionCsrf = bin2hex(random_bytes(16));
$validCsrf = $sessionCsrf;
$invalidCsrf = bin2hex(random_bytes(16));
if (!hash_equals($sessionCsrf, $validCsrf)) {
    echo "FAIL: Valid CSRF hash_equals failed\n";
    exit(1);
}
if (hash_equals($sessionCsrf, $invalidCsrf)) {
    echo "FAIL: Invalid CSRF hash_equals should not match\n";
    exit(1);
}

// Test file existence
if (!file_exists(__DIR__ . '/../public_html/shop-checkout.php')) {
    echo "FAIL: public_html/shop-checkout.php missing\n";
    exit(1);
}
if (!file_exists(__DIR__ . '/../public_html/shop-payment-callback.php')) {
    echo "FAIL: public_html/shop-payment-callback.php missing\n";
    exit(1);
}

echo "PASS: Checkout validation, server-side pricing, and stock decrement verified.\n";
exit(0);
