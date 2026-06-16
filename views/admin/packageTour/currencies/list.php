<?php
use app\Core\Csrf;
$currencies = $currencies ?? [];
$usage = $usage ?? [];
$filters = $filters ?? [];
?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>Monedas</h2>
      <p class="card-sub">Controla las monedas disponibles en la creación y edición de paquetes.</p>
    </div>
    <div class="card-actions" style="flex-wrap:wrap; justify-content:flex-end;">
      <form method="POST" action="/admin/currencies/seed" onsubmit="return confirm('¿Crear monedas base como COP, USD y EUR?');">
        <?= Csrf::input(); ?>
        <button type="submit" class="btn">Crear monedas base</button>
      </form>
      <a href="/admin/packageTour" class="btn" style="text-decoration:none;">Ver paquetes</a>
      <a href="/admin/currencies/create" class="btn primary" style="text-decoration:none;">+ Nueva moneda</a>
    </div>
  </div>

  <div class="sep"></div>

  <form method="GET" action="/admin/currencies" class="admin-filter-grid">
    <input type="text" name="q" value="<?= htmlspecialchars((string)($filters['q'] ?? '')) ?>" placeholder="Buscar código, nombre o símbolo">
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
          <th>Código</th>
          <th>Nombre</th>
          <th>Símbolo</th>
          <th>Estado</th>
          <th>Usada en</th>
          <th>Orden</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($currencies as $currency): ?>
          <?php $count = (int)($usage[(int)$currency->id] ?? 0); ?>
          <tr>
            <td><strong><?= htmlspecialchars((string)$currency->code) ?></strong></td>
            <td><?= htmlspecialchars((string)$currency->name) ?></td>
            <td><?= htmlspecialchars((string)$currency->symbol) ?></td>
            <td>
              <span class="badge <?= !empty($currency->is_active) ? 'ok' : 'neutral' ?>">
                <?= !empty($currency->is_active) ? 'Activa' : 'Inactiva' ?>
              </span>
            </td>
            <td><span class="badge info"><?= $count ?> paquete(s)</span></td>
            <td><?= (int)($currency->sort_order ?? 0) ?></td>
            <td>
              <div class="row-actions">
                <a class="chip" title="Editar" href="/admin/currencies/edit?id=<?= (int)$currency->id ?>" style="display:inline-grid;place-items:center;text-decoration:none;">✏️</a>
                <form method="POST" action="/admin/currencies/toggle" onsubmit="return confirm('¿Cambiar estado de esta moneda?');">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="id" value="<?= (int)$currency->id ?>">
                  <button class="chip" type="submit" title="<?= !empty($currency->is_active) ? 'Desactivar' : 'Activar' ?>">
                    <?= !empty($currency->is_active) ? '⏸️' : '▶️' ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($currencies)): ?>
          <tr>
            <td colspan="7" class="t-muted">No hay monedas registradas. Usa “Crear monedas base” para iniciar.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
