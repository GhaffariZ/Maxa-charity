-- Migration 001 — panel identity & auth foundation
-- Import INTO the existing `erfantey_macsacharity` database. Panel tables are
-- prefixed `panel_` to coexist with the site's own `users`/`campaigns`/`donations`.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS schema_migrations (
    version    VARCHAR(50) NOT NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

INSERT INTO donor_tiers (slug, name_fa, min_points, sort_order) VALUES
    ('bronze',   'حامی برنزی',     0,     1),
    ('silver',   'حامی نقره‌ای',   1000,  2),
    ('gold',     'حامی طلایی',     5000,  3),
    ('platinum', 'حامی پلاتینیوم', 15000, 4)
ON DUPLICATE KEY UPDATE name_fa = VALUES(name_fa);

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

INSERT INTO schema_migrations (version) VALUES ('001_init_auth')
ON DUPLICATE KEY UPDATE version = version;
