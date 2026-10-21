-- Migration 022: Add annual-reports and financial-reports to pages table
-- Inserts the "گزارش‌های سالانه" and "صورت‌های مالی" pages with their components

INSERT INTO `pages` (`title`, `slug`, `components`, `status`, `branch_id`) VALUES
('گزارش‌های سالانه', 'annual-reports', '["topbar","header","annual-reports","footer"]', 'published', 1),
('صورت‌های مالی', 'financial-reports', '["topbar","header","financial-reports","footer"]', 'published', 1)
ON DUPLICATE KEY UPDATE `components` = VALUES(`components`), `status` = 'published';
