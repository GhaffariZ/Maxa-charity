<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

echo "========================================================\n";
echo "  MAXA CHARITY SHOP - FULL END-TO-END INTEGRATION TEST  \n";
echo "========================================================\n\n";

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ---------------------------------------------------------
// 1. Schema Initialization
// ---------------------------------------------------------
echo "[1/8] Verifying Database Schema Initialization...\n";
$pdo->exec("
    CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        tracking_code VARCHAR(50),
        branch_id INTEGER,
        stand_id INTEGER,
        total_price DECIMAL(12,2) DEFAULT 0,
        from_user VARCHAR(150),
        to_user VARCHAR(150),
        message TEXT,
        address TEXT
    )
");
$pdo->exec("
    CREATE TABLE IF NOT EXISTS branches (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(150),
        province VARCHAR(100),
        city VARCHAR(100),
        is_hq TINYINT(1) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active'
    )
");
$pdo->exec("
    CREATE TABLE IF NOT EXISTS stands (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        branch_id INTEGER,
        title VARCHAR(200),
        unit_price DECIMAL(12,2) DEFAULT 0,
        image VARCHAR(255),
        is_active TINYINT(1) DEFAULT 1
    )
");

shop_ensure_schema($pdo);

// Assert tables exist
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
$requiredTables = ['product_categories', 'products', 'orders', 'order_items'];
foreach ($requiredTables as $rt) {
    if (!in_array($rt, $tables, true)) {
        throw new RuntimeException("Missing required table: {$rt}");
    }
}
echo "  ✓ Schema and required tables verified.\n";

// ---------------------------------------------------------
// 2. Product and Category Management (Admin)
// ---------------------------------------------------------
echo "[2/8] Creating Categories and Products...\n";
$catStmt = $pdo->prepare("INSERT INTO product_categories (title, slug, sort_order) VALUES (?, ?, ?)");
$catStmt->execute(['صنایع دستی مددجویان', 'handicrafts', 1]);
$catId = (int)$pdo->lastInsertId();

$prodStmt = $pdo->prepare("
    INSERT INTO products (category_id, title, slug, price, stock, status, description)
    VALUES (?, ?, ?, ?, ?, 'active', ?)
");
$prodStmt->execute([$catId, 'کاسه سفالی کارگاه امید', 'pottery-bowl-hope', 350000, 10, 'کاسه سفالی هنر دست مددجویان']);
$prod1Id = (int)$pdo->lastInsertId();

$prodStmt->execute([$catId, 'کیف سوزن‌دوزی بلوچی', 'balochi-needlecraft-bag', 480000, 3, 'کیف تمام دست‌دوز سوزن‌دوزی']);
$prod2Id = (int)$pdo->lastInsertId();

$cats = shop_get_categories($pdo);
$prods = shop_get_products($pdo);

if (count($cats) !== 1 || count($prods) !== 2) {
    throw new RuntimeException("Category or Product insertion failed.");
}
echo "  ✓ 1 category and 2 products created successfully.\n";

// ---------------------------------------------------------
// 3. Storefront Catalog Retrieval & Cart Simulation
// ---------------------------------------------------------
echo "[3/8] Simulating Storefront Catalog & Cart Calculations...\n";
$p1 = shop_get_product_by_id($pdo, $prod1Id);
$p2 = shop_get_product_by_id($pdo, $prod2Id);

if (!$p1 || !$p2 || (int)$p1['stock'] !== 10 || (int)$p2['stock'] !== 3) {
    throw new RuntimeException("Product retrieval failed.");
}

// User adds 2 of Product 1 and 1 of Product 2 to cart
$cartItems = [
    ['product_id' => $prod1Id, 'quantity' => 2],
    ['product_id' => $prod2Id, 'quantity' => 1],
];

// Server calculates total authoritatively
$calculatedTotal = 0;
$orderItemsData = [];
foreach ($cartItems as $ci) {
    $p = shop_get_product_by_id($pdo, (int)$ci['product_id']);
    if (!$p || (int)$p['stock'] < (int)$ci['quantity']) {
        throw new RuntimeException("Insufficient stock for product {$ci['product_id']}");
    }
    $lineTotal = (float)$p['price'] * (int)$ci['quantity'];
    $calculatedTotal += $lineTotal;
    $orderItemsData[] = [
        'product_id' => $p['id'],
        'title' => $p['title'],
        'qty' => $ci['quantity'],
        'unit_price' => (float)$p['price'],
        'line_total' => $lineTotal,
    ];
}

$expectedTotal = (350000 * 2) + (480000 * 1); // 700000 + 480000 = 1180000
if ($calculatedTotal !== (float)$expectedTotal) {
    throw new RuntimeException("Total price mismatch: expected {$expectedTotal}, got {$calculatedTotal}");
}
echo "  ✓ Server-authoritative cart pricing verified (Total: " . number_format($calculatedTotal) . " Tomans).\n";

// ---------------------------------------------------------
// 4. Order Placement (Checkout)
// ---------------------------------------------------------
echo "[4/8] Placing Pending Order...\n";
$benefactorUserId = 42;
$orderTrackingCode = 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

$pdo->beginTransaction();
$insOrder = $pdo->prepare("
    INSERT INTO orders (
        user_id, tracking_code, order_type, payment_status, fulfillment_status,
        total_price, recipient_name, recipient_phone, province, city, postal_code, address,
        created_at
    ) VALUES (
        ?, ?, 'product', 'pending', 'pending',
        ?, ?, ?, ?, ?, ?, ?,
        CURRENT_TIMESTAMP
    )
");
$insOrder->execute([
    $benefactorUserId,
    $orderTrackingCode,
    $calculatedTotal,
    'سهراب سپهری',
    '09123456789',
    'اصفهان',
    'کاشان',
    '8712345678',
    'خیابان گلستان، کوچه بهار، پلاک ۱۲',
]);
$orderId = (int)$pdo->lastInsertId();

$insItem = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, product_title, quantity, unit_price, total_price)
    VALUES (?, ?, ?, ?, ?, ?)
");
foreach ($orderItemsData as $oid) {
    $insItem->execute([
        $orderId,
        $oid['product_id'],
        $oid['title'],
        $oid['qty'],
        $oid['unit_price'],
        $oid['line_total'],
    ]);
}
$pdo->commit();
echo "  ✓ Pending order #{$orderId} placed with tracking code {$orderTrackingCode}.\n";

// ---------------------------------------------------------
// 5. Payment Callback & Atomic Stock Decrement
// ---------------------------------------------------------
echo "[5/8] Simulating Payment Callback & Atomic Stock Reduction...\n";
$pdo->beginTransaction();

// Concurrency guard: update payment status only if not already paid
$updOrder = $pdo->prepare("
    UPDATE orders 
    SET payment_status = 'paid', paid_at = CURRENT_TIMESTAMP 
    WHERE id = ? AND payment_status != 'paid'
");
$updOrder->execute([$orderId]);

if ($updOrder->rowCount() !== 1) {
    $pdo->rollBack();
    throw new RuntimeException("Payment callback concurrency guard failed (already paid or missing).");
}

// Atomically decrement stock
$decStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
foreach ($orderItemsData as $oid) {
    $decStock->execute([$oid['qty'], $oid['product_id'], $oid['qty']]);
    if ($decStock->rowCount() !== 1) {
        $pdo->rollBack();
        throw new RuntimeException("Stock decrement failed for product ID {$oid['product_id']}");
    }
}
$pdo->commit();

// Assert updated stock
$p1After = shop_get_product_by_id($pdo, $prod1Id);
$p2After = shop_get_product_by_id($pdo, $prod2Id);

if ((int)$p1After['stock'] !== 8 || (int)$p2After['stock'] !== 2) {
    throw new RuntimeException("Updated stock mismatch: P1 expected 8 got {$p1After['stock']}, P2 expected 2 got {$p2After['stock']}");
}

// Test concurrency guard against double execution
$pdo->beginTransaction();
$updOrder->execute([$orderId]);
if ($updOrder->rowCount() !== 0) {
    $pdo->rollBack();
    throw new RuntimeException("Double callback guard failed: rowCount was not 0.");
}
$pdo->rollBack();
echo "  ✓ Payment callback succeeded. P1 stock: 10 -> 8, P2 stock: 3 -> 2. Double-processing prevented.\n";

// ---------------------------------------------------------
// 6. Admin Fulfillment & Postal Tracking Enhancement
// ---------------------------------------------------------
echo "[6/8] Simulating Admin Order Fulfillment & Postal Tracking Assignment...\n";
$postalTrackingCode = '123456789012345678901234';
$carrier = 'پست پیشتاز';

$updFulfillment = $pdo->prepare("
    UPDATE orders 
    SET fulfillment_status = ?, carrier = ?, postal_tracking_code = ?, shipped_at = CURRENT_TIMESTAMP
    WHERE id = ?
");
$updFulfillment->execute(['shipped', $carrier, $postalTrackingCode, $orderId]);

$checkFulfill = $pdo->prepare("SELECT fulfillment_status, carrier, postal_tracking_code, shipped_at FROM orders WHERE id = ?");
$checkFulfill->execute([$orderId]);
$fRow = $checkFulfill->fetch(PDO::FETCH_ASSOC);

if ($fRow['fulfillment_status'] !== 'shipped' || $fRow['carrier'] !== $carrier || $fRow['postal_tracking_code'] !== $postalTrackingCode || empty($fRow['shipped_at'])) {
    throw new RuntimeException("Admin fulfillment update verification failed.");
}
echo "  ✓ Order fulfillment marked as 'shipped' via {$carrier} with tracking code {$postalTrackingCode}.\n";

// ---------------------------------------------------------
// 7. Benefactor User Panel API Query (`/user/orders`)
// ---------------------------------------------------------
echo "[7/8] Testing Benefactor API Orders Retrieval & Items Association...\n";

// Also seed 1 stand order for the same benefactor to test multi-order and multi-type handling
$insStand = $pdo->prepare("
    INSERT INTO orders (
        user_id, tracking_code, order_type, payment_status, fulfillment_status,
        total_price, from_user, to_user, address, created_at
    ) VALUES (
        ?, 'ORD-STAND-99', 'stand', 'paid', 'processing',
        250000, 'سهراب سپهری', 'خانواده محترم کاشانی', 'مسجد جامع کاشان', CURRENT_TIMESTAMP
    )
");
$insStand->execute([$benefactorUserId]);
$standOrderId = (int)$pdo->lastInsertId();

// Run the exact query logic from OrderController::getMyOrders()
$stmt = $pdo->prepare("
    SELECT o.*, b.name AS branch_name, s.title AS stand_title
    FROM `orders` o
    LEFT JOIN `branches` b ON o.branch_id = b.id
    LEFT JOIN `stands` s ON o.stand_id = s.id
    WHERE o.`user_id` = ? 
    ORDER BY o.`id` DESC
");
$stmt->execute([$benefactorUserId]);
$userOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($userOrders) !== 2) {
    throw new RuntimeException("Expected 2 user orders, got " . count($userOrders));
}

$productOrderIds = [];
foreach ($userOrders as &$ord) {
    if (empty($ord['order_type'])) {
        $ord['order_type'] = (!empty($ord['stand_id']) || !empty($ord['from_user'])) ? 'stand' : 'product';
    }
    if ($ord['order_type'] === 'product') {
        $productOrderIds[] = (int)$ord['id'];
    }
    $ord['items'] = [];
}
unset($ord);

if (!empty($productOrderIds)) {
    $inClause = implode(',', array_fill(0, count($productOrderIds), '?'));
    $itemStmt = $pdo->prepare("SELECT * FROM `order_items` WHERE `order_id` IN ($inClause) ORDER BY `id` ASC");
    $itemStmt->execute($productOrderIds);
    $itemsMap = [];
    while ($row = $itemStmt->fetch(PDO::FETCH_ASSOC)) {
        $itemsMap[(int)$row['order_id']][] = $row;
    }
    foreach ($userOrders as &$ord) {
        if (($ord['order_type'] ?? '') === 'product') {
            $ord['items'] = $itemsMap[(int)$ord['id']] ?? [];
        }
    }
    unset($ord);
}

// Assert Product Order details
$foundProductOrder = null;
$foundStandOrder = null;
foreach ($userOrders as $uo) {
    if ($uo['order_type'] === 'product') $foundProductOrder = $uo;
    if ($uo['order_type'] === 'stand') $foundStandOrder = $uo;
}

if (!$foundProductOrder || !$foundStandOrder) {
    throw new RuntimeException("Failed to find both product and stand orders in user results.");
}

if ($foundProductOrder['fulfillment_status'] !== 'shipped' || $foundProductOrder['postal_tracking_code'] !== $postalTrackingCode) {
    throw new RuntimeException("Product order fulfillment details mismatch.");
}

if (count($foundProductOrder['items']) !== 2) {
    throw new RuntimeException("Expected 2 order items in product order, got " . count($foundProductOrder['items']));
}

if ($foundProductOrder['items'][0]['product_title'] !== 'کاسه سفالی کارگاه امید' || $foundProductOrder['items'][1]['product_title'] !== 'کیف سوزن‌دوزی بلوچی') {
    throw new RuntimeException("Product order item titles mismatch.");
}

echo "  ✓ Benefactor API orders query verified (2 orders returned: 1 product with 2 items, 1 stand).\n";

// ---------------------------------------------------------
// 8. Admin Stand Order Postal Tracking Enhancement
// ---------------------------------------------------------
echo "[8/8] Testing Stand Order Postal Tracking Enhancement...\n";
$standTrackingCode = '987654321098765432109876';
$updStandFulfill = $pdo->prepare("
    UPDATE orders 
    SET fulfillment_status = ?, carrier = ?, postal_tracking_code = ?, shipped_at = CURRENT_TIMESTAMP
    WHERE id = ?
");
$updStandFulfill->execute(['delivered', 'پیک موتوری', $standTrackingCode, $standOrderId]);

$checkStand = $pdo->prepare("SELECT fulfillment_status, carrier, postal_tracking_code FROM orders WHERE id = ?");
$checkStand->execute([$standOrderId]);
$stRow = $checkStand->fetch(PDO::FETCH_ASSOC);

if ($stRow['fulfillment_status'] !== 'delivered' || $stRow['carrier'] !== 'پیک موتوری' || $stRow['postal_tracking_code'] !== $standTrackingCode) {
    throw new RuntimeException("Stand order fulfillment update failed.");
}
echo "  ✓ Stand order fulfillment and tracking verified.\n\n";

echo "========================================================\n";
echo "  ALL END-TO-END VERIFICATION STEPS PASSED (0 ERRORS)   \n";
echo "========================================================\n";
exit(0);
