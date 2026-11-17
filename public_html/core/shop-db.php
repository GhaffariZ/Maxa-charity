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
            "created_at DATETIME NULL",
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

/**
 * Safely obtain a PDO database connection without dying if offline
 */
function shop_get_pdo(): ?PDO {
    if (isset($GLOBALS['pdo']) && ($GLOBALS['pdo'] instanceof PDO)) {
        return $GLOBALS['pdo'];
    }
    $cfgPath = __DIR__ . '/db-config.php';
    if (file_exists($cfgPath)) {
        try {
            $DB = require $cfgPath;
            $host = $DB['host'] ?? 'localhost';
            $name = $DB['name'] ?? '';
            $user = $DB['user'] ?? '';
            $pass = $DB['pass'] ?? '';
            $charset = $DB['charset'] ?? 'utf8mb4';
            $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, $user, $pass, $options);
            $GLOBALS['pdo'] = $pdo;
            return $pdo;
        } catch (Throwable $e) {
            return null;
        }
    }
    return null;
}

/**
 * Fallback demo categories for instant storefront display
 */
function shop_get_demo_categories(): array {
    return [
        ['id' => 1, 'title' => 'ماگ و ملزومات اداری', 'slug' => 'mugs-stationery', 'sort_order' => 1, 'status' => 'active'],
        ['id' => 2, 'title' => 'صنایع دستی و کارگاه امید', 'slug' => 'handicrafts', 'sort_order' => 2, 'status' => 'active'],
        ['id' => 3, 'title' => 'محصولات فرهنگی و هنری', 'slug' => 'cultural-art', 'sort_order' => 3, 'status' => 'active'],
        ['id' => 4, 'title' => 'نشان‌ها و یادبود حامیان', 'slug' => 'pins-badges', 'sort_order' => 4, 'status' => 'active'],
    ];
}

/**
 * Fallback demo products for instant storefront display
 */
function shop_get_demo_products(): array {
    return [
        [
            'id' => 1,
            'category_id' => 1,
            'category_title' => 'ماگ و ملزومات اداری',
            'title' => 'ماگ سرامیکی امید و آرامش مکسا',
            'slug' => 'ceramic-mug-hope',
            'description' => 'ماگ سرامیکی اختصاصی مکسا با لعاب باکیفیت و دوام بالا؛ تمامی عواید حاصل از خرید این ماگ مستقیماً صرف تامین دارو و تجهیزات مراقبت تسکینی بیماران مبتلا به سرطان می‌شود.',
            'price' => 185000,
            'stock' => 25,
            'image_url' => '',
            'status' => 'active',
        ],
        [
            'id' => 2,
            'category_id' => 3,
            'category_title' => 'محصولات فرهنگی و هنری',
            'title' => 'تقویم رومیزی و یادداشت همدلی',
            'slug' => 'desk-calendar-notepad',
            'description' => 'تقویم رومیزی با طرح‌های گرافیکی الهام‌بخش به همراه دفترچه یادداشت روزانه؛ هدیه‌ای معنادار برای میز کار شما و یادآور امید به زندگی.',
            'price' => 120000,
            'stock' => 40,
            'image_url' => '',
            'status' => 'active',
        ],
        [
            'id' => 3,
            'category_id' => 2,
            'category_title' => 'صنایع دستی و کارگاه امید',
            'title' => 'کاسه سفالی کارگاه توانمندسازی',
            'slug' => 'pottery-bowl-empowerment',
            'description' => 'دست‌سازه‌های هنری تولید شده در کارگاه‌های حمایتی خانواده‌های تحت پوشش مکسا؛ نماد مهر، خودکفایی و اصالت هنر ایرانی.',
            'price' => 350000,
            'stock' => 15,
            'image_url' => '',
            'status' => 'active',
        ],
        [
            'id' => 4,
            'category_id' => 4,
            'category_title' => 'نشان‌ها و یادبود حامیان',
            'title' => 'پیکسل فلزی و جاکلیدی حامیان سلامت',
            'slug' => 'health-ambassador-badge',
            'description' => 'نشان سینه فلزی با روکش شفاف و نماد روبان امید؛ برای همراهی و گسترش فرهنگ مراقبت حمایتی و تسکینی در جامعه.',
            'price' => 45000,
            'stock' => 60,
            'image_url' => '',
            'status' => 'active',
        ],
        [
            'id' => 5,
            'category_id' => 2,
            'category_title' => 'صنایع دستی و کارگاه امید',
            'title' => 'شمع دست‌ساز با رایحه آرامش',
            'slug' => 'handmade-scented-candle',
            'description' => 'شمع دست‌ساز با موم گیاهی سویا و سازگار با محیط‌زیست؛ با روشن کردن این شمع گرمای امید را به خانه بیماران تحت مراقبت هدیه دهید.',
            'price' => 140000,
            'stock' => 20,
            'image_url' => '',
            'status' => 'active',
        ],
        [
            'id' => 6,
            'category_id' => 3,
            'category_title' => 'محصولات فرهنگی و هنری',
            'title' => 'ساک پارچه‌ای کتان حامی طبیعت',
            'slug' => 'cotton-tote-bag',
            'description' => 'ساک پارچه‌ای کتان دوخته‌شده با الیاف طبیعی و ماندگار؛ جایگزین کیسه‌های پلاستیکی با طرح اختصاصی پویش مراقبت در منزل مکسا.',
            'price' => 95000,
            'stock' => 30,
            'image_url' => '',
            'status' => 'active',
        ],
    ];
}

