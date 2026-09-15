-- ============================================================================
-- مهاجرت 015: احراز هویت پیامکی (OTP)، اتصال CRM و انطباق با الزامات شاپرک
-- ----------------------------------------------------------------------------
-- ۱. ارتقای جدول panel_users برای ورود با شماره موبایل و نال‌پذیر شدن ایمیل/پسورد
-- ۲. افزودن فیلدهای کد ملی و شناسه CRM به جدول user_profiles
-- ۳. ایجاد جدول otp_codes جهت چرخه حیات کدهای یک‌بار مصرف
-- ============================================================================

SET NAMES utf8mb4;

-- ۱. ارتقای جدول panel_users
ALTER TABLE `panel_users`
  MODIFY COLUMN `email` VARCHAR(255) NULL,
  MODIFY COLUMN `password_hash` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `phone_verified_at` DATETIME NULL AFTER `email_verified_at`;

ALTER TABLE `panel_users`
  ADD UNIQUE INDEX IF NOT EXISTS `uq_panel_users_phone` (`phone`);

-- ۲. ارتقای جدول user_profiles
ALTER TABLE `user_profiles`
  ADD COLUMN IF NOT EXISTS `national_code` VARCHAR(10) NULL AFTER `last_name`,
  ADD COLUMN IF NOT EXISTS `crm_id` VARCHAR(100) NULL AFTER `donor_tier_id`,
  ADD COLUMN IF NOT EXISTS `crm_synced_at` DATETIME NULL AFTER `crm_id`;

ALTER TABLE `user_profiles`
  ADD INDEX IF NOT EXISTS `idx_user_profiles_national_code` (`national_code`),
  ADD INDEX IF NOT EXISTS `idx_user_profiles_crm_id` (`crm_id`);

-- ۳. جدول کدهای یک‌بار مصرف (OTP)
CREATE TABLE IF NOT EXISTS `otp_codes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `phone` VARCHAR(20) NOT NULL,
    `code_hash` VARCHAR(255) NOT NULL,
    `purpose` ENUM('donation_auth', 'login') NOT NULL DEFAULT 'donation_auth',
    `ip_address` VARCHAR(45) NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `consumed_at` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_otp_phone_purpose_expires` (`phone`, `purpose`, `expires_at`),
    INDEX `idx_otp_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
