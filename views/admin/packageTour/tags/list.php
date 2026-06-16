<?php
use app\Core\Csrf;

$tags = $tags ?? [];
$usage = $usage ?? [];
$filters = $filters ?? [];
?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>Etiquetas de paquetes</h2>
      <p class="card-sub">Controlan las opciones que ve el cliente en “Mis preferencias de viaje”.</p>
    </div>
    <div class="card-actions" style="flex-wrap:wrap; justify-content:flex-end;">
      <form method="POST" action="/admin/packageTour/tags/seed" onsubmit="return confirm('¿Crear etiquetas iniciales recomendadas?');">
        <?= Csrf::input(); ?>
        <button type="submit" class="btn">Crear etiquetas iniciales</button>
      </form>
      <a href="/admin/packageTour" class="btn" style="text-decoration:none;">Ver paquetes</a>
      <a href="/admin/packageTour/tags/create" class="btn primary" style="text-decoration:none;">+ Nueva etiqueta</a>
    </div>
  </div>

  <div class="sep"></div>

  <form method="GET" action="/admin/packageTour/tags" class="admin-filter-grid">
    <input type="text" name="q" value="<?= htmlspecialchars((string) ($filters['q'] ?? '')) ?>" placeholder="Buscar por nombre o slug">
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
          <th>Etiqueta</th>
          <th>Slug</th>
          <th>Color</th>
          <th>Estado</th>
          <th>Usada en</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tags as $tag): ?>
          <?php $count = (int) ($usage[(int) $tag->id] ?? 0); ?>
          <tr>
            <td>
              <span style="display:inline-flex; align-items:center; gap:8px;">
                <span style="width:12px; height:12px; border-radius:999px; background:<?= htmlspecialchars($tag->color ?? '#1FA4CF') ?>; border:1px solid #e5e7eb;"></span>
                <?= htmlspecialchars($tag->name ?? '') ?>
              </span>
            </td>
            <td class="t-muted"><?= htmlspecialchars($tag->slug ?? '') ?></td>
            <td><code><?= htmlspecialchars($tag->color ?? '') ?></code></td>
            <td>
              <span class="badge <?= !empty($tag->is_active) ? 'ok' : 'neutral' ?>">
                <?= !empty($tag->is_active) ? 'Activa' : 'Inactiva' ?>
              </span>
            </td>
            <td><span class="badge info"><?= $count ?> paquete(s)</span></td>
            <td>
              <div class="row-actions">
                <a class="chip" title="Editar" href="/admin/packageTour/tags/edit?id=<?= (int) $tag->id ?>" style="display:inline-grid; place-items:center; text-decoration:none;">✏️</a>
                <form method="POST" action="/admin/packageTour/tags/delete" onsubmit="return confirm('¿Eliminar esta etiqueta? Si está usada, se desactivará.');">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="id" value="<?= (int) $tag->id ?>">
                  <button class="chip" type="submit" title="Eliminar">🗑️</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($tags)): ?>
          <tr>
            <td colspan="6" class="t-muted">No hay etiquetas registradas. Usa “Crear etiquetas iniciales” para empezar rápido.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
