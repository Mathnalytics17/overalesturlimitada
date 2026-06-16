<?php
use app\Core\Csrf;
$errors = $errors ?? [];
$old = $old ?? [];
$currency = $currency ?? null;
$action = $action ?? '/admin/currencies/create';
$mode = $mode ?? 'create';
$get = fn(string $field, $default = '') => htmlspecialchars((string)($old[$field] ?? $default));
$error = fn(string $field) => $errors[$field][0] ?? null;
$isActive = array_key_exists('is_active', $old) ? (int)$old['is_active'] === 1 : true;
?>

<form method="POST" action="<?= htmlspecialchars($action) ?>" class="card" style="max-width:860px;">
  <?= Csrf::input(); ?>
  <?php if ($mode === 'edit' && $currency): ?>
    <input type="hidden" name="id" value="<?= (int)$currency->id ?>">
  <?php endif; ?>

  <div class="card-head">
    <div>
      <h2><?= $mode === 'edit' ? 'Editar moneda' : 'Nueva moneda' ?></h2>
      <p class="card-sub">Estas monedas serán seleccionables al crear o editar paquetes.</p>
    </div>
  </div>

  <div class="sep"></div>

  <div class="admin-filter-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));align-items:start;">
    <div>
      <label style="font-weight:800;display:block;margin-bottom:8px;">Código</label>
      <input name="code" value="<?= $get('code') ?>" placeholder="COP" maxlength="3">
      <?php if ($error('code')): ?><div class="t-danger" style="margin-top:6px;"><?= htmlspecialchars($error('code')) ?></div><?php endif; ?>
    </div>

    <div>
      <label style="font-weight:800;display:block;margin-bottom:8px;">Nombre</label>
      <input name="name" value="<?= $get('name') ?>" placeholder="Peso colombiano">
      <?php if ($error('name')): ?><div class="t-danger" style="margin-top:6px;"><?= htmlspecialchars($error('name')) ?></div><?php endif; ?>
    </div>

    <div>
      <label style="font-weight:800;display:block;margin-bottom:8px;">Símbolo</label>
      <input name="symbol" value="<?= $get('symbol') ?>" placeholder="$">
      <?php if ($error('symbol')): ?><div class="t-danger" style="margin-top:6px;"><?= htmlspecialchars($error('symbol')) ?></div><?php endif; ?>
    </div>

    <div>
      <label style="font-weight:800;display:block;margin-bottom:8px;">Orden</label>
      <input type="number" min="0" name="sort_order" value="<?= $get('sort_order', '0') ?>" placeholder="1">
    </div>
  </div>

  <label class="check-chip" style="margin-top:16px;">
    <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?>>
    <span>Moneda activa</span>
  </label>

  <div class="sep"></div>

  <div class="row-actions" style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="btn" href="/admin/currencies" style="text-decoration:none;">Cancelar</a>
    <button class="btn primary" type="submit"><?= $mode === 'edit' ? 'Guardar cambios' : 'Crear moneda' ?></button>
  </div>
</form>
