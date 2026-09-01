-- Migration 009: Add Supreme Council (headdirectors), Board of Directors (directors), and Medical Staff (doctorspage) pages

INSERT INTO `pages` (`title`, `slug`, `components`, `status`, `created_at`, `updated_at`, `branch_id`)
SELECT 'شورای عالی مکسا', 'headdirectors', '["topbar","header","headdirectors","footer"]', 'published', NOW(), NOW(), 1
WHERE NOT EXISTS (
    SELECT 1 FROM `pages` WHERE `slug` = 'headdirectors' AND `branch_id` = 1
);

INSERT INTO `pages` (`title`, `slug`, `components`, `status`, `created_at`, `updated_at`, `branch_id`)
SELECT 'هیئت مدیره مکسا', 'directors', '["topbar","header","directors","footer"]', 'published', NOW(), NOW(), 1
WHERE NOT EXISTS (
    SELECT 1 FROM `pages` WHERE `slug` = 'directors' AND `branch_id` = 1
);

INSERT INTO `pages` (`title`, `slug`, `components`, `status`, `created_at`, `updated_at`, `branch_id`)
SELECT 'کادر درمان و متخصصان مکسا', 'doctorspage', '["topbar","header","doctorspage","footer"]', 'published', NOW(), NOW(), 1
WHERE NOT EXISTS (
    SELECT 1 FROM `pages` WHERE `slug` = 'doctorspage' AND `branch_id` = 1
);
