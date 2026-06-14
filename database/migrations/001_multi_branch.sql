-- ============================================================================
--  Migration 001 — سامانه‌ی چندشعبه‌ای (Multi-Branch) + احراز هویت پنل مدیریت
-- ----------------------------------------------------------------------------
--  این مهاجرت روی دیتابیس موجود امن است:
--    1) جدول‌های جدید را می‌سازد (branches, dashboard_users, dashboard_roles,
--       branch_features) و ستون branch_id را به جدول‌های محتوا اضافه می‌کند.
--    2) یک ردیف «دفتر مرکزی» (HQ) می‌سازد و تمام داده‌های موجود را به آن نسبت
--       می‌دهد (backfill) — محتوای فعلی متعلق به شعبه‌ی مرکزی است.
--    3) نقش‌های پیش‌فرض (مثل «خبرنگار») را برای دفتر مرکزی seed می‌کند.
--
--  نحوه‌ی اجرا:
--    mysql -u USER -p DBNAME < database/migrations/001_multi_branch.sql
--
--  نکته: ساخت ادمین مرکزی (نام کاربری/رمز) از طریق اسکریپت امن CLI انجام می‌شود:
--    php database/seed-admin.php
--  (این فایل رمز عبور را hard-code نمی‌کند.)
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- 1) جدول شعبه‌ها
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `branches` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,                       -- مثلا: شعبه بیمارستان امام خمینی
  `slug`       VARCHAR(100) NOT NULL,                       -- مثلا: tabriz-branch  (فقط a-z0-9-)
  `is_hq`      TINYINT(1)   NOT NULL DEFAULT 0,             -- دفتر مرکزی = 1 (دقیقا یک ردیف)
  `status`     ENUM('active','disabled') NOT NULL DEFAULT 'active',
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_branches_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- یک ردیف دفتر مرکزی (HQ) — اگر از قبل نباشد
INSERT INTO `branches` (`id`, `name`, `slug`, `is_hq`, `status`)
SELECT 1, 'دفتر مرکزی مکسا', 'hq', 1, 'active'
WHERE NOT EXISTS (SELECT 1 FROM `branches` WHERE `is_hq` = 1);

