-- Migration 010: Sanitize existing stored content for XSS prevention
--
-- This migration identifies records that may contain unsanitized HTML.
-- The actual sanitization is performed by a PHP script (below) because
-- DOMDocument-based sanitization cannot be done in pure SQL.
--
-- USAGE:
--   php database/migrations/010_xss_sanitize_existing_content.php
--
-- The PHP script will:
--   1. Load the HtmlSanitizer from public_html/core/html-sanitizer.php
--   2. Read all news.content and campaigns.description rows
--   3. Sanitize each one through HtmlSanitizer::sanitize()
--   4. Update only rows whose content actually changed
--   5. Report how many rows were cleaned

-- Add a column to track which records have been sanitized (optional audit).
-- Uncomment if you want to track sanitization status:
-- ALTER TABLE `news` ADD COLUMN IF NOT EXISTS `content_sanitized_at` DATETIME DEFAULT NULL;
-- ALTER TABLE `campaigns` ADD COLUMN IF NOT EXISTS `description_sanitized_at` DATETIME DEFAULT NULL;

-- Marker so the PHP migration script knows this migration was applied.
SELECT 'Migration 010 ready — run the PHP sanitization script next.' AS status;
