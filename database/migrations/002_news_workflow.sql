-- ============================================================================
--  Migration 002 — گردش‌کارِ تحریریه‌ی خبر (Editorial Workflow)
-- ----------------------------------------------------------------------------
--  وضعیتِ خبر را گسترش می‌دهد تا «ارسال به سردبیر» و «عدم تایید» پشتیبانی شوند:
--    draft     → ذخیره‌ی اولیه‌ی نویسنده
--    review    → ارسال‌شده برای سردبیر (در صفِ بررسی)
--    published → تاییدشده و منتشرشده
--    rejected  → ردشده توسط سردبیر (به‌همراه reject_reason)
--
--  ستونِ reject_reason از قبل وجود دارد. این مهاجرت فقط enum را گسترش می‌دهد.
--
--  اجرا:
--    mysql -u USER -p DBNAME < database/migrations/002_news_workflow.sql
-- ============================================================================

SET NAMES utf8mb4;

ALTER TABLE `news`
  MODIFY `status` ENUM('draft','review','published','rejected') NOT NULL DEFAULT 'draft';

-- نقشِ پیش‌فرضِ «سردبیر» برای ستاد مرکزی (branch_id = 1) — دسترسی به خبر + سردبیری
INSERT INTO `dashboard_roles` (`branch_id`, `name`, `permissions`, `is_preset`)
SELECT 1, 'سردبیر', JSON_ARRAY('news','news_editor'), 1
WHERE NOT EXISTS (SELECT 1 FROM `dashboard_roles` WHERE `branch_id` = 1 AND `name` = 'سردبیر');

-- ============================================================================
-- پایان مهاجرت 002
-- ============================================================================
