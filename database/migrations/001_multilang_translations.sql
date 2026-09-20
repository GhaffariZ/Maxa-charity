-- Multi-language translation tables migration
-- Run this after the main schema.sql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `page_translations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `page_translations`;
CREATE TABLE `page_translations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_id` int(10) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `components` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`components`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_page_locale` (`page_id`, `locale`),
  KEY `idx_page_translations_locale` (`locale`),
  CONSTRAINT `fk_page_translations_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `campaign_translations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `campaign_translations`;
CREATE TABLE `campaign_translations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id` int(10) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_campaign_locale` (`campaign_id`, `locale`),
  KEY `idx_campaign_translations_locale` (`locale`),
  CONSTRAINT `fk_campaign_translations_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `news_translations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `news_translations`;
CREATE TABLE `news_translations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id` int(10) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `summary` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_news_locale` (`news_id`, `locale`),
  KEY `idx_news_translations_locale` (`locale`),
  CONSTRAINT `fk_news_translations_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `hero_slide_translations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `hero_slide_translations`;
CREATE TABLE `hero_slide_translations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hero_slide_id` int(10) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_link` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_hero_slide_locale` (`hero_slide_id`, `locale`),
  KEY `idx_hero_slide_translations_locale` (`locale`),
  CONSTRAINT `fk_hero_slide_translations_hero` FOREIGN KEY (`hero_slide_id`) REFERENCES `hero_slides` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `course_translations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `course_translations`;
CREATE TABLE `course_translations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` int(10) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(512) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `what_you_learn` mediumtext DEFAULT NULL,
  `requirements` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_course_locale` (`course_id`, `locale`),
  KEY `idx_course_translations_locale` (`locale`),
  CONSTRAINT `fk_course_translations_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `employee_profile_translations`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `employee_profile_translations`;
CREATE TABLE `employee_profile_translations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `bio_professional` text DEFAULT NULL,
  `academic_background` text DEFAULT NULL,
  `maxa_responsibilities` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_employee_locale` (`employee_id`, `locale`),
  KEY `idx_employee_translations_locale` (`locale`),
  CONSTRAINT `fk_employee_translations_employee` FOREIGN KEY (`employee_id`) REFERENCES `employee_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `component_translations`
-- For components that use data.json format
-- --------------------------------------------------------

DROP TABLE IF EXISTS `component_translations`;
CREATE TABLE `component_translations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `component_name` varchar(255) NOT NULL,
  `locale` varchar(5) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_component_locale` (`component_name`, `locale`),
  KEY `idx_component_translations_locale` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `static_translations`
-- For UI strings, buttons, labels, etc. (key-value store)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `static_translations`;
CREATE TABLE `static_translations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `translation_key` varchar(255) NOT NULL,
  `locale` varchar(5) NOT NULL,
  `value` text DEFAULT NULL,
  `context` varchar(100) DEFAULT NULL COMMENT 'e.g., "nav", "button", "form", "hero"',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key_locale` (`translation_key`, `locale`),
  KEY `idx_static_translations_locale` (`locale`),
  KEY `idx_static_translations_context` (`context`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `languages`
-- Language configuration
-- --------------------------------------------------------

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `code` varchar(5) NOT NULL,
  `name_native` varchar(50) NOT NULL,
  `name_en` varchar(50) NOT NULL,
  `direction` enum('ltr','rtl') NOT NULL DEFAULT 'ltr',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `date_format` varchar(30) DEFAULT NULL,
  `number_format` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert default languages
-- --------------------------------------------------------

INSERT INTO `languages` (`code`, `name_native`, `name_en`, `direction`, `is_active`, `is_default`, `sort_order`, `date_format`, `number_format`) VALUES
('fa', 'فارسی', 'Persian', 'rtl', 1, 1, 1, 'Y/m/d', 'fa-IR'),
('en', 'English', 'English', 'ltr', 1, 0, 2, 'F j, Y', 'en-US'),
('ar', 'العربية', 'Arabic', 'rtl', 1, 0, 3, 'Y/m/d', 'ar-SA');

-- --------------------------------------------------------
-- Create view for easy translated content fetching
-- --------------------------------------------------------

-- Pages with translations view
CREATE OR REPLACE VIEW `v_pages_with_translations` AS
SELECT 
  p.*,
  pt.locale,
  pt.title AS trans_title,
  pt.slug AS trans_slug,
  pt.meta_title AS trans_meta_title,
  pt.meta_description AS trans_meta_description,
  pt.components AS trans_components
FROM `pages` p
LEFT JOIN `page_translations` pt ON p.id = pt.page_id;

-- Campaigns with translations view
CREATE OR REPLACE VIEW `v_campaigns_with_translations` AS
SELECT 
  c.*,
  ct.locale,
  ct.title AS trans_title,
  ct.short_description AS trans_short_description,
  ct.description AS trans_description
FROM `campaigns` c
LEFT JOIN `campaign_translations` ct ON c.id = ct.campaign_id;

-- News with translations view
CREATE OR REPLACE VIEW `v_news_with_translations` AS
SELECT 
  n.*,
  nt.locale,
  nt.title AS trans_title,
  nt.summary AS trans_summary,
  nt.content AS trans_content,
  nt.meta_title AS trans_meta_title,
  nt.meta_description AS trans_meta_description
FROM `news` n
LEFT JOIN `news_translations` nt ON n.id = nt.news_id;

-- Hero slides with translations view
CREATE OR REPLACE VIEW `v_hero_slides_with_translations` AS
SELECT 
  h.*,
  ht.locale,
  ht.title AS trans_title,
  ht.description AS trans_description,
  ht.button_text AS trans_button_text,
  ht.button_link AS trans_button_link
FROM `hero_slides` h
LEFT JOIN `hero_slide_translations` ht ON h.id = ht.hero_slide_id;

COMMIT;