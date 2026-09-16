-- Keep the local benefactor receipt/history while Campaign Service remains the
-- authoritative payment and campaign store.
ALTER TABLE `panel_donations`
  ADD COLUMN IF NOT EXISTS `remote_campaign_id` char(36) DEFAULT NULL AFTER `campaign_id`,
  ADD COLUMN IF NOT EXISTS `remote_campaign_title` varchar(255) DEFAULT NULL AFTER `remote_campaign_id`;

ALTER TABLE `panel_donations`
  ADD KEY IF NOT EXISTS `idx_panel_donations_remote_campaign` (`remote_campaign_id`);
