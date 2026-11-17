-- Migration 028: Remove hamrah component from patientoption page
UPDATE `pages`
SET `components` = '["header","patientoption","footer"]'
WHERE `slug` = 'patientoption';
