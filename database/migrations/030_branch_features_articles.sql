-- Enable articles feature for all branches so users granted the permission can manage articles
INSERT IGNORE INTO branch_features (branch_id, feature, enabled)
SELECT id, 'articles', 1 FROM branches;
