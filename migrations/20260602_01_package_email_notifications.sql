ALTER TABLE customer_travel_preferences
  ADD COLUMN notify_new_packages TINYINT(1) NOT NULL DEFAULT 0 AFTER usual_travelers,
  ADD COLUMN notify_recommendations TINYINT(1) NOT NULL DEFAULT 0 AFTER notify_new_packages;

CREATE TABLE IF NOT EXISTS customer_package_notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  tour_package_id BIGINT UNSIGNED NOT NULL,
  notification_type VARCHAR(40) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'pending',
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  last_error VARCHAR(500) NULL,
  queued_at DATETIME NOT NULL,
  sent_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_customer_package_notification (customer_id, tour_package_id, notification_type),
  INDEX idx_customer_package_notifications_status (status),
  INDEX idx_customer_package_notifications_customer (customer_id),
  INDEX idx_customer_package_notifications_package (tour_package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
