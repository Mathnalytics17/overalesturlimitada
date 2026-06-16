CREATE TABLE IF NOT EXISTS customer_travel_preferences (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT UNSIGNED NOT NULL,
  preferred_tag_slugs_json JSON NULL,
  desired_destinations TEXT NULL,
  budget_min DECIMAL(14,2) NULL,
  budget_max DECIMAL(14,2) NULL,
  usual_travelers INT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_customer_travel_preferences_customer (customer_id),
  INDEX idx_customer_travel_preferences_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
