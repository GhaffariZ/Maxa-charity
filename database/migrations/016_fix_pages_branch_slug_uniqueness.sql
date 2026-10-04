-- ============================================================================
-- Migration 016: allow every branch to own a page with the same slug (home).
-- Safe to run more than once on MySQL/MariaDB.
-- ============================================================================

SET NAMES utf8mb4;

-- Older databases still have a single-column UNIQUE index on pages.slug.
-- Locate it regardless of its environment-specific index name and remove it.
SET @legacy_pages_slug_index = (
  SELECT s.`INDEX_NAME`
  FROM `information_schema`.`STATISTICS` s
  WHERE s.`TABLE_SCHEMA` = DATABASE()
    AND s.`TABLE_NAME` = 'pages'
    AND s.`NON_UNIQUE` = 0
  GROUP BY s.`INDEX_NAME`
  HAVING COUNT(*) = 1
     AND SUM(s.`COLUMN_NAME` = 'slug') = 1
  LIMIT 1
);

SET @drop_legacy_pages_slug_sql = IF(
  @legacy_pages_slug_index IS NULL,
  'SELECT 1',
  CONCAT(
    'ALTER TABLE `pages` DROP INDEX `',
    REPLACE(@legacy_pages_slug_index, '`', '``'),
    '`'
  )
);

PREPARE drop_legacy_pages_slug_stmt FROM @drop_legacy_pages_slug_sql;
EXECUTE drop_legacy_pages_slug_stmt;
DEALLOCATE PREPARE drop_legacy_pages_slug_stmt;

-- Correct uniqueness rule: a slug is unique only inside its branch.
-- Use information_schema instead of ADD ... IF NOT EXISTS so this also works
-- on MySQL releases that do not support that clause for indexes.
SET @pages_branch_slug_index_exists = (
  SELECT COUNT(*)
  FROM (
    SELECT s.`INDEX_NAME`
    FROM `information_schema`.`STATISTICS` s
    WHERE s.`TABLE_SCHEMA` = DATABASE()
      AND s.`TABLE_NAME` = 'pages'
      AND s.`NON_UNIQUE` = 0
    GROUP BY s.`INDEX_NAME`
    HAVING COUNT(*) = 2
       AND SUM(s.`SEQ_IN_INDEX` = 1 AND s.`COLUMN_NAME` = 'branch_id') = 1
       AND SUM(s.`SEQ_IN_INDEX` = 2 AND s.`COLUMN_NAME` = 'slug') = 1
  ) existing_branch_slug_indexes
);

SET @add_pages_branch_slug_sql = IF(
  @pages_branch_slug_index_exists > 0,
  'SELECT 1',
  'ALTER TABLE `pages` ADD UNIQUE KEY `uq_pages_branch_slug` (`branch_id`, `slug`)'
);

PREPARE add_pages_branch_slug_stmt FROM @add_pages_branch_slug_sql;
EXECUTE add_pages_branch_slug_stmt;
DEALLOCATE PREPARE add_pages_branch_slug_stmt;
