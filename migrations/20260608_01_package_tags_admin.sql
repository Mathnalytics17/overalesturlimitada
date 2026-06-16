CREATE TABLE IF NOT EXISTS tour_package_tags (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NOT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  color VARCHAR(20) NOT NULL DEFAULT '#1FA4CF',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_tour_package_tags_uuid (uuid),
  UNIQUE KEY uq_tour_package_tags_slug (slug),
  INDEX idx_tour_package_tags_active (is_active),
  INDEX idx_tour_package_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tour_package_tag_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tour_package_id BIGINT UNSIGNED NOT NULL,
  tag_id BIGINT UNSIGNED NOT NULL,
  UNIQUE KEY uq_tour_package_tag_items_package_tag (tour_package_id, tag_id),
  INDEX idx_tour_package_tag_items_package (tour_package_id),
  INDEX idx_tour_package_tag_items_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tour_package_tags (uuid, name, slug, color, is_active, created_at, updated_at)
SELECT UUID(), seed.name, seed.slug, seed.color, 1, NOW(), NOW()
FROM (
  SELECT 'Playa' AS name, 'playa' AS slug, '#0EA5E9' AS color UNION ALL
  SELECT 'Familiar', 'familiar', '#22C55E' UNION ALL
  SELECT 'Luna de miel', 'luna-de-miel', '#E11D48' UNION ALL
  SELECT 'Aventura', 'aventura', '#F97316' UNION ALL
  SELECT 'Internacional', 'internacional', '#6366F1' UNION ALL
  SELECT 'Nacional', 'nacional', '#14B8A6' UNION ALL
  SELECT 'Todo incluido', 'todo-incluido', '#8B5CF6' UNION ALL
  SELECT 'Económico', 'economico', '#84CC16' UNION ALL
  SELECT 'Premium', 'premium', '#F59E0B' UNION ALL
  SELECT 'Naturaleza', 'naturaleza', '#16A34A' UNION ALL
  SELECT 'Cultural', 'cultural', '#A855F7' UNION ALL
  SELECT 'Crucero', 'crucero', '#0284C7' UNION ALL
  SELECT 'Disney', 'disney', '#EC4899' UNION ALL
  SELECT 'Compras', 'compras', '#64748B' UNION ALL
  SELECT 'Fin de semana', 'fin-de-semana', '#0891B2'
) AS seed
WHERE NOT EXISTS (
  SELECT 1 FROM tour_package_tags existing WHERE existing.slug = seed.slug
);
