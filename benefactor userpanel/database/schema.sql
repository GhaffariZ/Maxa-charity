-- =============================================================================
--  خیرین مکسا (Maksa Benefactors) — Panel database schema
-- -----------------------------------------------------------------------------
--  IMPORTANT: this is meant to be imported INTO the EXISTING site database
--  `erfantey_macsacharity`. To avoid clashing with the site's own tables
--  (`users`, `campaigns`, `donations`), every panel table is prefixed `panel_`
--  or is panel-specific. The panel READS campaigns from the existing
--  `campaigns` table and bumps its `collected_amount` on a successful donation;
--  it never creates or alters the site's tables.
--
--  Paste this whole file into phpMyAdmin → SQL tab (select erfantey_macsacharity).
--  Engine:   InnoDB   Charset: utf8mb4   Collation: utf8mb4_unicode_ci
--  Money:    BIGINT UNSIGNED in Toman.  Time: UTC (Jalali handled in frontend).
--  Tokens:   only SHA-256 hashes are stored, never plaintext.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+00:00';

-- ============================ REFERENCE ======================================

CREATE TABLE IF NOT EXISTS donor_tiers (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug        VARCHAR(30)  NOT NULL,
    name_fa     VARCHAR(50)  NOT NULL,
    min_points  INT UNSIGNED NOT NULL DEFAULT 0,
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_donor_tiers_slug (slug),
    KEY idx_donor_tiers_points (min_points)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================== IDENTITY / AUTH ====================================
-- Panel supporter accounts. Distinct from the site's `users` (admins) and
-- `webusers` (site members) tables.

CREATE TABLE IF NOT EXISTS panel_users (
    id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email                 VARCHAR(255) NOT NULL,
    password_hash         VARCHAR(255) NOT NULL,
    email_verified_at     DATETIME NULL DEFAULT NULL,
    status                ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending',
    failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until          DATETIME NULL DEFAULT NULL,
    last_login_at         DATETIME NULL DEFAULT NULL,
    last_login_ip         VARCHAR(45) NULL DEFAULT NULL,
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_panel_users_email (email),
    KEY idx_panel_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_profiles (
    user_id         BIGINT UNSIGNED NOT NULL,
    first_name      VARCHAR(100) NULL DEFAULT NULL,
    last_name       VARCHAR(100) NULL DEFAULT NULL,
    phone           VARCHAR(20)  NULL DEFAULT NULL,
    avatar_url      VARCHAR(500) NULL DEFAULT NULL,
    postal_address  TEXT NULL DEFAULT NULL,
    timezone        VARCHAR(64) NOT NULL DEFAULT 'Asia/Tehran',
    locale          VARCHAR(10) NOT NULL DEFAULT 'fa-IR',
    donor_tier_id   TINYINT UNSIGNED NULL DEFAULT NULL,
    kindness_points INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    KEY idx_user_profiles_tier (donor_tier_id),
    CONSTRAINT fk_profiles_user FOREIGN KEY (user_id)       REFERENCES panel_users(id) ON DELETE CASCADE,
    CONSTRAINT fk_profiles_tier FOREIGN KEY (donor_tier_id) REFERENCES donor_tiers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_notification_prefs (
    user_id        BIGINT UNSIGNED NOT NULL,
    news           TINYINT(1) NOT NULL DEFAULT 1,
    impact_reports TINYINT(1) NOT NULL DEFAULT 1,
    new_campaigns  TINYINT(1) NOT NULL DEFAULT 1,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_notif_prefs_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at    DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_evt_token_hash (token_hash),
    KEY idx_evt_user (user_id),
    KEY idx_evt_expires (expires_at),
    CONSTRAINT fk_evt_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id           BIGINT UNSIGNED NOT NULL,
    token_hash        CHAR(64) NOT NULL,
    expires_at        DATETIME NOT NULL,
    used_at           DATETIME NULL DEFAULT NULL,
    ip_requested_from VARCHAR(45) NULL DEFAULT NULL,
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_prt_token_hash (token_hash),
    KEY idx_prt_user (user_id),
    KEY idx_prt_expires (expires_at),
    CONSTRAINT fk_prt_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS refresh_tokens (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id          BIGINT UNSIGNED NOT NULL,
    token_hash       CHAR(64) NOT NULL,
    family_id        CHAR(36) NOT NULL,
    expires_at       DATETIME NOT NULL,
    revoked_at       DATETIME NULL DEFAULT NULL,
    replaced_by_hash CHAR(64) NULL DEFAULT NULL,
    user_agent       VARCHAR(255) NULL DEFAULT NULL,
    ip               VARCHAR(45)  NULL DEFAULT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rt_token_hash (token_hash),
    KEY idx_rt_user (user_id),
    KEY idx_rt_family (family_id),
    KEY idx_rt_expires (expires_at),
    CONSTRAINT fk_rt_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email        VARCHAR(255) NULL DEFAULT NULL,
    ip           VARCHAR(45)  NOT NULL,
    success      TINYINT(1)   NOT NULL DEFAULT 0,
    user_agent   VARCHAR(255) NULL DEFAULT NULL,
    attempted_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_la_email_time (email, attempted_at),
    KEY idx_la_ip_time (ip, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    BIGINT UNSIGNED NULL DEFAULT NULL,
    action     VARCHAR(100) NOT NULL,
    ip         VARCHAR(45)  NULL DEFAULT NULL,
    user_agent VARCHAR(255) NULL DEFAULT NULL,
    metadata   JSON NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user (user_id),
    KEY idx_audit_action (action),
    KEY idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES panel_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================= DONATIONS =========================================
-- The panel's own donation ledger (the site's `donations` table is left alone).
-- `campaign_id` references the EXISTING `campaigns.id` — kept as a plain indexed
-- column (no cross-table FK) so the two schemas stay independently manageable.

CREATE TABLE IF NOT EXISTS panel_donations (
    id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference          VARCHAR(20) NOT NULL,
    user_id            BIGINT UNSIGNED NULL DEFAULT NULL,
    campaign_id        INT UNSIGNED NULL DEFAULT NULL,    -- => campaigns.id
    amount             BIGINT UNSIGNED NOT NULL,          -- Toman
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

-- ========================= ENGAGEMENT ========================================

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

SET FOREIGN_KEY_CHECKS = 1;

-- ============================ SEED ===========================================
-- NOTE: campaigns are NOT seeded here — the panel reads the existing `campaigns`
-- table that already lives in this database.

INSERT INTO donor_tiers (slug, name_fa, min_points, sort_order) VALUES
    ('bronze',   'حامی برنزی',     0,     1),
    ('silver',   'حامی نقره‌ای',   1000,  2),
    ('gold',     'حامی طلایی',     5000,  3),
    ('platinum', 'حامی پلاتینیوم', 15000, 4)
ON DUPLICATE KEY UPDATE name_fa = VALUES(name_fa), min_points = VALUES(min_points), sort_order = VALUES(sort_order);
