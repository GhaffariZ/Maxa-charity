-- NULL inherits the legacy role. An empty JSON array grants no features.
ALTER TABLE dashboard_users ADD COLUMN direct_permissions JSON NULL AFTER role_id;
