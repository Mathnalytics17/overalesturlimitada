<?php
use app\Core\Csrf;

$action = $action ?? '/admin/packageTour/tags/create';
$submitText = $submitText ?? 'Guardar etiqueta';
$cancelUrl = $cancelUrl ?? '/admin/packageTour/tags';
$old = $old ?? [];
$errors = $errors ?? [];
$tag = $tag ?? null;

$value = function (string $field, string $default = '') use ($old) {
    return htmlspecialchars((string) ($old[$field] ?? $default), ENT_QUOTES, 'UTF-8');
};

$error = function (string $field) use ($errors) {
    return $errors[$field][0] ?? null;
};

$isActive = array_key_exists('is_active', $old)
    ? (int) $old['is_active'] === 1
    : true;
?>

<div class="card" style="max-width: 860px;">
  <div class="card-head">
    <div>
      <h2><?= htmlspecialchars($formTitle ?? 'Etiqueta') ?></h2>
      <p class="card-sub">Estas etiquetas se muestran en el perfil del cliente y en el formulario de paquetes.</p>
    </div>
    <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn" style="text-decoration:none;">Volver</a>
  </div>

  <div class="sep"></div>

  <form method="POST" action="<?= htmlspecialchars($action) ?>" class="form">
    <?= Csrf::input(); ?>
    <?php if ($tag): ?>
      <input type="hidden" name="id" value="<?= (int) $tag->id ?>">
    <?php endif; ?>

    <div class="field">
      <label for="name">Nombre de la etiqueta</label>
      <input id="name" name="name" type="text" value="<?= $value('name') ?>" placeholder="Ej: Playa" required>
      <?php if ($error('name')): ?><div class="t-muted" style="color:#b91c1c;"><?= htmlspecialchars($error('name')) ?></div><?php endif; ?>
    </div>

    <div class="field">
      <label for="slug">Slug</label>
      <input id="slug" name="slug" type="text" value="<?= $value('slug') ?>" placeholder="playa">
      <div class="t-muted">Puedes dejarlo vacío; el sistema lo genera desde el nombre.</div>
      <?php if ($error('slug')): ?><div class="t-muted" style="color:#b91c1c;"><?= htmlspecialchars($error('slug')) ?></div><?php endif; ?>
    </div>

    <div class="field">
      <label for="color">Color</label>
      <div style="display:flex; gap:10px; align-items:center;">
        <input id="color" name="color" type="color" value="<?= $value('color', '#1FA4CF') ?>" style="width:72px; padding:5px; height:46px;">
        <input id="colorText" type="text" value="<?= $value('color', '#1FA4CF') ?>" readonly style="max-width:140px;">
      </div>
    </div>

    <div class="field">
      <label>Estado</label>
      <label style="display:flex; align-items:center; gap:10px; font-size:14px; font-weight:800; margin:0;">
        <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?> style="width:auto;">
        Activa
      </label>
      <div class="t-muted">Solo las etiquetas activas aparecen para seleccionar en paquetes y preferencias.</div>
    </div>

    <div class="field" style="grid-column:1 / -1; display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
      <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn" style="text-decoration:none;">Cancelar</a>
      <button type="submit" class="btn primary"><?= htmlspecialchars($submitText) ?></button>
    </div>
  </form>
</div>

<script>
(function(){
  const name = document.getElementById('name');
  const slug = document.getElementById('slug');
  const color = document.getElementById('color');
  const colorText = document.getElementById('colorText');

  function slugify(value) {
    return String(value || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  if (name && slug) {
    let touchedSlug = slug.value.trim() !== '';
    slug.addEventListener('input', () => { touchedSlug = true; slug.value = slugify(slug.value); });
    name.addEventListener('input', () => {
      if (!touchedSlug) slug.value = slugify(name.value);
    });
  }

  if (color && colorText) {
    color.addEventListener('input', () => { colorText.value = color.value.toUpperCase(); });
  }
})();
</script>
