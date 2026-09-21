-- Event management module for the HQ content team.
INSERT INTO `branch_features` (`branch_id`, `feature`, `enabled`)
SELECT `id`, 'events', 1 FROM `branches` WHERE `is_hq` = 1
ON DUPLICATE KEY UPDATE `enabled` = VALUES(`enabled`);

CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `event_date` DATE NOT NULL,
  `start_time` TIME NOT NULL DEFAULT '08:00:00',
  `short_description` VARCHAR(500) DEFAULT NULL,
  `about` LONGTEXT DEFAULT NULL,
  `poster` VARCHAR(500) DEFAULT NULL,
  `schedule_pdf` VARCHAR(500) DEFAULT NULL,
  `registration_url` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  `banner_active` TINYINT(1) NOT NULL DEFAULT 0,
  `banner_label` VARCHAR(120) DEFAULT 'رویداد پیش‌رو',
  `banner_cta` VARCHAR(120) DEFAULT 'مشاهده رویداد',
  `banner_link` VARCHAR(500) DEFAULT NULL,
  `banner_theme` VARCHAR(30) NOT NULL DEFAULT 'teal',
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_events_slug` (`slug`), KEY `idx_events_date_status` (`event_date`,`status`), KEY `idx_events_banner` (`banner_active`),
  CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `dashboard_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_heroes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `event_id` INT UNSIGNED NOT NULL, `title` VARCHAR(255) NOT NULL, `description` VARCHAR(500) DEFAULT NULL, `image` VARCHAR(500) DEFAULT NULL, `button_label` VARCHAR(120) DEFAULT NULL, `button_link` VARCHAR(500) DEFAULT NULL, `sort_order` INT NOT NULL DEFAULT 0, `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`), KEY `idx_event_heroes_event` (`event_id`), CONSTRAINT `fk_event_heroes_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_people` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `event_id` INT UNSIGNED NOT NULL, `role` ENUM('scientific_secretary','executive_secretary') NOT NULL, `name` VARCHAR(180) NOT NULL, `title` VARCHAR(255) DEFAULT NULL, `image` VARCHAR(500) DEFAULT NULL, `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`), KEY `idx_event_people_event` (`event_id`), CONSTRAINT `fk_event_people_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_speakers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `event_id` INT UNSIGNED NOT NULL, `name` VARCHAR(180) NOT NULL, `title` VARCHAR(255) DEFAULT NULL, `image` VARCHAR(500) DEFAULT NULL, `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`), KEY `idx_event_speakers_event` (`event_id`), CONSTRAINT `fk_event_speakers_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_partners` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `event_id` INT UNSIGNED NOT NULL, `name` VARCHAR(180) NOT NULL, `logo` VARCHAR(500) DEFAULT NULL, `sort_order` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`), KEY `idx_event_partners_event` (`event_id`), CONSTRAINT `fk_event_partners_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_news` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `event_id` INT UNSIGNED NOT NULL, `title` VARCHAR(255) NOT NULL, `slug` VARCHAR(180) NOT NULL, `excerpt` VARCHAR(500) DEFAULT NULL, `content` LONGTEXT NOT NULL, `image` VARCHAR(500) DEFAULT NULL, `status` ENUM('draft','published') NOT NULL DEFAULT 'draft', `published_at` DATETIME DEFAULT NULL, `created_by` INT UNSIGNED DEFAULT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_event_news_slug` (`slug`), KEY `idx_event_news_event` (`event_id`), CONSTRAINT `fk_event_news_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE, CONSTRAINT `fk_event_news_author` FOREIGN KEY (`created_by`) REFERENCES `dashboard_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
