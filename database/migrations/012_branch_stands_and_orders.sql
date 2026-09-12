-- ============================================================================
-- مهاجرت 012: پیاده‌سازی سامانه هوشمند استندهای اختصاصی شعب و ایزولاسیون سفارشات در مکسا
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ۱. افزودن فیلد استان و شهر تحت پوشش به جدول branches
ALTER TABLE `branches` 
  ADD COLUMN IF NOT EXISTS `province` VARCHAR(100) NULL AFTER `name`,
  ADD COLUMN IF NOT EXISTS `city` VARCHAR(100) NULL AFTER `province`;

-- بروزرسانی استان شعب موجود
UPDATE `branches` SET `province` = 'تهران', `city` = 'تهران' WHERE `slug` = 'tehran-branch' OR `name` LIKE '%تهران%';
UPDATE `branches` SET `province` = 'اصفهان', `city` = 'اصفهان' WHERE `slug` = 'esfahan-branch' OR `name` LIKE '%اصفهان%';
UPDATE `branches` SET `province` = 'خراسان رضوی', `city` = 'مشهد' WHERE `slug` = 'mashhad-branch' OR `name` LIKE '%مشهد%';
UPDATE `branches` SET `province` = 'خوزستان', `city` = 'اهواز' WHERE `slug` = 'ahvaz-branch' OR `name` LIKE '%اهواز%';
UPDATE `branches` SET `province` = 'آذربایجان شرقی', `city` = 'تبریز' WHERE `slug` = 'tabriz-branch' OR `name` LIKE '%تبریز%';
UPDATE `branches` SET `province` = 'قم', `city` = 'قم' WHERE `slug` = 'qom-branch' OR `name` LIKE '%قم%';
UPDATE `branches` SET `province` = 'کرمان', `city` = 'کرمان' WHERE `slug` = 'kerman-branch' OR `name` LIKE '%کرمان%';

-- ۲. ساخت جدول استندهای اختصاصی شعب
CREATE TABLE IF NOT EXISTS `stands` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(191) NOT NULL,
  `stand_type` ENUM('congrats', 'condolence') NOT NULL DEFAULT 'congrats',
  `image` VARCHAR(255) NOT NULL,
  `unit_price` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stands_branch` (`branch_id`),
  KEY `idx_stands_active` (`is_active`),
  CONSTRAINT `fk_stands_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۳. اصلاح جدول orders جهت ذخیره دقیق انتساب به شعبه و آدرس ساختاریافته
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED NULL AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `stand_id` INT UNSIGNED NULL AFTER `branch_id`,
  ADD COLUMN IF NOT EXISTS `province` VARCHAR(100) NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `city` VARCHAR(100) NULL AFTER `province`;

ALTER TABLE `orders` 
  ADD KEY IF NOT EXISTS `idx_orders_branch` (`branch_id`),
  ADD KEY IF NOT EXISTS `idx_orders_stand` (`stand_id`);

-- ۴. فعال‌سازی فیچر stands برای شعب نمونه به جز دفتر مرکزی (id=1 / is_hq=1)
-- ستاد مرکزی نباید فیچر stands را داشته باشد.
INSERT IGNORE INTO `branch_features` (`branch_id`, `feature`, `enabled`)
SELECT `id`, 'stands', 1 FROM `branches` WHERE `is_hq` = 0 AND `status` = 'active';

-- ۵. ثبت چند طرح اولیه استند برای شعب فعال که استند دارند
INSERT INTO `stands` (`branch_id`, `title`, `stand_type`, `image`, `unit_price`, `description`, `is_active`, `sort_order`)
SELECT b.`id`, 'استند تبریک و شادباش - طرح اول', 'congrats', '/dashboard/components/event-cards/images/1-removebg-preview.png', 300000, 'با سفارش این استند، ضمن تبریک به عزیزانتان، حامی بیماران مبتلا به سرطان باشید.', 1, 1
FROM `branches` b WHERE b.`is_hq` = 0 AND b.`status` = 'active'
AND NOT EXISTS (SELECT 1 FROM `stands` s WHERE s.`branch_id` = b.`id` AND s.`title` = 'استند تبریک و شادباش - طرح اول');

INSERT INTO `stands` (`branch_id`, `title`, `stand_type`, `image`, `unit_price`, `description`, `is_active`, `sort_order`)
SELECT b.`id`, 'استند تبریک و شادباش - طرح دوم', 'congrats', '/dashboard/components/event-cards/images/2-removebg-preview.png', 350000, 'شادی‌های خود را با مهربانی پیوند بزنید.', 1, 2
FROM `branches` b WHERE b.`is_hq` = 0 AND b.`status` = 'active'
AND NOT EXISTS (SELECT 1 FROM `stands` s WHERE s.`branch_id` = b.`id` AND s.`title` = 'استند تبریک و شادباش - طرح دوم');

INSERT INTO `stands` (`branch_id`, `title`, `stand_type`, `image`, `unit_price`, `description`, `is_active`, `sort_order`)
SELECT b.`id`, 'استند تسلیت و ابراز همدردی - طرح اول', 'condolence', '/dashboard/components/event-cards/images/3-removebg-preview.png', 300000, 'تسلی بخش دل بازماندگان و امیدی برای بیماران سرطانی.', 1, 3
FROM `branches` b WHERE b.`is_hq` = 0 AND b.`status` = 'active'
AND NOT EXISTS (SELECT 1 FROM `stands` s WHERE s.`branch_id` = b.`id` AND s.`title` = 'استند تسلیت و ابراز همدردی - طرح اول');

INSERT INTO `stands` (`branch_id`, `title`, `stand_type`, `image`, `unit_price`, `description`, `is_active`, `sort_order`)
SELECT b.`id`, 'استند تسلیت و ابراز همدردی - طرح دوم', 'condolence', '/dashboard/components/event-cards/images/4-removebg-preview.png', 400000, 'با اهدای هزینه تاج گل به خیریه، نامی ماندگار از عزیز از دست رفته به یادگار بگذارید.', 1, 4
FROM `branches` b WHERE b.`is_hq` = 0 AND b.`status` = 'active'
AND NOT EXISTS (SELECT 1 FROM `stands` s WHERE s.`branch_id` = b.`id` AND s.`title` = 'استند تسلیت و ابراز همدردی - طرح دوم');

SET FOREIGN_KEY_CHECKS = 1;
