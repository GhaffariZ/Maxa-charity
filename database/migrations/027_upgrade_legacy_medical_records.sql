-- Run after 017_medical_records.sql on the application database (MySQL/MariaDB).
-- Upgrade the original mobile/documents intake table without discarding its rows or documents.
SET NAMES utf8mb4;
SET @medical_schema = DATABASE();
SET @medical_db = CONCAT('`', REPLACE(@medical_schema, '`', '``'), '`.');
SET @medical_old_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = CONCAT_WS(',', @@SESSION.sql_mode, 'STRICT_ALL_TABLES');

SET @medical_legacy = (
  SELECT COUNT(*) = 1 FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @medical_schema AND TABLE_NAME = 'medical_records' AND COLUMN_NAME = 'mobile'
) AND NOT EXISTS (
  SELECT 1 FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @medical_schema AND TABLE_NAME = 'medical_records' AND COLUMN_NAME = 'phone'
);

-- Retain an in-database snapshot; rerunning a failed upgrade never replaces the first snapshot.
SET @medical_sql = IF(@medical_legacy, CONCAT('CREATE TABLE IF NOT EXISTS ', @medical_db,
  '`medical_records_before_027` LIKE ', @medical_db, '`medical_records`'), 'SELECT 1');
PREPARE medical_upgrade FROM @medical_sql;
EXECUTE medical_upgrade;
DEALLOCATE PREPARE medical_upgrade;
SET @medical_sql = IF(@medical_legacy, CONCAT('INSERT IGNORE INTO ', @medical_db,
  '`medical_records_before_027` SELECT * FROM ', @medical_db, '`medical_records`'), 'SELECT 1');
PREPARE medical_upgrade FROM @medical_sql;
EXECUTE medical_upgrade;
DEALLOCATE PREPARE medical_upgrade;

-- Keep nullable historical owners and the original documents column. Never truncate legacy fields.
SET @medical_legacy_alter = 'MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
MODIFY COLUMN user_id BIGINT UNSIGNED NULL,
CHANGE COLUMN mobile phone VARCHAR(20) NOT NULL,
ADD COLUMN branch_id INT UNSIGNED NULL AFTER user_id,
ADD COLUMN status ENUM(''new'',''reviewing'',''contacted'',''closed'') NOT NULL DEFAULT ''new'',
ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD KEY idx_medical_records_branch_status (branch_id,status)';
SET @medical_sql = IF(@medical_legacy, CONCAT('ALTER TABLE ', @medical_db, '`medical_records` ',
  @medical_legacy_alter), 'SELECT 1');
PREPARE medical_upgrade FROM @medical_sql;
EXECUTE medical_upgrade;
DEALLOCATE PREPARE medical_upgrade;

-- The legacy public intake was central, like MedicalRecordController::hqBranchId(); never infer a branch from the patient's city.
SET @medical_sql = CONCAT('UPDATE ', @medical_db, '`medical_records` SET branch_id = (SELECT MIN(id) FROM ',
  @medical_db, '`branches` WHERE is_hq = 1 AND status = ''active'') WHERE branch_id IS NULL');
PREPARE medical_upgrade FROM @medical_sql;
EXECUTE medical_upgrade;
DEALLOCATE PREPARE medical_upgrade;
SET @medical_sql = CONCAT('ALTER TABLE ', @medical_db, '`medical_records` MODIFY COLUMN branch_id INT UNSIGNED NOT NULL');
PREPARE medical_upgrade FROM @medical_sql;
EXECUTE medical_upgrade;
DEALLOCATE PREPARE medical_upgrade;

SET @medical_has_branch_fk = EXISTS (
  SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = @medical_schema AND TABLE_NAME = 'medical_records'
    AND COLUMN_NAME = 'branch_id' AND REFERENCED_TABLE_NAME = 'branches'
);
SET @medical_sql = IF(@medical_has_branch_fk, 'SELECT 1', CONCAT('ALTER TABLE ', @medical_db,
  '`medical_records` ADD CONSTRAINT fk_medical_records_branch FOREIGN KEY (branch_id) REFERENCES ', @medical_db, '`branches` (id)'));
PREPARE medical_upgrade FROM @medical_sql;
EXECUTE medical_upgrade;
DEALLOCATE PREPARE medical_upgrade;

-- The old signed INT primary key prevented migration 017 from creating this child table.
SET @medical_sql = CONCAT('CREATE TABLE IF NOT EXISTS ', @medical_db, '`medical_record_files` (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  record_id BIGINT UNSIGNED NOT NULL,
  path VARCHAR(500) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100) NOT NULL,
  size INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_medical_record_files_record (record_id),
  CONSTRAINT fk_medical_record_files_record FOREIGN KEY (record_id) REFERENCES ', @medical_db,
  '`medical_records` (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
PREPARE medical_upgrade FROM @medical_sql;
EXECUTE medical_upgrade;
DEALLOCATE PREPARE medical_upgrade;
SET SESSION sql_mode = @medical_old_mode;
