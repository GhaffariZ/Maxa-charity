-- Migration 003 — notifications, campaign suggestions, tax certificate requests

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    type       VARCHAR(40) NOT NULL,
    title      VARCHAR(200) NOT NULL,
    body       TEXT NULL DEFAULT NULL,
    link       VARCHAR(255) NULL DEFAULT NULL,
    read_at    DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notif_user_read (user_id, read_at),
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS campaign_suggestions (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NULL DEFAULT NULL,
    title       VARCHAR(200) NULL DEFAULT NULL,
    description TEXT NOT NULL,
    contact     VARCHAR(255) NULL DEFAULT NULL,
    status      ENUM('new','reviewing','accepted','rejected') NOT NULL DEFAULT 'new',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_suggestions_status (status),
    KEY idx_suggestions_user (user_id),
    CONSTRAINT fk_suggestions_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tax_certificate_requests (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     BIGINT UNSIGNED NOT NULL,
    year_jalali SMALLINT UNSIGNED NULL DEFAULT NULL,
    status      ENUM('requested','processing','issued','rejected') NOT NULL DEFAULT 'requested',
    note        TEXT NULL DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_taxcert_user (user_id),
    KEY idx_taxcert_status (status),
    CONSTRAINT fk_taxcert_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_migrations (version) VALUES ('003_engagement')
ON DUPLICATE KEY UPDATE version = version;
