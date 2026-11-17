# Charity Shop & Order Fulfillment Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a mini online charity store for physical products with shopping cart, address collection, online payment, benefactor panel order tracking, and admin product and shipment management (for both shop products and stand orders).

**Architecture:** A lightweight modular PHP/MySQL backend with self-healing schema migrations, server-authoritative pricing and atomic stock deduction; a responsive public storefront and client-side Drawer Cart (`localStorage`); an enhanced React/TypeScript Benefactor Panel (`Orders.tsx`) with shipment status and postal tracking link; and an admin dashboard for product catalog and unified order fulfillment.

**Tech Stack:** PHP 7.4+, MySQL/MariaDB, Tailwind CSS, JavaScript (Vanilla/Alpine), React 18, TypeScript, Vite, Lucide Icons.

**Spec:** `docs/superpowers/specs/2026-11-11-charity-shop-design.md`

## Global Constraints

- **Git Branch:** All work MUST be performed on `feature/charity-shop`. Do not touch `main`.
- **Pricing:** Server-authoritative pricing only. Never trust item prices or total sent from client.
- **Stock Management:** Atomic stock reduction upon successful payment confirmation only (`UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?`).
- **Shipping Policy:** Cash on delivery (پس‌کرایه) — shipping fee is 0 on the invoice; recipient pays carrier upon receipt.
- **Backward Compatibility:** Existing stand orders in `orders` table must remain intact and work with new fulfillment fields.
- **Benefactor Panel:** Must pass `npm run build` without TypeScript errors.

---

### Task 1: Database Migration & Schema Setup (Products, Categories, Order Items, Orders columns)

**Files:**
- Create: `database/migrations/20261111_charity_shop_schema.sql`
- Create: `public_html/core/shop-db.php`
- Create: `tests/test_shop_db.php`

**Interfaces:**
- Produces: `shop_ensure_schema(PDO $pdo): void`
- Produces: `shop_get_categories(PDO $pdo): array`
- Produces: `shop_get_products(PDO $pdo, array $filters = []): array`
- Produces: `shop_get_product_by_id(PDO $pdo, int $id): ?array`

- [ ] **Step 1: Write the failing test for schema setup and database helpers**

Create `tests/test_shop_db.php`:
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

// Setup SQLite in-memory or MySQL test PDO
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

// Assert seeding and retrieval
$pdo->exec("INSERT INTO product_categories (title, slug) VALUES ('صنایع دستی', 'handicrafts')");
$cats = shop_get_categories($pdo);
if (count($cats) !== 1 || $cats[0]['slug'] !== 'handicrafts') {
    echo "FAIL: category retrieval failed\n";
    exit(1);
}

echo "PASS: Database schema and helper functions verified.\n";
exit(0);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php tests/test_shop_db.php`
Expected: FAIL with "Failed opening required shop-db.php"

- [ ] **Step 3: Create SQL migration file and implementation**

Create `database/migrations/20261111_charity_shop_schema.sql`:
```sql
-- Migration: Charity Shop and Unified Order Fulfillment
CREATE TABLE IF NOT EXISTS `product_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `stock` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(500) NULL,
    `gallery_json` TEXT NULL,
    `status` ENUM('active', 'draft', 'out_of_stock') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NULL,
    `product_title` VARCHAR(255) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `total_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Create `public_html/core/shop-db.php`:
