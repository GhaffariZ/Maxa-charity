-- Migration 029: Remove hamrah component from patient page
UPDATE `pages`
SET `components` = '["header","patientoption","footer"]'
WHERE `slug` = 'patientoption';
