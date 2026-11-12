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