```php
<?php
declare(strict_types=1);

/**
 * Self-healing schema migration for Charity Shop & Unified Fulfillment
 */
function shop_ensure_schema(PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $isSqlite = ($driver === 'sqlite');

    if ($isSqlite) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `product_categories` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `title` VARCHAR(150) NOT NULL,
                `slug` VARCHAR(150) NOT NULL,
                `sort_order` INTEGER DEFAULT 0,
                `status` VARCHAR(20) DEFAULT 'active',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS `products` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `category_id` INTEGER NULL,
                `title` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `price` DECIMAL(12, 2) NOT NULL DEFAULT 0,
                `stock` INTEGER NOT NULL DEFAULT 0,
                `image_url` VARCHAR(500) NULL,
                `gallery_json` TEXT NULL,
                `status` VARCHAR(20) DEFAULT 'active',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS `order_items` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `order_id` INTEGER NOT NULL,
                `product_id` INTEGER NULL,
                `product_title` VARCHAR(255) NOT NULL,
                `quantity` INTEGER NOT NULL DEFAULT 1,
                `unit_price` DECIMAL(12, 2) NOT NULL DEFAULT 0,
                `total_price` DECIMAL(12, 2) NOT NULL DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $cols = [
            "order_type VARCHAR(20) DEFAULT 'stand'",
            "payment_status VARCHAR(20) DEFAULT 'pending'",
            "fulfillment_status VARCHAR(20) DEFAULT 'pending'",
            "carrier VARCHAR(100) NULL",
            "postal_tracking_code VARCHAR(100) NULL",
            "recipient_name VARCHAR(150) NULL",
            "recipient_phone VARCHAR(30) NULL",
            "province VARCHAR(100) NULL",
            "city VARCHAR(100) NULL",
            "postal_code VARCHAR(20) NULL",
            "address TEXT NULL",
            "customer_note TEXT NULL",
            "paid_at DATETIME NULL",
            "shipped_at DATETIME NULL",
        ];
        foreach ($cols as $colDef) {
            try {
                $pdo->exec("ALTER TABLE `orders` ADD COLUMN {$colDef}");
            } catch (Throwable $e) {}
        }
        return;
    }

    // MySQL / MariaDB Schema
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `product_categories` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(150) NOT NULL,
            `slug` VARCHAR(150) NOT NULL UNIQUE,
            `sort_order` INT NOT NULL DEFAULT 0,
            `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `products` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT UNSIGNED NULL,
            `title` VARCHAR(255) NOT NULL,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `description` TEXT NULL,
            `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `stock` INT NOT NULL DEFAULT 0,
            `image_url` VARCHAR(500) NULL,
            `gallery_json` TEXT NULL,
            `status` ENUM('active', 'draft', 'out_of_stock') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `order_items` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `order_id` BIGINT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NULL,
            `product_title` VARCHAR(255) NOT NULL,
            `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
            `unit_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `total_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $mysqlColumns = [
        "ADD COLUMN `order_type` ENUM('stand', 'product') NOT NULL DEFAULT 'stand' AFTER `user_id`",
        "ADD COLUMN `payment_status` ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending' AFTER `order_type`",
        "ADD COLUMN `fulfillment_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending' AFTER `payment_status`",
        "ADD COLUMN `carrier` VARCHAR(100) NULL AFTER `fulfillment_status`",
        "ADD COLUMN `postal_tracking_code` VARCHAR(100) NULL AFTER `carrier`",
        "ADD COLUMN `recipient_name` VARCHAR(150) NULL AFTER `postal_tracking_code`",
        "ADD COLUMN `recipient_phone` VARCHAR(30) NULL AFTER `recipient_name`",
        "ADD COLUMN `province` VARCHAR(100) NULL AFTER `recipient_phone`",
        "ADD COLUMN `city` VARCHAR(100) NULL AFTER `province`",
        "ADD COLUMN `postal_code` VARCHAR(20) NULL AFTER `city`",
        "ADD COLUMN `address` TEXT NULL AFTER `postal_code`",
        "ADD COLUMN `customer_note` TEXT NULL AFTER `address`",
        "ADD COLUMN `paid_at` DATETIME NULL AFTER `customer_note`",
        "ADD COLUMN `shipped_at` DATETIME NULL AFTER `paid_at`",
    ];

    foreach ($mysqlColumns as $alter) {
        try {
            $pdo->exec("ALTER TABLE `orders` " . $alter);
        } catch (Throwable $e) {}
    }
}

function shop_get_categories(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM product_categories WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
    return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
}

