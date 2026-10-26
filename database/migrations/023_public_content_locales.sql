-- Existing content remains Persian. Safe to run repeatedly; no records are backfilled.
CREATE TABLE IF NOT EXISTS public_content_locales (
    content_type VARCHAR(48) NOT NULL,
    content_id VARCHAR(190) NOT NULL,
    locale CHAR(2) NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 0,
    copy_fa TINYINT(1) NOT NULL DEFAULT 0,
    payload LONGTEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (content_type, content_id, locale),
    INDEX locale_listing (content_type, locale, enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
