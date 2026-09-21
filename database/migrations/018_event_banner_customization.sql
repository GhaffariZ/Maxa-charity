-- Add the temporary-dismiss option to installations that already ran 017_events.
SET @event_banner_dismissible_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'events' AND COLUMN_NAME = 'banner_dismissible'
);
SET @event_banner_dismissible_sql := IF(
  @event_banner_dismissible_exists = 0,
  'ALTER TABLE `events` ADD COLUMN `banner_dismissible` TINYINT(1) NOT NULL DEFAULT 1 AFTER `banner_theme`',
  'SELECT 1'
);
PREPARE event_banner_dismissible_stmt FROM @event_banner_dismissible_sql;
EXECUTE event_banner_dismissible_stmt;
DEALLOCATE PREPARE event_banner_dismissible_stmt;
