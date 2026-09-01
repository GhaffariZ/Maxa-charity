-- Migration 009: Race condition fix for donation callbacks
--
-- Adds a UNIQUE constraint on `gateway_authority` so that duplicate
-- gateway callbacks for the same payment are rejected at the database level.
-- This is the database-level safety net; the application layer also uses
-- atomic UPDATE ... WHERE status = 'pending' with rowCount() checks.

-- 1. Remove any duplicate authorities first (keep the oldest record per authority).
--    This handles any existing duplicates before enforcing uniqueness.
DELETE d1 FROM panel_donations d1
  INNER JOIN panel_donations d2
  ON d1.gateway_authority = d2.gateway_authority
   AND d1.id > d2.id
 WHERE d1.gateway_authority IS NOT NULL
   AND d1.gateway_authority != '';

-- 2. Drop the non-unique index if it exists, then add the unique constraint.
ALTER TABLE `panel_donations`
  DROP INDEX IF EXISTS `idx_panel_donations_authority`;

ALTER TABLE `panel_donations`
  ADD UNIQUE KEY `uq_panel_donations_authority` (`gateway_authority`);

-- 3. Add an index on (status) for the atomic UPDATE ... WHERE status = 'pending' pattern.
--    The existing idx_panel_donations_status already covers this, but ensure it exists.
ALTER TABLE `panel_donations`
  ADD INDEX IF NOT EXISTS `idx_panel_donations_status` (`status`);
