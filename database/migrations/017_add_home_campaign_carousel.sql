-- ============================================================================
-- Migration 017: place the latest-center-campaign carousel above "hamrah" on
-- the HQ home page. Safe to run more than once.
-- ============================================================================

SET NAMES utf8mb4;

UPDATE `pages` p
INNER JOIN `branches` b ON b.`id` = p.`branch_id` AND b.`is_hq` = 1
SET p.`components` = REPLACE(
  p.`components`,
  '"hamrah"',
  '"home-campaigns","hamrah"'
)
WHERE p.`slug` = 'home'
  AND p.`status` = 'published'
  AND p.`components` LIKE '%"hamrah"%'
  AND p.`components` NOT LIKE '%"home-campaigns"%';
