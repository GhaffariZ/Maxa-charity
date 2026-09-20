-- ============================================================================
--  Migration 016 — افزودن مختصات نقشه به جدول شعب (Branch Map Coordinates)
-- ----------------------------------------------------------------------------
--  ذخیره مختصات X و Y نشانگر هر شعبه روی نقشه وکتور ایران (سیستم مختصات SVG)
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE `branches`
  ADD COLUMN IF NOT EXISTS `map_x` FLOAT NULL DEFAULT NULL AFTER `city`,
  ADD COLUMN IF NOT EXISTS `map_y` FLOAT NULL DEFAULT NULL AFTER `map_x`;

-- تنظیم مختصات پیش‌فرض برای شعب موجود بر مبنای نقشه SVG
UPDATE `branches` SET `map_x` = 475.0, `map_y` = 283.0 WHERE `slug` = 'tehran-branch' OR `name` LIKE '%تهران%';
UPDATE `branches` SET `map_x` = 510.0, `map_y` = 468.0 WHERE `slug` = 'esfahan-branch' OR (`name` LIKE '%اصفهان%' AND `name` NOT LIKE '%کاشان%');
UPDATE `branches` SET `map_x` = 472.0, `map_y` = 398.0 WHERE `slug` = 'kashan-branch' OR `name` LIKE '%کاشان%';
UPDATE `branches` SET `map_x` = 905.0, `map_y` = 263.0 WHERE `slug` = 'mashhad-branch' OR `name` LIKE '%مشهد%';
UPDATE `branches` SET `map_x` = 315.0, `map_y` = 573.0 WHERE `slug` = 'ahvaz-branch' OR `name` LIKE '%اهواز%';
UPDATE `branches` SET `map_x` = 185.0, `map_y` = 100.0 WHERE `slug` = 'tabriz-branch' OR `name` LIKE '%تبریز%';
UPDATE `branches` SET `map_x` = 430.0, `map_y` = 343.0 WHERE `slug` = 'qom-branch' OR `name` LIKE '%قم%';
