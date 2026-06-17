<?php
use app\Core\Csrf;

$templates = $templates ?? [];
$filters = $filters ?? [];

function template_payload_summary($template): string {
    $payload = method_exists($template, 'payload') ? $template->payload() : [];
    $parts = [];
    if (!empty($payload['title'])) $parts[] = 'Título sugerido';
    if (!empty($payload['price_from'])) $parts[] = 'Precio';
    if (!empty($payload['includes'])) $parts[] = count($payload['includes']) . ' incluidos';
    if (!empty($payload['excludes'])) $parts[] = count($payload['excludes']) . ' no incluidos';
    if (!empty($payload['conditions'])) $parts[] = count($payload['conditions']) . ' condiciones';
    if (!empty($payload['itinerary'])) $parts[] = count($payload['itinerary']) . ' días';
    if (!empty($payload['no_itinerary'])) $parts[] = 'sin itinerario';
    if (!empty($payload['tag_ids'])) $parts[] = count($payload['tag_ids']) . ' etiquetas';
    return $parts ? implode(' · ', $parts) : 'Sin contenido reutilizable';
}
?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>Plantillas de paquetes</h2>
      <p class="card-sub">Crea y edita estructuras reutilizables para no repetir información en paquetes similares.</p>
    </div>
    <div class="card-actions" style="flex-wrap:wrap; justify-content:flex-end;">
      <a href="/admin/packageTour/create" class="btn" style="text-decoration:none;">Crear paquete</a>
      <a href="/admin/packageTour/templates/create" class="btn primary" style="text-decoration:none;">+ Nueva plantilla</a>
    </div>
  </div>

  <div class="alert info" style="margin-top:14px;">
    <strong>¿Qué guarda una plantilla?</strong><br>
    Guarda textos, ubicación/precio sugeridos, moneda, duración, incluidos, no incluidos, destacados, condiciones, itinerario, etiquetas y marcadores comercialmente reutilizables. No guarda imágenes, slug, estado de publicación ni posición para evitar duplicados raros.
  </div>

  <div class="sep"></div>

  <form method="GET" action="/admin/packageTour/templates" class="admin-filter-grid">
    <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>" placeholder="Buscar por nombre, slug o descripción">
    <select name="status">
      <option value="">Todos los estados</option>
      <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Activas</option>
      <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactivas</option>
    </select>
    <button class="btn primary" type="submit">Filtrar</button>
  </form>

  <div class="table-wrap" style="margin-top:14px;">
    <table>
      <thead>
        <tr>
          <th>Plantilla</th>
          <th>Slug</th>
          <th>Contenido guardado</th>
          <th>Estado</th>
          <th>Actualizada</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($templates as $template): ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars((string) ($template->name ?? 'Plantilla')) ?></strong>
              <?php if (!empty($template->description)): ?>
                <div class="t-muted"><?= htmlspecialchars((string) $template->description) ?></div>
              <?php endif; ?>
            </td>
            <td class="t-muted"><?= htmlspecialchars((string) ($template->slug ?? '')) ?></td>
            <td><?= htmlspecialchars(template_payload_summary($template)) ?></td>
            <td>
              <span class="badge <?= (int) ($template->is_active ?? 0) === 1 ? 'ok' : 'neutral' ?>">
                <?= (int) ($template->is_active ?? 0) === 1 ? 'Activa' : 'Inactiva' ?>
              </span>
            </td>
            <td class="t-muted"><?= htmlspecialchars((string) ($template->updated_at ?? '-')) ?></td>
            <td>
              <div class="row-actions">
                <a class="chip" title="Editar" href="/admin/packageTour/templates/edit?id=<?= (int) $template->id ?>" style="display:inline-grid; place-items:center; text-decoration:none;">✏️</a>
                <form method="POST" action="/admin/packageTour/templates/toggle">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="id" value="<?= (int) $template->id ?>">
                  <button class="chip" type="submit" title="<?= (int) ($template->is_active ?? 0) === 1 ? 'Desactivar' : 'Activar' ?>">
                    <?= (int) ($template->is_active ?? 0) === 1 ? '⏸️' : '▶️' ?>
                  </button>
                </form>
                <form method="POST" action="/admin/packageTour/templates/delete" onsubmit="return confirm('¿Eliminar esta plantilla? Esta acción no afecta paquetes ya creados.');">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="id" value="<?= (int) $template->id ?>">
                  <button class="chip" type="submit" title="Eliminar">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($templates)): ?>
          <tr>
            <td colspan="6" class="t-muted">No hay plantillas registradas. Crea una plantilla para reutilizar contenido al crear paquetes.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
