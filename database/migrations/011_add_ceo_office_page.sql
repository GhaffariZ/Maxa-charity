-- Migration: Add CEO Office page to the pages table
-- This inserts the "مدیرعامل" page with the ceo-office component

INSERT INTO `pages` (`title`, `slug`, `components`, `status`, `branch_id`) VALUES
('مدیرعامل', 'CEOoffice', '["topbar","header","ceo-office","footer"]', 'published', 1)
ON DUPLICATE KEY UPDATE `components` = VALUES(`components`), `status` = 'published';
