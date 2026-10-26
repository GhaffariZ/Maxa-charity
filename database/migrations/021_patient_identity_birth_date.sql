-- Apply after 017_medical_records.sql, before deploying the updated intake API.
-- Nullable identity/date columns preserve existing medical records.
SET NAMES utf8mb4;

ALTER TABLE `medical_records`
  ADD COLUMN `identity_code` VARCHAR(12) NULL AFTER `phone`,
  ADD COLUMN `is_foreign` TINYINT(1) NOT NULL DEFAULT 0 AFTER `identity_code`,
  ADD COLUMN `birth_date` DATE NULL COMMENT 'Gregorian date; entered in the Persian calendar' AFTER `is_foreign`;
