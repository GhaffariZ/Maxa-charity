-- Enable each new section at the branch level where it applies.
-- Ordinary users still need an explicit grant for each section.
INSERT IGNORE INTO branch_features (branch_id, feature, enabled)
SELECT id, 'tickets', 1 FROM branches
UNION ALL SELECT id, 'branch_intro', 1 FROM branches WHERE is_hq = 0
UNION ALL SELECT id, 'donation_impacts', 1 FROM branches WHERE is_hq = 1
UNION ALL SELECT id, 'macsa_stories', 1 FROM branches WHERE is_hq = 1
UNION ALL SELECT id, 'maxapedia', 1 FROM branches WHERE is_hq = 1
UNION ALL SELECT id, 'news_editor', 1 FROM branches WHERE is_hq = 1;
