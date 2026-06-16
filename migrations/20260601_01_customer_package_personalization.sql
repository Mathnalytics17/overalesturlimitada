CREATE TABLE IF NOT EXISTS customer_package_favorites (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  tour_package_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_customer_package_favorite (customer_id, tour_package_id),
  INDEX idx_customer_package_favorites_customer (customer_id),
  INDEX idx_customer_package_favorites_package (tour_package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customer_package_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NULL,
  tour_package_id BIGINT UNSIGNED NOT NULL,
  event_type VARCHAR(40) NOT NULL,
  visitor_key VARCHAR(64) NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX idx_customer_package_events_customer (customer_id),
  INDEX idx_customer_package_events_package (tour_package_id),
  INDEX idx_customer_package_events_type (event_type),
  INDEX idx_customer_package_events_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
