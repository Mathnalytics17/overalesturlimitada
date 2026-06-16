CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` char(3) NOT NULL,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(12) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_currencies_code` (`code`),
  KEY `idx_currencies_active_order` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `currencies` (`code`, `name`, `symbol`, `is_active`, `sort_order`) VALUES
('COP', 'Peso colombiano', '$', 1, 1),
('USD', 'Dólar estadounidense', 'US$', 1, 2),
('EUR', 'Euro', '€', 1, 3),
('MXN', 'Peso mexicano', 'MX$', 1, 4),
('BRL', 'Real brasileño', 'R$', 1, 5),
('GBP', 'Libra esterlina', '£', 1, 6),
('CAD', 'Dólar canadiense', 'CA$', 1, 7)
ON DUPLICATE KEY UPDATE
`name` = VALUES(`name`),
`symbol` = VALUES(`symbol`),
`is_active` = VALUES(`is_active`),
`sort_order` = VALUES(`sort_order`),
`updated_at` = CURRENT_TIMESTAMP;

ALTER TABLE `tour_packages`
  ADD COLUMN `currency_id` int UNSIGNED DEFAULT NULL AFTER `price_from`;

UPDATE `tour_packages` p
JOIN `currencies` c ON c.`code` = UPPER(TRIM(p.`currency`))
SET p.`currency_id` = c.`id`
WHERE p.`currency_id` IS NULL;

ALTER TABLE `tour_packages`
  ADD KEY `idx_tour_packages_currency_id` (`currency_id`),
  ADD KEY `idx_tour_packages_sort_order` (`sort_order`),
  ADD CONSTRAINT `fk_tour_packages_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL;
