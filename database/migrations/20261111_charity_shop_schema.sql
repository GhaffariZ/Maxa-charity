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

ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `order_type` ENUM('stand', 'product') NOT NULL DEFAULT 'stand',
    ADD COLUMN IF NOT EXISTS `payment_status` ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS `fulfillment_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS `carrier` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `postal_tracking_code` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `recipient_name` VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS `recipient_phone` VARCHAR(30) NULL,
    ADD COLUMN IF NOT EXISTS `province` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `city` VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS `postal_code` VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS `address` TEXT NULL,
    ADD COLUMN IF NOT EXISTS `customer_note` TEXT NULL,
    ADD COLUMN IF NOT EXISTS `paid_at` DATETIME NULL,
    ADD COLUMN IF NOT EXISTS `shipped_at` DATETIME NULL;
