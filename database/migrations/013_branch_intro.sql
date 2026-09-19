-- ============================================================================
--  Migration 013 — جدول معرفی شعبه (Branch Introduction & Gallery)
-- ----------------------------------------------------------------------------
--  امکان ثبت متن معرفی، آدرس دقیق، اطلاعات تماس و گالری تصاویر برای هر شعبه
-- ============================================================================

CREATE TABLE IF NOT EXISTS `branch_intros` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `branch_id` INT NOT NULL UNIQUE,
  `title` VARCHAR(255) NULL,
  `intro_text` MEDIUMTEXT NULL,
  `address` TEXT NULL,
  `phone` VARCHAR(100) NULL,
  `working_hours` VARCHAR(150) NULL,
  `email` VARCHAR(150) NULL,
  `images` LONGTEXT NULL, -- آرایه JSON از مسیرهای تصاویر
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_branch_intros_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
