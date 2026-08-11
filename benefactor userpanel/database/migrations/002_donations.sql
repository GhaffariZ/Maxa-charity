-- Migration 002 — panel donations ledger
-- The site's `campaigns` table already exists in this database; we DO NOT create
-- or seed it. The panel reads it and bumps `collected_amount` on success.
-- `campaign_id` below references the existing `campaigns.id` (no cross-table FK).

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS panel_donations (
    id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference          VARCHAR(20) NOT NULL,
    user_id            BIGINT UNSIGNED NULL DEFAULT NULL,
    campaign_id        INT UNSIGNED NULL DEFAULT NULL,
    amount             BIGINT UNSIGNED NOT NULL,
    currency           CHAR(3) NOT NULL DEFAULT 'IRT',
    type               ENUM('online','card_to_card') NOT NULL DEFAULT 'online',
    status             ENUM('pending','success','failed','refunded','canceled') NOT NULL DEFAULT 'pending',
    gateway            VARCHAR(30) NULL DEFAULT NULL,
    gateway_authority  VARCHAR(255) NULL DEFAULT NULL,
    gateway_ref_id     VARCHAR(255) NULL DEFAULT NULL,
    gateway_track_id   VARCHAR(255) NULL DEFAULT NULL,
    failure_reason     VARCHAR(255) NULL DEFAULT NULL,
    receipt_number     VARCHAR(30)  NULL DEFAULT NULL,
    paid_at            DATETIME NULL DEFAULT NULL,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_panel_donations_reference (reference),
    KEY idx_panel_donations_user_time (user_id, created_at),
    KEY idx_panel_donations_campaign (campaign_id),
    KEY idx_panel_donations_status (status),
    KEY idx_panel_donations_authority (gateway_authority),
    CONSTRAINT fk_panel_donations_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_migrations (version) VALUES ('002_donations')
ON DUPLICATE KEY UPDATE version = version;
