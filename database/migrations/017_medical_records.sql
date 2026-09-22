-- پرونده‌های پزشکی ثبت‌شده از فرم عمومی پذیرش بیمار
SET NAMES utf8mb4;

ALTER TABLE `otp_codes`
  MODIFY COLUMN `purpose` ENUM('donation_auth','login','medical_intake') NOT NULL DEFAULT 'donation_auth';

CREATE TABLE IF NOT EXISTS `medical_records` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `branch_id` INT UNSIGNED NOT NULL,
  `full_name` VARCHAR(200) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `age` TINYINT UNSIGNED NULL,
  `gender` ENUM('male','female','unspecified') NOT NULL DEFAULT 'unspecified',
  `province` VARCHAR(100) NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `cancer_type` VARCHAR(100) NULL,
  `diagnosis_status` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `status` ENUM('new','reviewing','contacted','closed') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medical_records_user` (`user_id`),
  KEY `idx_medical_records_branch_status` (`branch_id`, `status`),
  CONSTRAINT `fk_medical_records_user` FOREIGN KEY (`user_id`) REFERENCES `panel_users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_medical_records_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medical_record_files` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `record_id` BIGINT UNSIGNED NOT NULL,
  `path` VARCHAR(500) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `size` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_medical_record_files_record` (`record_id`),
  CONSTRAINT `fk_medical_record_files_record` FOREIGN KEY (`record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
