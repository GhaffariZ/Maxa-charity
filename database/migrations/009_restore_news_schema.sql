-- ============================================================================
-- Migration 009 — بازگردانی ساختار دیتابیس اخبار به حالت اصلی و اولیه
-- ----------------------------------------------------------------------------
-- این میگریشن ستون‌های لازم در جدول news و جدول‌های تگ‌ها (news_tags و news_tags_map)
-- را دقیقاً مطابق با اسکیما و کدهای اصلی دیتابیس بازگردانی می‌کند.
-- ============================================================================

SET NAMES utf8mb4;

-- ۱. اطمینان از وجود ستون‌های subtitle، keywords، tags و read_time در جدول news
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `subtitle` VARCHAR(255) DEFAULT NULL AFTER `title`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `keywords` TEXT DEFAULT NULL AFTER `category_id`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `tags` VARCHAR(255) DEFAULT NULL AFTER `keywords`;
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `read_time` INT(11) DEFAULT 1 AFTER `viewed`;

-- ۲. ساخت جدول تگ‌های اخبار (news_tags) دقیقاً مطابق اسکیمای اصلی
CREATE TABLE IF NOT EXISTS `news_tags` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ۳. درج تگ‌های اصلی و اولیه
INSERT IGNORE INTO `news_tags` (`id`, `name`) VALUES
(1, 'medical'),
(2, 'palliative_care'),
(3, 'technology'),
(4, 'economic'),
(5, 'international'),
(6, 'cultural'),
(7, 'social'),
(8, 'education'),
(9, 'research');

-- ۴. ساخت جدول اتصال چندبه‌چند برچسب‌های اخبار (news_tags_map)
CREATE TABLE IF NOT EXISTS `news_tags_map` (
  `news_id` INT(11) NOT NULL,
  `tag_id` INT(11) NOT NULL,
  PRIMARY KEY (`news_id`, `tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- پایان میگریشن 009
-- ============================================================================
