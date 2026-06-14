-- ============================================================================
--  Ticketing system — support tickets submitted from the supporters' panel
--  and managed from the admin dashboard (tickets.php).
--
--  Apply with:  mysql -u USER -p DBNAME < 2026_06_14_create_tickets.sql
--  The dashboard also auto-creates these tables on first load (ticket-db.php),
--  so applying this file manually is optional but recommended for versioning.
-- ============================================================================

-- A ticket raised by a supporter / visitor.
CREATE TABLE IF NOT EXISTS `tickets` (
  `id`           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`      INT(11) DEFAULT NULL COMMENT 'webusers.id when the submitter is logged in',
  `name`         VARCHAR(120) NOT NULL COMMENT 'submitter display name',
  `contact`      VARCHAR(255) DEFAULT NULL COMMENT 'email or phone for follow-up',
  `subject`      VARCHAR(200) NOT NULL,
  `category`     ENUM('general','donation','technical','campaign','other') NOT NULL DEFAULT 'general',
  `priority`     ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
  `message`      TEXT NOT NULL,
  `status`       ENUM('open','answered','closed') NOT NULL DEFAULT 'open',
  `admin_unread` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'new/unseen by an admin',
  `user_unread`  TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'has an unread admin reply',
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tickets_user` (`user_id`),
  KEY `idx_tickets_status` (`status`),
  KEY `idx_tickets_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A message in a ticket thread (the first message lives on the ticket itself;
-- subsequent admin answers / user follow-ups are stored here).
CREATE TABLE IF NOT EXISTS `ticket_replies` (
  `id`         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id`  BIGINT(20) UNSIGNED NOT NULL,
  `author`     ENUM('admin','user') NOT NULL DEFAULT 'admin',
  `message`    TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_replies_ticket` (`ticket_id`),
  CONSTRAINT `fk_replies_ticket` FOREIGN KEY (`ticket_id`)
    REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
