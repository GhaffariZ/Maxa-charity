-- ============================================================================
-- مهاجرت 014: ایجاد نقش پیش‌فرض سیستمی «مسئول مالی» در مکسا
-- ----------------------------------------------------------------------------
-- این نقش دسترسی انحصاری به بخش مالی (گزارش‌ها و خروجی اکسل) دارد:
-- permissions: ["financial"]
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ۱. ثبت نقش «مسئول مالی» برای ستاد مرکزی (id = 1) در صورت عدم وجود
INSERT INTO `dashboard_roles` (`branch_id`, `name`, `permissions`, `is_preset`)
SELECT 1, 'مسئول مالی', JSON_ARRAY('financial'), 1
WHERE NOT EXISTS (
  SELECT 1 FROM `dashboard_roles` WHERE `branch_id` = 1 AND `name` = 'مسئول مالی'
);

-- ۲. ثبت نقش «مسئول مالی» برای کلیه شعب فعال دیگر در صورت عدم وجود
INSERT INTO `dashboard_roles` (`branch_id`, `name`, `permissions`, `is_preset`)
SELECT b.`id`, 'مسئول مالی', JSON_ARRAY('financial'), 1
FROM `branches` b
WHERE b.`status` = 'active'
AND NOT EXISTS (
  SELECT 1 FROM `dashboard_roles` r WHERE r.`branch_id` = b.`id` AND r.`name` = 'مسئول مالی'
);

SET FOREIGN_KEY_CHECKS = 1;