/**
 * Seed initial catalog categories & products into database if empty
 */
function shop_seed_default_catalog(PDO $pdo): void {
    try {
        $pCount = (int)$pdo->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
        if ($pCount > 0) {
            return;
        }

        $catCount = (int)$pdo->query("SELECT COUNT(*) FROM `product_categories`")->fetchColumn();
        if ($catCount === 0) {
            $categories = [
                ['ماگ و ملزومات اداری', 'mugs-stationery', 1],
                ['صنایع دستی و کارگاه امید', 'handicrafts', 2],
                ['محصولات فرهنگی و هنری', 'cultural-art', 3],
                ['نشان‌ها و یادبود حامیان', 'pins-badges', 4],
            ];
            $catStmt = $pdo->prepare("INSERT INTO `product_categories` (`title`, `slug`, `sort_order`, `status`) VALUES (?, ?, ?, 'active')");
            foreach ($categories as $cat) {
                $catStmt->execute($cat);
            }
        }

        $catRows = $pdo->query("SELECT `slug`, `id` FROM `product_categories`")->fetchAll(PDO::FETCH_KEY_PAIR);
        $mugsId = (int)($catRows['mugs-stationery'] ?? 1);
        $craftsId = (int)($catRows['handicrafts'] ?? 2);
        $cultureId = (int)($catRows['cultural-art'] ?? 3);
        $pinsId = (int)($catRows['pins-badges'] ?? 4);

        $initialProducts = [
            [
                'category_id' => $mugsId,
                'title' => 'ماگ سرامیکی امید و آرامش مکسا',
                'slug' => 'ceramic-mug-hope',
                'description' => 'ماگ سرامیکی اختصاصی مکسا با لعاب باکیفیت و دوام بالا؛ تمامی عواید حاصل از خرید این ماگ مستقیماً صرف تامین دارو و تجهیزات مراقبت تسکینی بیماران مبتلا به سرطان می‌شود.',
                'price' => 185000,
                'stock' => 25,
                'image_url' => '',
            ],
            [
                'category_id' => $cultureId,
                'title' => 'تقویم رومیزی و یادداشت همدلی',
                'slug' => 'desk-calendar-notepad',
                'description' => 'تقویم رومیزی با طرح‌های گرافیکی الهام‌بخش به همراه دفترچه یادداشت روزانه؛ هدیه‌ای معنادار برای میز کار شما و یادآور امید به زندگی.',
                'price' => 120000,
                'stock' => 40,
                'image_url' => '',
            ],
            [
                'category_id' => $craftsId,
                'title' => 'کاسه سفالی کارگاه توانمندسازی',
                'slug' => 'pottery-bowl-empowerment',
                'description' => 'دست‌سازه‌های هنری تولید شده در کارگاه‌های حمایتی خانواده‌های تحت پوشش مکسا؛ نماد مهر، خودکفایی و اصالت هنر ایرانی.',
                'price' => 350000,
                'stock' => 15,
                'image_url' => '',
            ],
            [
                'category_id' => $pinsId,
                'title' => 'پیکسل فلزی و جاکلیدی حامیان سلامت',
                'slug' => 'health-ambassador-badge',
                'description' => 'نشان سینه فلزی با روکش شفاف و نماد روبان امید؛ برای همراهی و گسترش فرهنگ مراقبت حمایتی و تسکینی در جامعه.',
                'price' => 45000,
                'stock' => 60,
                'image_url' => '',
            ],
            [
                'category_id' => $craftsId,
                'title' => 'شمع دست‌ساز با رایحه آرامش',
                'slug' => 'handmade-scented-candle',
                'description' => 'شمع دست‌ساز با موم گیاهی سویا و سازگار با محیط‌زیست؛ با روشن کردن این شمع گرمای امید را به خانه بیماران تحت مراقبت هدیه دهید.',
                'price' => 140000,
                'stock' => 20,
                'image_url' => '',
            ],
            [
                'category_id' => $cultureId,
                'title' => 'ساک پارچه‌ای کتان حامی طبیعت',
                'slug' => 'cotton-tote-bag',
                'description' => 'ساک پارچه‌ای کتان دوخته‌شده با الیاف طبیعی و ماندگار؛ جایگزین کیسه‌های پلاستیکی با طرح اختصاصی پویش مراقبت در منزل مکسا.',
                'price' => 95000,
                'stock' => 30,
                'image_url' => '',
            ],
        ];

        $prodStmt = $pdo->prepare("
            INSERT INTO `products` (`category_id`, `title`, `slug`, `description`, `price`, `stock`, `image_url`, `status`)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        foreach ($initialProducts as $p) {
            $prodStmt->execute([
                $p['category_id'],
                $p['title'],
                $p['slug'],
                $p['description'],
                $p['price'],
                $p['stock'],
                $p['image_url'],
            ]);
        }
    } catch (Throwable $e) {}
}
