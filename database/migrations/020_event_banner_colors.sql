-- Allow HQ editors to define a complete banner color system.
SET @event_banner_colors_sql := IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'banner_background') = 0,
  'ALTER TABLE `events` ADD COLUMN `banner_background` CHAR(7) NOT NULL DEFAULT ''#007b7a'' AFTER `banner_dismissible`, ADD COLUMN `banner_text_color` CHAR(7) NOT NULL DEFAULT ''#ffffff'' AFTER `banner_background`, ADD COLUMN `banner_accent_color` CHAR(7) NOT NULL DEFAULT ''#f4a61e'' AFTER `banner_text_color`',
  'SELECT 1'
);
PREPARE event_banner_colors_stmt FROM @event_banner_colors_sql;
EXECUTE event_banner_colors_stmt;
DEALLOCATE PREPARE event_banner_colors_stmt;
