CREATE TABLE IF NOT EXISTS `tour_package_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(180) NOT NULL,
  `description` VARCHAR(255) NULL,
  `payload_json` JSON NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by_admin_id` INT UNSIGNED NULL,
  `updated_by_admin_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tour_package_templates_uuid` (`uuid`),
  UNIQUE KEY `uq_tour_package_templates_slug` (`slug`),
  INDEX `idx_tour_package_templates_active` (`is_active`),
  INDEX `idx_tour_package_templates_admin` (`created_by_admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @col_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'tour_package_templates'
    AND COLUMN_NAME = 'updated_by_admin_id'
);
SET @sql := IF(@col_exists = 0,
  'ALTER TABLE tour_package_templates ADD COLUMN updated_by_admin_id INT UNSIGNED NULL AFTER created_by_admin_id',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE tour_package_templates
SET is_active = 1
WHERE is_active IS NULL;
