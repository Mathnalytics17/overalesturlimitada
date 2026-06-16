<?php
$report = $report ?? [];
$summary = $report['summary'] ?? [];
$rows = $report['rows'] ?? [];
$pagination = $report['pagination'] ?? [];
?>
<div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;margin-bottom:18px;">
  <div>
    <h1 style="margin:0;">Analítica de paquetes</h1>
    <p style="margin:6px 0 0;color:#64748b;">Compara el interés de los viajeros desde la vista inicial hasta la consulta.</p>
  </div>
  <a class="btn" href="/admin/packageTour" style="text-decoration:none;">Volver a paquetes</a>
</div>

<div class="card" style="margin-bottom:18px;">
  <form method="GET" action="/admin/packageTour/analytics" class="form">
    <div class="field">
      <label for="from">Desde</label>
      <input id="from" name="from" type="date" value="<?= htmlspecialchars((string) ($report['from'] ?? '')) ?>">
    </div>
    <div class="field">
      <label for="to">Hasta</label>
      <input id="to" name="to" type="date" value="<?= htmlspecialchars((string) ($report['to'] ?? '')) ?>">
    </div>
    <div class="field">
      <label for="per_page">Filas</label>
      <select id="per_page" name="per_page">
        <?php foreach ([10, 25, 50, 100] as $size): ?>
          <option value="<?= $size ?>" <?= (int) ($pagination['per_page'] ?? 25) === $size ? 'selected' : '' ?>><?= $size ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="grid-column:1/-1;display:flex;gap:10px;justify-content:flex-end;">
      <a class="btn" href="/admin/packageTour/analytics" style="text-decoration:none;">Limpiar filtro</a>
      <button class="btn primary" type="submit">Aplicar fechas</button>
    </div>
  </form>
</div>

<div class="grid kpis" style="margin-bottom:18px;">
  <div class="card kpi">
    <div class="card-sub">Vistas</div>
    <div class="kpi-value"><?= (int) ($summary['views'] ?? 0) ?></div>
  </div>
  <div class="card kpi">
    <div class="card-sub">Favoritos agregados</div>
    <div class="kpi-value"><?= (int) ($summary['favorite_adds'] ?? 0) ?></div>
  </div>
  <div class="card kpi">
    <div class="card-sub">Favoritos actuales</div>
    <div class="kpi-value"><?= (int) ($summary['favorites_current'] ?? 0) ?></div>
  </div>
  <div class="card kpi">
    <div class="card-sub">Consultas</div>
    <div class="kpi-value"><?= (int) ($summary['inquiries'] ?? 0) ?></div>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <div>
      <h2>Conversión por paquete</h2>
      <p class="card-sub">Los favoritos actuales reflejan el estado de hoy; el resto respeta el rango de fechas seleccionado.</p>
    </div>
  </div>
  <div class="sep"></div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Paquete</th>
          <th>Estado</th>
          <th>Vistas</th>
          <th>Favoritos agregados</th>
          <th>Favoritos actuales</th>
          <th>Consultas</th>
          <th>Vista a favorito</th>
          <th>Vista a consulta</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <?php $package = $row['package']; ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars((string) ($package->title ?? '')) ?></strong>
              <div class="t-muted"><?= htmlspecialchars((string) ($package->location_name ?? '')) ?></div>
            </td>
            <td><span class="badge <?= ($package->status ?? '') === 'published' ? 'ok' : 'warn' ?>"><?= htmlspecialchars((string) ($package->status ?? '')) ?></span></td>
            <td><?= (int) $row['views'] ?></td>
            <td><?= (int) $row['favorite_adds'] ?></td>
            <td><?= (int) $row['favorites_current'] ?></td>
            <td><?= (int) $row['inquiries'] ?></td>
            <td><?= number_format((float) $row['favorite_rate'], 1, ',', '.') ?>%</td>
            <td><?= number_format((float) $row['inquiry_rate'], 1, ',', '.') ?>%</td>
          </tr>
        <?php endforeach; ?>

        <?php if (empty($rows)): ?>
          <tr><td colspan="8" class="t-muted">No hay paquetes registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php
    $paginationBaseUrl = '/admin/packageTour/analytics';
    $paginationFilters = [
      'from' => $report['from'] ?? '',
      'to' => $report['to'] ?? '',
      'per_page' => $pagination['per_page'] ?? 25,
    ];
    require dirname(__DIR__, 3) . '/shared/partials/admin_pagination.php';
  ?>
</div>
