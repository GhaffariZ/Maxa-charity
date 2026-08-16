-- ============================================================================
-- Migration 009 — بازگردانی ساختار دیتابیس اخبار به حالت قبل از تغییرات
-- ----------------------------------------------------------------------------
-- این میگریشن ستون‌های لازم در جدول news و جدول‌های تگ‌ها (news_tags_map و news_tags)
-- را برای سازگاری کامل با کدهای قبلی بازگردانی و تضمین می‌کند.
-- ============================================================================

SET NAMES utf8mb4;

-- ۱. اطمینان از وجود ستون‌های subtitle، tags، keywords و read_time در جدول news
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `subtitle` VARCHAR(255) DEFAULT NULL AFTER `title`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `keywords` TEXT DEFAULT NULL AFTER `category_id`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `tags` VARCHAR(255) DEFAULT NULL AFTER `keywords`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `read_time` INT(11) DEFAULT 1 AFTER `viewed`;

-- ۲. ساخت جدول تگ‌های اخبار (news_tags) در صورت عدم وجود
CREATE TABLE IF NOT EXISTS `news_tags` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `name_fa` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۳. درج تگ‌های پیش‌فرض در صورت خالی بودن جدول
INSERT IGNORE INTO `news_tags` (`id`, `name`, `name_fa`) VALUES
(1, 'health', 'سلامت'),
(2, 'charity', 'خیریه'),
(3, 'medical', 'پزشکی'),
(4, 'events', 'رویدادها'),
(5, 'cancer', 'سرطان');

-- ۴. ساخت جدول اتصال چندبه‌چند برچسب‌های اخبار (news_tags_map)
CREATE TABLE IF NOT EXISTS `news_tags_map` (
  `news_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  PRIMARY KEY (`news_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- پایان میگریشن 009
-- ============================================================================
