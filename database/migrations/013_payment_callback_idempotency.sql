-- One gateway transaction must map to at most one donation.
-- MySQL permits multiple NULL values in a UNIQUE index, so pending/manual rows
-- without gateway identifiers remain unaffected.
-- Migration 009 used authority alone; replace it so gateway namespaces remain
-- independent while existing installations upgrade cleanly.
ALTER TABLE `panel_donations`
  DROP INDEX IF EXISTS `uq_panel_donations_authority`;

ALTER TABLE `panel_donations`
  ADD UNIQUE KEY `uq_panel_donations_gateway_authority` (`gateway`, `gateway_authority`),
  ADD UNIQUE KEY `uq_panel_donations_gateway_ref` (`gateway`, `gateway_ref_id`);