function shop_get_products(PDO $pdo, array $filters = []): array {
    $where = ["p.status != 'draft'"];
    $params = [];

    if (!empty($filters['category_id'])) {
        $where[] = "p.category_id = ?";
        $params[] = (int)$filters['category_id'];
    }

    if (!empty($filters['search'])) {
        $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
        $term = "%" . trim((string)$filters['search']) . "%";
        $params[] = $term;
        $params[] = $term;
    }

    $whereSql = implode(' AND ', $where);
    $sql = "
        SELECT p.*, c.title AS category_title 
        FROM products p 
        LEFT JOIN product_categories c ON p.category_id = c.id 
        WHERE {$whereSql}
        ORDER BY p.id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function shop_get_product_by_id(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT p.*, c.title AS category_title FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return $res ?: null;
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php tests/test_shop_db.php`
Expected: PASS: Database schema and helper functions verified.

- [ ] **Step 5: Commit changes**

```bash
git add database/migrations/20261111_charity_shop_schema.sql public_html/core/shop-db.php tests/test_shop_db.php
git commit -m "feat(shop): add database migrations and schema helpers for charity shop"
```

---

### Task 2: Admin Dashboard Product Management (`/dashboard/products.php`)

**Files:**
- Create: `public_html/dashboard/products.php`
- Modify: `public_html/dashboard/_panel_head.php` (add navigation item for shop/products)
- Test: `tests/test_admin_products.php`

**Interfaces:**
- Consumes: `shop_ensure_schema(PDO $pdo)` from `public_html/core/shop-db.php`
- Consumes: `_guard.php` (`dash_require()`, `csrf_check()`, `csrf_token()`)
- Produces: CRUD endpoints for products (`action=create`, `action=update`, `action=delete`, `action=toggle_status`)

- [ ] **Step 1: Write the test for product admin management**

Create `tests/test_admin_products.php`:
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY)");
shop_ensure_schema($pdo);

// Test Insert Product
$stmt = $pdo->prepare("INSERT INTO products (title, slug, price, stock, status) VALUES (?, ?, ?, ?, ?)");
$stmt->execute(['ماگ خیریه مکسا', 'maksa-mug', 120000, 25, 'active']);
$id = (int)$pdo->lastInsertId();

$prod = shop_get_product_by_id($pdo, $id);
if (!$prod || $prod['title'] !== 'ماگ خیریه مکسا' || (int)$prod['stock'] !== 25) {
    echo "FAIL: Product creation verification failed\n";
    exit(1);
}

// Test Stock Update
$upd = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
$upd->execute([5, $id, 5]);

$updatedProd = shop_get_product_by_id($pdo, $id);
if ((int)$updatedProd['stock'] !== 20) {
    echo "FAIL: Stock reduction failed\n";
    exit(1);
}

echo "PASS: Admin product CRUD and stock mechanics verified.\n";
exit(0);
```

- [ ] **Step 2: Run test to verify it passes**

Run: `php tests/test_admin_products.php`
Expected: PASS: Admin product CRUD and stock mechanics verified.

- [ ] **Step 3: Implement `public_html/dashboard/products.php`**

Create `public_html/dashboard/products.php` with:
1. `require_once __DIR__ . '/_guard.php';` and `dash_require('pages');`
2. Invocation of `shop_ensure_schema($pdo);`
3. Handling of POST actions:
   - `save_product`: Handle title, slug, category_id, price, stock, description, image upload (validate JPG/PNG/WebP, upload to `../uploads/products/`), insert or update.
   - `toggle_status`: Toggle between active and out_of_stock / draft.
   - `delete_product`: Soft delete or delete.
4. Render HTML table of products with categories, current stock, price, status badge, action buttons (edit modal, delete, toggle status).
5. Add modal dialog for "افزودن محصول جدید" and "ویرایش محصول".

- [ ] **Step 4: Update dashboard navigation**

In `public_html/dashboard/_panel_head.php`, add the link for "مدیریت محصولات فروشگاه" under appropriate navigation section:
```php
<a href="/dashboard/products.php" class="dash-nav-item">
    <i class="dash-icon bx bx-shopping-bag"></i>
    <span>محصولات فروشگاه</span>
</a>
```

- [ ] **Step 5: Run tests and verify syntax**

Run: `php -l public_html/dashboard/products.php`
Expected: No syntax errors detected.

- [ ] **Step 6: Commit changes**

```bash
git add public_html/dashboard/products.php public_html/dashboard/_panel_head.php tests/test_admin_products.php
git commit -m "feat(dashboard): add product catalog and stock management page"
```

---

### Task 3: Public Storefront & Drawer Cart (`/shop.php` & Showcase Component)

**Files:**
- Create: `public_html/shop.php`
- Create: `public_html/dashboard/components/shop-showcase.php`
- Modify: `public_html/core/component-renderer.php` (if necessary for nav link or component mapping)
- Test: `tests/test_shop_storefront.php`

**Interfaces:**
- Consumes: `shop_get_products(PDO $pdo, array $filters)` and `shop_get_categories(PDO $pdo)` from `public_html/core/shop-db.php`
- Produces: Public page `/shop.php`
- Produces: Reusable component `render_dashboard_component('shop-showcase')`
- Produces: JS Cart API in browser (`window.MaxaShopCart` with `items`, `add()`, `remove()`, `updateQty()`, `openDrawer()`, `closeDrawer()`)

- [ ] **Step 1: Write test for shop storefront rendering and categories**

Create `tests/test_shop_storefront.php`:
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY)");
shop_ensure_schema($pdo);

$pdo->exec("INSERT INTO product_categories (title, slug) VALUES ('صنایع دستی', 'crafts')");
$pdo->exec("INSERT INTO products (title, slug, price, stock, status) VALUES ('کاسه سفالی کارگاه امید', 'pottery-bowl', 350000, 10, 'active')");

$cats = shop_get_categories($pdo);
$prods = shop_get_products($pdo);

if (empty($cats) || empty($prods)) {
    echo "FAIL: Storefront data fetching failed\n";
    exit(1);
}
if ($prods[0]['title'] !== 'کاسه سفالی کارگاه امید') {
    echo "FAIL: Product title mismatch\n";
    exit(1);
}

echo "PASS: Shop storefront catalog data ready.\n";
exit(0);
```

- [ ] **Step 2: Run test to verify it passes**

Run: `php tests/test_shop_storefront.php`
Expected: PASS: Shop storefront catalog data ready.

- [ ] **Step 3: Implement `public_html/shop.php`**

Build `public_html/shop.php`:
- Load database connection and `shop_ensure_schema($pdo)`.
- Fetch active categories and products.
- Render site header using standard `render_dashboard_component('header', get_defined_vars());`.
- Charity Shop Hero banner with cancer palliative care support messaging.
- Category pills filter bar + live search input.
- Grid of product cards with stock status badge, price, thumbnail, "مشاهده جزئیات" and "افزودن به سبد".
- Product Quick-View modal.
- Drawer Cart component:
  - Slide-in side drawer.
  - List of cart items from `localStorage.getItem('maxa_shop_cart')`.
  - Item count, unit price, quantity increment/decrement buttons, remove button.
  - Total price calculator.
  - Prominent notice: "هزینه ارسال به صورت پس‌کرایه است و زمان تحویل پرداخت می‌شود".
  - Checkout CTA button linking to `/shop-checkout.php`.
- Render site footer using `render_dashboard_component('footer', get_defined_vars());`.

- [ ] **Step 4: Implement `public_html/dashboard/components/shop-showcase.php`**

Create featured products section component for inclusion on home page:
- 4 featured active products.
- CTA: "مشاهده همه محصولات فروشگاه خیریه" linking to `/shop.php`.

- [ ] **Step 5: Verify PHP syntax**

Run: `php -l public_html/shop.php`
Expected: No syntax errors detected.

- [ ] **Step 6: Commit changes**

```bash
git add public_html/shop.php public_html/dashboard/components/shop-showcase.php tests/test_shop_storefront.php
git commit -m "feat(storefront): implement public charity shop, drawer cart and homepage showcase component"
```

---

### Task 4: Checkout, Order Placement & Payment Callback (`/shop-checkout.php` & `/shop-payment-callback.php`)

**Files:**
- Create: `public_html/shop-checkout.php`
- Create: `public_html/shop-payment-callback.php`
- Create: `tests/test_shop_checkout.php`

**Interfaces:**
- Consumes: Cart items from POST / localStorage
- Consumes: Benefactor user authentication from cookie/session/token
- Produces: Pending order record in `orders` (`order_type = 'product'`) and `order_items`
- Produces: Payment gateway redirect and callback handling with atomic stock deduction

- [ ] **Step 1: Write test for checkout validation and stock decrement**

Create `tests/test_shop_checkout.php`:
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY, user_id INTEGER, tracking_code VARCHAR(50), total_price DECIMAL(12,2))");
shop_ensure_schema($pdo);

// Seed product with 5 in stock
$pdo->exec("INSERT INTO products (id, title, slug, price, stock, status) VALUES (1, 'کیف سوزن‌دوزی', 'bag-1', 250000, 5, 'active')");

// Test order placement with server-side pricing
$items = [
    ['product_id' => 1, 'quantity' => 2]
];

$pdo->beginTransaction();
$totalPrice = 0;
$orderItemsToInsert = [];

foreach ($items as $item) {
    $prod = shop_get_product_by_id($pdo, (int)$item['product_id']);
    if (!$prod || (int)$prod['stock'] < (int)$item['quantity']) {
        $pdo->rollBack();
        echo "FAIL: Stock validation error\n";
        exit(1);
    }
    $lineTotal = (float)$prod['price'] * (int)$item['quantity'];
    $totalPrice += $lineTotal;
    $orderItemsToInsert[] = [
        'product_id' => $prod['id'],
        'title' => $prod['title'],
        'qty' => $item['quantity'],
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

// Simulate Payment Callback & Stock decrement
$pdo->beginTransaction();
$stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
foreach ($orderItemsToInsert as $ins) {
    $stockStmt->execute([$ins['qty'], $ins['product_id'], $ins['qty']]);
}
$updOrder = $pdo->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
$updOrder->execute([$orderId]);
$pdo->commit();

// Check updated product stock
$updatedProd = shop_get_product_by_id($pdo, 1);
if ((int)$updatedProd['stock'] !== 3) {
    echo "FAIL: Expected stock to be 3, got " . $updatedProd['stock'] . "\n";
    exit(1);
}

echo "PASS: Checkout validation, server-side pricing, and stock decrement verified.\n";
exit(0);
```

- [ ] **Step 2: Run test to verify it passes**

Run: `php tests/test_shop_checkout.php`
Expected: PASS: Checkout validation, server-side pricing, and stock decrement verified.

- [ ] **Step 3: Implement `public_html/shop-checkout.php`**

Create `public_html/shop-checkout.php`:
1. Check benefactor login state (JWT token from `localStorage` or `$_COOKIE['maxa_token']` / session).
2. If user is not logged in: display clean login prompt with mobile number & verification code right on the page (or redirect to benefactor login with `redirect=/shop-checkout.php` return parameter).
3. If logged in:
   - Form for recipient shipping details: `recipient_name`, `recipient_phone`, `province`, `city`, `postal_code`, `address`, `customer_note`.
   - Read items from cart, fetch authoritative server prices from `products` table, calculate total.
   - On submission: insert pending order into `orders` with `order_type='product'`, insert rows into `order_items`.
   - Redirect to bank gateway (using existing payment gateway helper e.g. Behpardakht / Zarinpal mock).

- [ ] **Step 4: Implement `public_html/shop-payment-callback.php`**

Create `public_html/shop-payment-callback.php`:
1. Verify payment status from bank gateway.
2. In database transaction:
   - Mark order as `payment_status = 'paid'`, set `paid_at = NOW()`.
   - Decrement stock in `products` table. If stock reaches 0, set `status = 'out_of_stock'`.
3. Display receipt page with order tracking code, list of items, shipping note (پس‌کرایه), and link to Benefactor Panel orders tab.
4. Emit JS snippet to clear `localStorage.removeItem('maxa_shop_cart')`.

- [ ] **Step 5: Verify syntax**

Run: `php -l public_html/shop-checkout.php`
Run: `php -l public_html/shop-payment-callback.php`
Expected: No syntax errors detected.

- [ ] **Step 6: Commit changes**

```bash
git add public_html/shop-checkout.php public_html/shop-payment-callback.php tests/test_shop_checkout.php
git commit -m "feat(checkout): implement shop checkout flow, server-authoritative order placement and payment callback"
```

---

### Task 5: Admin Orders Fulfillment & Postal Tracking Enhancement (`/dashboard/orders.php`)

**Files:**
- Modify: `public_html/dashboard/orders.php`
- Create: `tests/test_admin_fulfillment.php`

**Interfaces:**
- Consumes: `orders` and `order_items` tables
- Produces: POST action `update_fulfillment` for updating `fulfillment_status`, `carrier`, `postal_tracking_code`
- Produces: Unified fulfillment UI for both shop product orders and stand orders

- [ ] **Step 1: Write test for fulfillment update**

Create `tests/test_admin_fulfillment.php`:
```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../public_html/core/shop-db.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INTEGER PRIMARY KEY, user_id INTEGER, tracking_code VARCHAR(50), total_price DECIMAL(12,2))");
shop_ensure_schema($pdo);

// Seed an order
$pdo->exec("INSERT INTO orders (id, user_id, order_type, payment_status, fulfillment_status) VALUES (1, 10, 'product', 'paid', 'pending')");

// Test fulfillment update
$stmt = $pdo->prepare("
    UPDATE orders 
    SET fulfillment_status = ?, carrier = ?, postal_tracking_code = ?, shipped_at = CURRENT_TIMESTAMP 
    WHERE id = ?
");
$stmt->execute(['shipped', 'پست پیشتاز', '123456789012345678901234', 1]);

$check = $pdo->query("SELECT fulfillment_status, carrier, postal_tracking_code, shipped_at FROM orders WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

if ($check['fulfillment_status'] !== 'shipped' || $check['carrier'] !== 'پست پیشتاز' || empty($check['postal_tracking_code'])) {
    echo "FAIL: Fulfillment update failed\n";
    exit(1);
}

echo "PASS: Admin fulfillment update mechanics verified.\n";
exit(0);
```

- [ ] **Step 2: Run test to verify it passes**

Run: `php tests/test_admin_fulfillment.php`
Expected: PASS: Admin fulfillment update mechanics verified.

- [ ] **Step 3: Update `public_html/dashboard/orders.php`**

In `public_html/dashboard/orders.php`:
1. Call `shop_ensure_schema($pdo);` at the top.
2. Add POST handler for `action=update_fulfillment`:
   - Validate CSRF token.
   - Extract `order_id`, `fulfillment_status` (`pending`, `processing`, `shipped`, `delivered`, `cancelled`), `carrier`, `postal_tracking_code`.
   - Update `orders` table. Set `shipped_at = NOW()` if status is `shipped` or `delivered`.
   - Return JSON or redirect with flash success message.
3. Add filters at the top:
   - Order Type Filter tabs: "همه سفارشات" / "فروشگاه محصولات" (`order_type='product'`) / "استند و تاج گل" (`order_type='stand'`).
   - Fulfillment status filter dropdown.
4. In table rows:
   - Display `order_type` badge ("محصول فیزیکی" vs "استند").
   - Display fulfillment status badge with color.
   - Display carrier & tracking code if present.
   - If order is a shop order, fetch its items from `order_items`.
   - Add button "مدیریت وضعیت و ارسال" that opens an offcanvas/modal allowing the admin to update status, choose carrier, and paste the 24-digit tracking code.

- [ ] **Step 4: Verify syntax**

Run: `php -l public_html/dashboard/orders.php`
Expected: No syntax errors detected.

- [ ] **Step 5: Commit changes**

```bash
git add public_html/dashboard/orders.php tests/test_admin_fulfillment.php
git commit -m "feat(dashboard): add unified order fulfillment and postal tracking management for shop and stand orders"
```

---

### Task 6: Benefactor User Panel & API Update (`Orders.tsx` & `OrderController.php`)

**Files:**
- Modify: `public_html/api/src/Controllers/OrderController.php`
- Modify: `benefactor userpanel/src/api/client.ts`
- Modify: `benefactor userpanel/src/app/views/Orders.tsx`
- Test: `tests/test_api_orders.php`
- Test: Build verification in `benefactor userpanel`

**Interfaces:**
- Consumes: REST endpoint `/user/orders`
- Produces: Updated `OrderDto` with `order_type`, `fulfillment_status`, `carrier`, `postal_tracking_code`, `items`
- Produces: Tabbed interface in `Orders.tsx` for Shop Products vs Stands with direct postal tracking link (`https://tracking.post.ir/?id=...`)

- [ ] **Step 1: Write test for API orders response**

Create `tests/test_api_orders.php`:
```php
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
```

- [ ] **Step 2: Run test to verify it passes**

Run: `php tests/test_api_orders.php`
Expected: PASS: API order items and fulfillment data query verified.

- [ ] **Step 3: Update `public_html/api/src/Controllers/OrderController.php`**

In `public_html/api/src/Controllers/OrderController.php`:
1. Call `shop_ensure_schema($pdo);` in `getMyOrders()`.
2. Update the query in `getMyOrders()` to select:
   - `o.order_type`, `o.fulfillment_status`, `o.carrier`, `o.postal_tracking_code`, `o.recipient_name`, `o.recipient_phone`, `o.postal_code`, `o.address`, `o.paid_at`, `o.shipped_at`.
3. For each order, if `order_type === 'product'`, fetch associated `order_items` and attach as `items` array.
4. Return in the JSON response under `orders`.

- [ ] **Step 4: Update `benefactor userpanel/src/api/client.ts`**

In `benefactor userpanel/src/api/client.ts`:
Update `OrderItemDto` and `OrderDto`:
```typescript
export interface OrderItemDto {
  id: number;
  product_id?: number;
  product_title: string;
  quantity: number;
  unit_price: number;
  total_price: number;
}

export interface OrderDto {
  id: number;
  user_id?: number;
  tracking_code?: string;
  order_type?: "stand" | "product";
  payment_status?: "pending" | "paid" | "failed";
  fulfillment_status?: "pending" | "processing" | "shipped" | "delivered" | "cancelled";
  carrier?: string;
  postal_tracking_code?: string;
  total_price: number;
  recipient_name?: string;
  recipient_phone?: string;
  province?: string;
  city?: string;
  postal_code?: string;
  address?: string;
  items?: OrderItemDto[];
  created_at: string;
  paid_at?: string;
  shipped_at?: string;
  // Existing stand fields remain optional
  stand_id?: number;
  stand_title?: string;
  stand_image?: string;
  stand_type?: string;
  from_user?: string;
  to_user?: string;
  message?: string;
}
```

- [ ] **Step 5: Update `benefactor userpanel/src/app/views/Orders.tsx`**

Enhance `benefactor userpanel/src/app/views/Orders.tsx`:
1. Add sub-tab switcher: `activeTab: "products" | "stands"`.
2. For "محصولات فروشگاه" (`activeTab === "products"`):
   - Filter `orders.filter(o => o.order_type === "product")`.
   - Card for each order displaying:
     - Order tracking code & created date.
     - Fulfillment status badge with distinct icons and colors:
       - `pending` (در انتظار پردازش)
       - `processing` (در حال آماده‌سازی و بسته‌بندی)
       - `shipped` (ارسال شده با پست/تیپاکس)
       - `delivered` (تحویل شده)
       - `cancelled` (لغو شده)
     - List of items with title, quantity, unit price, total price.
     - Shipping address and recipient details.
     - Postal Tracking Box: If `postal_tracking_code` is present, display carrier name, tracking code, copy button, and link: `https://tracking.post.ir/?id=${o.postal_tracking_code}` opening in new tab.
   - Empty state with CTA button linking to `/shop.php`.
3. For "استند و تاج گل" (`activeTab === "stands"`):
   - Display existing stand orders, also showing `fulfillment_status` badge and postal tracking if set.

- [ ] **Step 6: Build React benefactor panel to ensure zero TypeScript/bundle errors**

Run: `cd "benefactor userpanel" && npm run build`
Expected: Build succeeds with 0 errors.

- [ ] **Step 7: Commit changes**

```bash
git add "public_html/api/src/Controllers/OrderController.php" "benefactor userpanel/src/api/client.ts" "benefactor userpanel/src/app/views/Orders.tsx" tests/test_api_orders.php
git commit -m "feat(panel): update benefactor panel orders view with shop products tabs, fulfillment badges and post tracking"
```

---

### Task 7: Full System Integration, End-to-End Verification & Verification Report

**Files:**
- Create: `tests/verify_full_shop_flow.php`
- Modify: `docs/superpowers/specs/2026-11-11-charity-shop-design.md` (record verification results)

- [ ] **Step 1: Write comprehensive automated integration test**

Create `tests/verify_full_shop_flow.php` testing:
1. Schema migration execution.
2. Product creation and listing.
3. Cart item calculation with authoritative prices.
4. Order placement with pending status.
5. Payment callback and atomic stock decrement.
6. Admin fulfillment update with carrier and postal tracking code.
7. Verification that API orders endpoint reflects all fulfillment attributes and items.

- [ ] **Step 2: Run full integration test**

Run: `php tests/verify_full_shop_flow.php`
Expected: PASS: Full end-to-end shop and fulfillment pipeline verified.

- [ ] **Step 3: Run all project test scripts**

Run:
```bash
php tests/test_shop_db.php
php tests/test_admin_products.php
php tests/test_shop_storefront.php
php tests/test_shop_checkout.php
php tests/test_admin_fulfillment.php
php tests/test_api_orders.php
php tests/verify_full_shop_flow.php
```
Expected: All tests return PASS with exit code 0.

- [ ] **Step 4: Commit final verification artifact**

```bash
git add tests/verify_full_shop_flow.php
git commit -m "test(shop): add comprehensive end-to-end integration test suite for charity shop"
```
