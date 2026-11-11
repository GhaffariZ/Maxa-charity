<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY)");
shop_ensure_schema($pdo);

// 1. Test Insert Product
$stmt = $pdo->prepare("INSERT INTO products (title, slug, price, stock, status) VALUES (?, ?, ?, ?, ?)");
$stmt->execute(['ماگ خیریه مکسا', 'maksa-mug', 120000, 25, 'active']);
$id = (int)$pdo->lastInsertId();

$prod = shop_get_product_by_id($pdo, $id);
if (!$prod || $prod['title'] !== 'ماگ خیریه مکسا' || (int)$prod['stock'] !== 25) {
    echo "FAIL: Product creation verification failed\n";
    exit(1);
}

// 2. Test Stock Update
$upd = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
$upd->execute([5, $id, 5]);

$updatedProd = shop_get_product_by_id($pdo, $id);
if ((int)$updatedProd['stock'] !== 20) {
    echo "FAIL: Stock reduction failed\n";
    exit(1);
}

// 3. Test Product Update
$upd2 = $pdo->prepare("UPDATE products SET title = ?, price = ?, stock = ? WHERE id = ?");
$upd2->execute(['ماگ سرامیکی مکسا', 150000, 30, $id]);
$prodEdited = shop_get_product_by_id($pdo, $id);
if (!$prodEdited || $prodEdited['title'] !== 'ماگ سرامیکی مکسا' || (float)$prodEdited['price'] != 150000 || (int)$prodEdited['stock'] !== 30) {
    echo "FAIL: Product update verification failed\n";
    exit(1);
}

// 4. Test Toggle Status
$toggleStmt = $pdo->prepare("UPDATE products SET status = CASE WHEN status = 'active' THEN 'out_of_stock' ELSE 'active' END WHERE id = ?");
$toggleStmt->execute([$id]);
$prodToggled = shop_get_product_by_id($pdo, $id);
if (!$prodToggled || $prodToggled['status'] !== 'out_of_stock') {
    echo "FAIL: Toggle status failed\n";
    exit(1);
}

// 5. Test Delete Product
$delStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$delStmt->execute([$id]);
if (shop_get_product_by_id($pdo, $id) !== null) {
    echo "FAIL: Delete product failed\n";
    exit(1);
}

// 6. Test public_html/dashboard/products.php exists
$productsFile = __DIR__ . '/../public_html/dashboard/products.php';
if (!file_exists($productsFile)) {
    echo "FAIL: public_html/dashboard/products.php does not exist\n";
    exit(1);
}

// 7. Test public_html/dashboard/index.php navigation
$indexContent = file_get_contents(__DIR__ . '/../public_html/dashboard/index.php');
if (!str_contains($indexContent, 'products.php')) {
    echo "FAIL: public_html/dashboard/index.php missing link to products.php\n";
    exit(1);
}

echo "PASS: Admin product CRUD and stock mechanics verified.\n";
exit(0);
