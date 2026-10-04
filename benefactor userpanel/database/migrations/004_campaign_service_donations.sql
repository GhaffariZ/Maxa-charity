ALTER TABLE panel_donations
    ADD COLUMN IF NOT EXISTS remote_campaign_id CHAR(36) NULL AFTER campaign_id,
    ADD COLUMN IF NOT EXISTS remote_campaign_title VARCHAR(255) NULL AFTER remote_campaign_id,
    ADD KEY IF NOT EXISTS idx_panel_donations_remote_campaign (remote_campaign_id);
