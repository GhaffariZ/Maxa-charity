-- Migration 008 — ایجاد جدول کمکی برای برچسب‌های چندگانه اخبار
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `news_tags_map` (
  `news_id` INT NOT NULL,
  `tag_id` INT NOT NULL,
  PRIMARY KEY (`news_id`, `tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