-- ----------------------------------------------------------------------------
-- 2) نقش‌ها/دسترسی‌های پنل — هر شعبه نقش‌های خود را دارد
--    (قبل از dashboard_users چون FK به آن دارد)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dashboard_roles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id`   INT UNSIGNED NOT NULL,
  `name`        VARCHAR(80)  NOT NULL,                      -- مثلا: خبرنگار
  `permissions` JSON         NOT NULL,                      -- مثلا: ["news"]
  `is_preset`   TINYINT(1)   NOT NULL DEFAULT 0,            -- نقش‌های پیش‌فرض مثل خبرنگار
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_branch_name` (`branch_id`, `name`),
  KEY `idx_roles_branch` (`branch_id`),
  CONSTRAINT `fk_roles_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3) کاربران پنل مدیریت — جدول واحد و تمیز
--    (جایگزین احراز هویت پراکنده‌ی فعلی؛ webusers فقط برای پنل خیرین است و
--     به این سامانه ربطی ندارد.)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dashboard_users` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id`       INT UNSIGNED NOT NULL,
  `username`        VARCHAR(60)  NOT NULL,
  `password_hash`   VARCHAR(255) NOT NULL,                  -- password_hash(PASSWORD_DEFAULT)
  `full_name`       VARCHAR(120) DEFAULT NULL,
  `role_id`         INT UNSIGNED DEFAULT NULL,              -- برای کاربران شعبه
  `is_super`        TINYINT(1)   NOT NULL DEFAULT 0,        -- ادمین مرکزی = 1
  `is_branch_admin` TINYINT(1)   NOT NULL DEFAULT 0,        -- ادمین شعبه = 1
  `status`          ENUM('active','disabled') NOT NULL DEFAULT 'active',
  `failed_attempts` INT UNSIGNED NOT NULL DEFAULT 0,        -- شمارنده‌ی تلاش ناموفق
  `locked_until`    DATETIME     DEFAULT NULL,              -- قفل موقت پس از تلاش‌های ناموفق
  `last_login_at`   DATETIME     DEFAULT NULL,
  `last_login_ip`   VARCHAR(45)  DEFAULT NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dashboard_username` (`username`),
  KEY `idx_dusers_branch` (`branch_id`),
  KEY `idx_dusers_role` (`role_id`),
  CONSTRAINT `fk_dusers_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
  CONSTRAINT `fk_dusers_role`   FOREIGN KEY (`role_id`)   REFERENCES `dashboard_roles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4) قابلیت‌های فعالِ هر شعبه (با تیک‌های زمان ساخت شعبه)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `branch_features` (
  `branch_id` INT UNSIGNED NOT NULL,
  `feature`   VARCHAR(50)  NOT NULL,   -- hero|news|partners|campaigns|courses|pages|financial|feedback|medical
  `enabled`   TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`branch_id`, `feature`),
  CONSTRAINT `fk_features_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- دفتر مرکزی همه‌ی قابلیت‌ها را دارد
INSERT IGNORE INTO `branch_features` (`branch_id`, `feature`, `enabled`) VALUES
  (1, 'hero', 1), (1, 'news', 1), (1, 'campaigns', 1), (1, 'partners', 1),
  (1, 'courses', 1), (1, 'pages', 1), (1, 'financial', 1), (1, 'feedback', 1), (1, 'medical', 1);

-- ----------------------------------------------------------------------------
-- 5) افزودن branch_id به جدول‌های محتوا + backfill به دفتر مرکزی (id=1)
--    چون MariaDB از ADD COLUMN IF NOT EXISTS پشتیبانی می‌کند، تکرار اجرا امن است.
-- ----------------------------------------------------------------------------

-- pages -----------------------------------------------------------------------
ALTER TABLE `pages` ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED NOT NULL DEFAULT 1;
UPDATE `pages` SET `branch_id` = 1 WHERE `branch_id` IS NULL OR `branch_id` = 0;
ALTER TABLE `pages` ADD KEY IF NOT EXISTS `idx_pages_branch` (`branch_id`);
-- یکتایی slug باید ترکیبی شود تا هر شعبه صفحه‌ی home خود را داشته باشد.
-- اگر کلید یکتای قبلی روی slug وجود دارد، به‌صورت دستی حذفش کنید (نام آن در محیط شما متغیر است):
--   ALTER TABLE `pages` DROP INDEX `slug`;
ALTER TABLE `pages` ADD UNIQUE KEY IF NOT EXISTS `uq_pages_branch_slug` (`branch_id`, `slug`);

-- news ------------------------------------------------------------------------
ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED NOT NULL DEFAULT 1;
UPDATE `news` SET `branch_id` = 1 WHERE `branch_id` IS NULL OR `branch_id` = 0;
ALTER TABLE `news` ADD KEY IF NOT EXISTS `idx_news_branch` (`branch_id`);

-- campaigns -------------------------------------------------------------------
ALTER TABLE `campaigns` ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED NOT NULL DEFAULT 1;
UPDATE `campaigns` SET `branch_id` = 1 WHERE `branch_id` IS NULL OR `branch_id` = 0;
ALTER TABLE `campaigns` ADD KEY IF NOT EXISTS `idx_campaigns_branch` (`branch_id`);

-- hero_slides -----------------------------------------------------------------
ALTER TABLE `hero_slides` ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED NOT NULL DEFAULT 1;
UPDATE `hero_slides` SET `branch_id` = 1 WHERE `branch_id` IS NULL OR `branch_id` = 0;
ALTER TABLE `hero_slides` ADD KEY IF NOT EXISTS `idx_hero_branch` (`branch_id`);

-- courses ---------------------------------------------------------------------
ALTER TABLE `courses` ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED NOT NULL DEFAULT 1;
UPDATE `courses` SET `branch_id` = 1 WHERE `branch_id` IS NULL OR `branch_id` = 0;
ALTER TABLE `courses` ADD KEY IF NOT EXISTS `idx_courses_branch` (`branch_id`);

-- employee_profiles (همکاران / partners) --------------------------------------
ALTER TABLE `employee_profiles` ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED NOT NULL DEFAULT 1;
UPDATE `employee_profiles` SET `branch_id` = 1 WHERE `branch_id` IS NULL OR `branch_id` = 0;
ALTER TABLE `employee_profiles` ADD KEY IF NOT EXISTS `idx_emp_branch` (`branch_id`);

-- ----------------------------------------------------------------------------
-- 6) انتساب کمک‌ها به شعبه — ستون مستقیم branch_id روی panel_donations
--    قاعده‌ی backfill:
--      - اگر کمک به کمپینی متعلق به شعبه‌ای پرداخت شده باشد → شعبه‌ی همان کمپین.
--      - اگر کمک عمومی باشد (campaign_id IS NULL) → دفتر مرکزی (1).
--    کمک‌های آینده هنگام ساخت، branch_id را با همین قاعده پر می‌کنند.
-- ----------------------------------------------------------------------------
ALTER TABLE `panel_donations` ADD COLUMN IF NOT EXISTS `branch_id` INT UNSIGNED NOT NULL DEFAULT 1;
UPDATE `panel_donations` pd
  LEFT JOIN `campaigns` c ON c.`id` = pd.`campaign_id`
  SET pd.`branch_id` = COALESCE(c.`branch_id`, 1);
ALTER TABLE `panel_donations` ADD KEY IF NOT EXISTS `idx_donations_branch` (`branch_id`);

-- ----------------------------------------------------------------------------
-- 7) نقش‌های پیش‌فرض برای دفتر مرکزی (نمونه: خبرنگار)
-- ----------------------------------------------------------------------------
INSERT INTO `dashboard_roles` (`branch_id`, `name`, `permissions`, `is_preset`)
SELECT 1, 'خبرنگار', JSON_ARRAY('news'), 1
WHERE NOT EXISTS (SELECT 1 FROM `dashboard_roles` WHERE `branch_id` = 1 AND `name` = 'خبرنگار');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- پایان مهاجرت 001
-- ============================================================================
