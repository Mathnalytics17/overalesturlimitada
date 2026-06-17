-- Opcional: ejecuta esto si en producción las etiquetas iniciales aparecen inactivas.
UPDATE tour_package_tags
SET is_active = 1, updated_at = NOW()
WHERE slug IN (
  'playa', 'familiar', 'luna-de-miel', 'aventura', 'internacional',
  'nacional', 'todo-incluido', 'economico', 'premium', 'naturaleza',
  'cultural', 'crucero', 'disney', 'compras', 'fin-de-semana'
);
