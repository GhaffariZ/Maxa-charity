-- Rename the existing center without changing its slug, IDs or relationships.
-- Run once on the existing database; safe to run again.
SET NAMES utf8mb4;

START TRANSACTION;

UPDATE `branches`
SET `name` = 'مرکز تخصصی ارتباطات و سلامت از راه دور'
WHERE `slug` = 'contact-center';

UPDATE `employee_profiles`
SET `branch` = 'مرکز تخصصی ارتباطات و سلامت از راه دور'
WHERE `branch_id` = (SELECT `id` FROM `branches` WHERE `slug` = 'contact-center')
  AND `branch` IN ('مرکز ارتباطات و دورپزشکی', 'مرکز ارتباطات کشوری', 'مرکز ارتباطات مکسا');

UPDATE `dashboard_users`
SET `full_name` = REPLACE(`full_name`, 'مرکز ارتباطات و دورپزشکی', 'مرکز تخصصی ارتباطات و سلامت از راه دور')
WHERE `branch_id` = (SELECT `id` FROM `branches` WHERE `slug` = 'contact-center')
  AND `full_name` LIKE '%مرکز ارتباطات و دورپزشکی%';

UPDATE `pages`
SET `title` = REPLACE(`title`, 'مرکز ارتباطات و دورپزشکی', 'مرکز تخصصی ارتباطات و سلامت از راه دور')
WHERE `branch_id` = (SELECT `id` FROM `branches` WHERE `slug` = 'contact-center')
  AND `title` LIKE '%مرکز ارتباطات و دورپزشکی%';

COMMIT;
