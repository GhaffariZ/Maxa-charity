-- ============================================================================
--  Migration 002 — سامانه‌ی تیکتینگ (Ticketing) درختی برای پنل مدیریت مکسا
-- ----------------------------------------------------------------------------
--  هرم دسترسی:
--    1) کاربر شعبه (creator_role='user')      → تیکت به مدیر شعبه‌ی خود (target='branch')
--    2) مدیر شعبه (creator_role='branch_admin')→ پاسخ به کاربران + تیکت به ستاد (target='hq')
--    3) ستاد مرکزی (super)                     → پاسخ به تیکت‌های ارسالی از شعب (target='hq')
--
--  نکته‌ی مهم درباره‌ی کلیدهای خارجی (FOREIGN KEY):
--    این جدول‌ها عمداً بدونِ FOREIGN KEY ساخته می‌شوند تا روی هاست‌های اشتراکی
--    (که نوعِ دقیقِ ستونِ id در جدول‌های مرجع ممکن است متفاوت باشد) خطای
--    «errno 150» ندهند. یکپارچگیِ داده‌ها به‌طور کامل در لایه‌ی برنامه (PHP)
--    تضمین می‌شود: همه‌ی id‌ها از نشستِ کاربر و کوئری‌های معتبر می‌آیند.
--    اگر خواستید FK سخت‌گیرانه هم اضافه شود، انتهای فایلِ migration را ببینید.
--
--  این مهاجرت برای اعمالِ تمیز، ابتدا جدول‌های قبلیِ تیکتینگ را (در صورت وجودِ
--  ناقص از اجرای ناموفقِ قبلی) حذف می‌کند — چون این قابلیت تازه است و هنوز
--  داده‌ی واقعی ندارد، حذف امن است.
--
--  نحوه‌ی اجرا:
--    mysql -u USER -p DBNAME < database/migrations/002_ticketing.sql
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ticket_reads`;
DROP TABLE IF EXISTS `ticket_messages`;
DROP TABLE IF EXISTS `tickets`;

-- ----------------------------------------------------------------------------
-- 1) تیکت‌ها
-- ----------------------------------------------------------------------------
CREATE TABLE `tickets` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id`      INT UNSIGNED NOT NULL,                          -- شعبه‌ی مبدأ تیکت
  `subject`        VARCHAR(200) NOT NULL,                          -- موضوع
  `target`         ENUM('branch','hq') NOT NULL,                   -- branch: کاربر→مدیرشعبه ، hq: شعبه→ستاد
  `priority`       ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
  `status`         ENUM('open','answered','closed') NOT NULL DEFAULT 'open',
  `created_by`     INT UNSIGNED NOT NULL,                          -- dashboard_users.id سازنده
  `creator_role`   ENUM('user','branch_admin','super') NOT NULL,   -- نقشِ سازنده هنگام ساخت
  `escalated_from` INT UNSIGNED DEFAULT NULL,                      -- اگر از «ارجاع به ستاد» ساخته شده
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_reply_at`  DATETIME DEFAULT NULL,                          -- زمان آخرین پیام (برای مرتب‌سازی)
  PRIMARY KEY (`id`),
  KEY `idx_tickets_branch`  (`branch_id`),
  KEY `idx_tickets_target`  (`target`),
  KEY `idx_tickets_creator` (`created_by`),
  KEY `idx_tickets_status`  (`status`),
  KEY `idx_tickets_escal`   (`escalated_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2) پیام‌های هر تیکت (نخِ گفتگو)
-- ----------------------------------------------------------------------------
CREATE TABLE `ticket_messages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id`   INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED NOT NULL,                             -- نویسنده‌ی پیام
  `author_role` ENUM('user','branch_admin','super') NOT NULL,
  `body`        TEXT NOT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tmsg_ticket` (`ticket_id`),
  KEY `idx_tmsg_user`   (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3) ردگیری «خوانده‌شده» برای هر کاربر (برای نشانِ پیام جدید)
-- ----------------------------------------------------------------------------
CREATE TABLE `ticket_reads` (
  `ticket_id`    INT UNSIGNED NOT NULL,
  `user_id`      INT UNSIGNED NOT NULL,
  `last_read_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ticket_id`, `user_id`),
  KEY `idx_tread_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
--  کلیدهای خارجی
-- ----------------------------------------------------------------------------
--  نوعِ ستونِ id در branches و dashboard_users برابرِ INT UNSIGNED است (تأییدشده)،
--  پس این کلیدها به‌درستی شکل می‌گیرند. چون ابتدای این فایل جدول‌ها DROP می‌شوند،
--  اجرای دوباره‌ی کل فایل هم بدونِ خطای «نام تکراری» امن است.
-- ============================================================================
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_branch`  FOREIGN KEY (`branch_id`)      REFERENCES `branches`(`id`)        ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tickets_creator` FOREIGN KEY (`created_by`)     REFERENCES `dashboard_users`(`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tickets_escal`   FOREIGN KEY (`escalated_from`) REFERENCES `tickets`(`id`)         ON DELETE SET NULL;

ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `fk_tmsg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`)         ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tmsg_user`   FOREIGN KEY (`user_id`)   REFERENCES `dashboard_users`(`id`) ON DELETE CASCADE;

ALTER TABLE `ticket_reads`
  ADD CONSTRAINT `fk_tread_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`)         ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tread_user`   FOREIGN KEY (`user_id`)   REFERENCES `dashboard_users`(`id`) ON DELETE CASCADE;
-- ============================================================================
-- پایان مهاجرت 002
-- ============================================================================
