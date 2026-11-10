-- Admin notification acknowledgements are separate from donor notifications and task status.
CREATE TABLE IF NOT EXISTS dashboard_notification_reads (
  user_id INT UNSIGNED NOT NULL,
  branch_id INT UNSIGNED NOT NULL,
  event_key VARCHAR(100) NOT NULL,
  read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, branch_id, event_key),
  CONSTRAINT fk_dash_notification_user FOREIGN KEY (user_id) REFERENCES dashboard_users(id) ON DELETE CASCADE,
  CONSTRAINT fk_dash_notification_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
