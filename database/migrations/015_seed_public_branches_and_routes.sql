-- ============================================================================
-- Migration 015: public branch records and public patient page
-- Prerequisites: migrations 001 and 012
-- Safe to run more than once.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- These slugs are the canonical slugs used by the public map, branch list and
-- campaign-service environment keys. Keep them stable.
INSERT INTO `branches` (`name`, `province`, `city`, `slug`, `is_hq`, `status`) VALUES
  ('تهران', 'تهران', 'تهران', 'tehran-branch', 0, 'active'),
  ('اصفهان', 'اصفهان', 'اصفهان', 'esfahan-branch', 0, 'active'),
  ('مشهد', 'خراسان رضوی', 'مشهد', 'mashhad-branch', 0, 'active'),
  ('اهواز (خوزستان)', 'خوزستان', 'اهواز', 'ahvaz-branch', 0, 'active'),
  ('تبریز (آذربایجان شرقی)', 'آذربایجان شرقی', 'تبریز', 'tabriz-branch', 0, 'active'),
  ('قم', 'قم', 'قم', 'qom-branch', 0, 'active'),
  ('کاشان', 'اصفهان', 'کاشان', 'kashan-branch', 0, 'active'),
  ('مرکز ارتباطات و دورپزشکی', NULL, NULL, 'contact-center', 0, 'active'),
  ('مرکز رویش استعدادهای دانشجویی (CDST)', NULL, NULL, 'cdst', 0, 'active')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `province` = VALUES(`province`),
  `city` = VALUES(`city`),
  `is_hq` = 0,
  `status` = 'active';

-- Physical branches can receive stand orders.
INSERT INTO `branch_features` (`branch_id`, `feature`, `enabled`)
SELECT `id`, 'stands', 1
FROM `branches`
WHERE `slug` IN (
  'tehran-branch', 'esfahan-branch', 'mashhad-branch', 'ahvaz-branch',
  'tabriz-branch', 'qom-branch', 'kashan-branch'
)
ON DUPLICATE KEY UPDATE `enabled` = 1;

-- These are specialist centers, not stand-order fulfillment branches.
INSERT INTO `branch_features` (`branch_id`, `feature`, `enabled`)
SELECT `id`, 'stands', 0
FROM `branches`
WHERE `slug` IN ('contact-center', 'cdst')
ON DUPLICATE KEY UPDATE `enabled` = 0;

-- Seed the standard catalog for every physical branch that does not have it.
INSERT INTO `stands`
  (`branch_id`, `title`, `stand_type`, `image`, `unit_price`, `description`, `is_active`, `sort_order`)
SELECT b.`id`, d.`title`, d.`stand_type`, d.`image`, d.`unit_price`, d.`description`, 1, d.`sort_order`
FROM `branches` b
CROSS JOIN (
  SELECT 'استند تبریک و شادباش - طرح اول' AS `title`, 'congrats' AS `stand_type`,
         '/dashboard/components/event-cards/images/1-removebg-preview.png' AS `image`,
         300000 AS `unit_price`, 'با سفارش این استند، ضمن تبریک به عزیزانتان، حامی بیماران مبتلا به سرطان باشید.' AS `description`, 1 AS `sort_order`
  UNION ALL
  SELECT 'استند تبریک و شادباش - طرح دوم', 'congrats',
         '/dashboard/components/event-cards/images/2-removebg-preview.png',
         350000, 'شادی‌های خود را با مهربانی پیوند بزنید.', 2
  UNION ALL
  SELECT 'استند تسلیت و ابراز همدردی - طرح اول', 'condolence',
         '/dashboard/components/event-cards/images/3-removebg-preview.png',
         300000, 'تسلی‌بخش دل بازماندگان و امیدی برای بیماران مبتلا به سرطان.', 3
  UNION ALL
  SELECT 'استند تسلیت و ابراز همدردی - طرح دوم', 'condolence',
         '/dashboard/components/event-cards/images/4-removebg-preview.png',
         400000, 'با اهدای هزینه تاج گل به خیریه، نامی ماندگار از عزیز از دست رفته به یادگار بگذارید.', 4
) d
WHERE b.`slug` IN (
  'tehran-branch', 'esfahan-branch', 'mashhad-branch', 'ahvaz-branch',
  'tabriz-branch', 'qom-branch', 'kashan-branch'
)
AND NOT EXISTS (
  SELECT 1 FROM `stands` s
  WHERE s.`branch_id` = b.`id` AND s.`title` = d.`title`
);

-- Some production databases were created without the dump's patient page.
-- Only create it when missing; never overwrite content edited in the dashboard.
INSERT INTO `pages` (`branch_id`, `title`, `slug`, `components`, `status`)
SELECT b.`id`, 'راهنمای بیمار', 'patientoption',
       '["header","patientoption","hamrah","footer"]', 'published'
FROM `branches` b
WHERE b.`is_hq` = 1
  AND NOT EXISTS (
    SELECT 1 FROM `pages` p
    WHERE p.`branch_id` = b.`id` AND p.`slug` = 'patientoption'
  )
LIMIT 1;

SET FOREIGN_KEY_CHECKS = 1;
