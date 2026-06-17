<?php
use app\Core\Csrf;

$errors = $errors ?? [];
$old = $old ?? [];
$tags = $tags ?? [];
$currencies = $currencies ?? [];
$mode = $mode ?? 'create';
$action = $action ?? '/admin/packageTour/templates/create';
$template = $template ?? null;

$get = function(string $key, $default = '') use ($old) {
    $value = $old[$key] ?? $default;
    return htmlspecialchars(is_array($value) ? '' : (string) $value);
};
$getArr = function(string $key) use ($old) {
    $value = $old[$key] ?? [];
    return is_array($value) ? $value : [$value];
};
$getError = function(string $key) use ($errors) {
    return htmlspecialchars((string) ($errors[$key][0] ?? ''));
};
$selectedTags = array_map('intval', $getArr('tag_ids'));
$selectedCurrency = strtoupper((string) ($old['currency'] ?? 'COP'));
$includes = $getArr('includes');
$excludes = $getArr('excludes');
$highlights = $getArr('highlights');
$conditionTitles = $getArr('condition_titles');
$conditionContents = $getArr('condition_contents');
$itineraryDays = $getArr('itinerary_day_number');
$itineraryTitles = $getArr('itinerary_title');
$itineraryContents = $getArr('itinerary_content');
?>

<style>
.template-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.template-form-grid .wide{grid-column:1/-1}.repeat-box{display:grid;gap:10px}.repeat-item{display:grid;grid-template-columns:1fr auto;gap:10px}.repeat-condition{display:grid;grid-template-columns:.8fr 1.5fr auto;gap:10px}.template-help{background:#eff6ff;border:1px solid #bfdbfe;border-radius:16px;padding:14px;color:#1e3a8a;margin:14px 0}.tag-picker{display:flex;flex-wrap:wrap;gap:10px}.tag-picker label{display:inline-flex;align-items:center;gap:8px;border:1px solid #e5e7eb;border-radius:999px;padding:9px 12px;background:#fff;font-weight:800}.mini-danger{border:1px solid #fecaca;background:#fff1f2;color:#be123c;border-radius:12px;padding:0 12px;font-weight:800}@media(max-width:760px){.template-form-grid,.repeat-condition{grid-template-columns:1fr}.repeat-item{grid-template-columns:1fr}}
</style>

<div class="card">
  <div class="card-head">
    <div>
      <h2><?= $mode === 'edit' ? 'Editar plantilla' : 'Nueva plantilla' ?></h2>
      <p class="card-sub">Las plantillas rellenan el creador de paquetes sin guardar imágenes, slug de paquete ni estado de publicación.</p>
    </div>
    <div class="card-actions">
      <a href="/admin/packageTour/templates" class="btn" style="text-decoration:none;">Volver</a>
    </div>
  </div>

  <div class="template-help">
    <strong>Contenido que se guarda:</strong> título sugerido, subtítulo, ubicación, precio sugerido, moneda, duración, descripciones, incluidos, no incluidos, destacados, condiciones, itinerario, etiquetas y marcadores destacado/popular.
  </div>

  <form method="POST" action="<?= htmlspecialchars($action) ?>" id="templateForm">
    <?= Csrf::input(); ?>
    <?php if ($mode === 'edit'): ?>
      <input type="hidden" name="id" value="<?= (int) ($template->id ?? ($old['id'] ?? 0)) ?>">
    <?php endif; ?>

    <div class="template-form-grid">
      <div class="field">
        <label>Nombre de plantilla</label>
        <input name="name" value="<?= $get('name') ?>" placeholder="Ej: Europa con tiquetes y guía">
        <?php if ($getError('name')): ?><small class="error"><?= $getError('name') ?></small><?php endif; ?>
      </div>
      <div class="field">
        <label>Slug</label>
        <input name="slug" value="<?= $get('slug') ?>" placeholder="europa-con-tiquetes">
        <?php if ($getError('slug')): ?><small class="error"><?= $getError('slug') ?></small><?php endif; ?>
      </div>
      <div class="field wide">
        <label>Descripción interna</label>
        <input name="description" value="<?= $get('description') ?>" placeholder="Ej: Plantilla para paquetes internacionales con servicios estándar">
      </div>
      <div class="field wide">
        <label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" name="is_active" value="1" <?= !empty($old['is_active']) ? 'checked' : '' ?>> Plantilla activa</label>
        <small class="help">Solo las plantillas activas aparecen en “Plantilla rápida”.</small>
      </div>

      <div class="field"><label>Título sugerido</label><input name="title" value="<?= $get('title') ?>" placeholder="Ej: Europa fantástica"></div>
      <div class="field"><label>Subtítulo</label><input name="subtitle" value="<?= $get('subtitle') ?>" placeholder="Ej: Salida desde Bogotá"></div>
      <div class="field"><label>Ubicación sugerida</label><input name="location_name" value="<?= $get('location_name') ?>" placeholder="Europa / Turquía / Caribe"></div>
      <div class="field"><label>Precio desde sugerido</label><input type="number" step="0.01" name="price_from" value="<?= $get('price_from') ?>" placeholder="6076"></div>
      <div class="field">
        <label>Moneda</label>
        <select name="currency">
          <?php foreach ($currencies as $currency): ?>
            <?php $code = strtoupper((string) ($currency->code ?? '')); ?>
            <option value="<?= htmlspecialchars($code) ?>" <?= $code === $selectedCurrency ? 'selected' : '' ?>><?= htmlspecialchars($code . ' - ' . (string) ($currency->name ?? '')) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($getError('currency')): ?><small class="error"><?= $getError('currency') ?></small><?php endif; ?>
      </div>
      <div class="field"><label>Días</label><input type="number" min="0" name="duration_days" value="<?= $get('duration_days') ?>"></div>
      <div class="field"><label>Noches</label><input type="number" min="0" name="duration_nights" value="<?= $get('duration_nights') ?>"></div>
      <div class="field wide"><label>Descripción corta</label><textarea name="short_description" rows="3"><?= $get('short_description') ?></textarea></div>
      <div class="field wide"><label>Descripción general</label><textarea name="general_description" rows="5"><?= $get('general_description') ?></textarea></div>
      <div class="field wide"><label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" name="is_featured" value="1" <?= !empty($old['is_featured']) ? 'checked' : '' ?>> Sugerir como destacado</label></div>
      <div class="field wide"><label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" name="is_popular" value="1" <?= !empty($old['is_popular']) ? 'checked' : '' ?>> Sugerir como popular</label></div>

      <div class="field wide">
        <label>Etiquetas</label>
        <div class="tag-picker">
          <?php foreach ($tags as $tag): ?>
            <label><input type="checkbox" name="tag_ids[]" value="<?= (int) $tag->id ?>" <?= in_array((int) $tag->id, $selectedTags, true) ? 'checked' : '' ?>> <?= htmlspecialchars((string) ($tag->name ?? '')) ?></label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="sep"></div>

    <div class="template-form-grid">
      <div class="field wide">
        <label>Incluye</label>
        <div id="includesList" class="repeat-box">
          <?php foreach ($includes ?: [''] as $item): ?>
            <div class="repeat-item"><input name="includes[]" value="<?= htmlspecialchars((string) $item) ?>"><button type="button" class="mini-danger" data-remove>Eliminar</button></div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn" data-add-list="includesList" data-name="includes">+ Agregar incluido</button>
      </div>

      <div class="field wide">
        <label>No incluye</label>
        <div id="excludesList" class="repeat-box">
          <?php foreach ($excludes ?: [''] as $item): ?>
            <div class="repeat-item"><input name="excludes[]" value="<?= htmlspecialchars((string) $item) ?>"><button type="button" class="mini-danger" data-remove>Eliminar</button></div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn" data-add-list="excludesList" data-name="excludes">+ Agregar no incluido</button>
      </div>

      <div class="field wide">
        <label>Destacados / Highlights</label>
        <div id="highlightsList" class="repeat-box">
          <?php foreach ($highlights ?: [''] as $item): ?>
            <div class="repeat-item"><input name="highlights[]" value="<?= htmlspecialchars((string) $item) ?>"><button type="button" class="mini-danger" data-remove>Eliminar</button></div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="btn" data-add-list="highlightsList" data-name="highlights">+ Agregar destacado</button>
      </div>

      <div class="field wide">
        <label>Condiciones</label>
        <div id="conditionsList" class="repeat-box">
          <?php $maxConditions = max(count($conditionTitles), count($conditionContents), 1); ?>
          <?php for ($i = 0; $i < $maxConditions; $i++): ?>
            <div class="repeat-condition"><input name="condition_titles[]" value="<?= htmlspecialchars((string) ($conditionTitles[$i] ?? '')) ?>" placeholder="Título"><textarea name="condition_contents[]" placeholder="Contenido"><?= htmlspecialchars((string) ($conditionContents[$i] ?? '')) ?></textarea><button type="button" class="mini-danger" data-remove>Eliminar</button></div>
          <?php endfor; ?>
        </div>
        <button type="button" class="btn" data-add-condition>+ Agregar condición</button>
      </div>

      <div class="field wide">
        <label style="display:flex;align-items:center;gap:10px;"><input type="checkbox" name="no_itinerary" value="1" <?= !empty($old['no_itinerary']) ? 'checked' : '' ?>> No mostrar itinerario por ahora</label>
      </div>

      <div class="field wide">
        <label>Itinerario</label>
        <div id="itineraryList" class="repeat-box">
          <?php $maxItinerary = max(count($itineraryDays), count($itineraryTitles), count($itineraryContents), 1); ?>
          <?php for ($i = 0; $i < $maxItinerary; $i++): ?>
            <div class="repeat-condition"><input type="number" min="1" name="itinerary_day_number[]" value="<?= htmlspecialchars((string) ($itineraryDays[$i] ?? '')) ?>" placeholder="Día"><input name="itinerary_title[]" value="<?= htmlspecialchars((string) ($itineraryTitles[$i] ?? '')) ?>" placeholder="Título"><textarea name="itinerary_content[]" placeholder="Contenido"><?= htmlspecialchars((string) ($itineraryContents[$i] ?? '')) ?></textarea><button type="button" class="mini-danger" data-remove>Eliminar</button></div>
          <?php endfor; ?>
        </div>
        <button type="button" class="btn" data-add-itinerary>+ Agregar día</button>
      </div>
    </div>

    <div class="card-actions" style="margin-top:24px;">
      <a href="/admin/packageTour/templates" class="btn" style="text-decoration:none;">Cancelar</a>
      <button type="submit" class="btn primary"><?= $mode === 'edit' ? 'Guardar cambios' : 'Crear plantilla' ?></button>
    </div>
  </form>
</div>

<script>
(function(){
  function removeItem(btn){ const parent = btn.closest('.repeat-item,.repeat-condition'); if(parent) parent.remove(); }
  document.addEventListener('click', function(e){
    const remove = e.target.closest('[data-remove]');
    if(remove){ removeItem(remove); return; }
    const addList = e.target.closest('[data-add-list]');
    if(addList){
      const list = document.getElementById(addList.dataset.addList);
      const name = addList.dataset.name;
      if(list){ list.insertAdjacentHTML('beforeend','<div class="repeat-item"><input name="'+name+'[]" value=""><button type="button" class="mini-danger" data-remove>Eliminar</button></div>'); }
      return;
    }
    if(e.target.closest('[data-add-condition]')){
      document.getElementById('conditionsList')?.insertAdjacentHTML('beforeend','<div class="repeat-condition"><input name="condition_titles[]" placeholder="Título"><textarea name="condition_contents[]" placeholder="Contenido"></textarea><button type="button" class="mini-danger" data-remove>Eliminar</button></div>');
      return;
    }
    if(e.target.closest('[data-add-itinerary]')){
      document.getElementById('itineraryList')?.insertAdjacentHTML('beforeend','<div class="repeat-condition"><input type="number" min="1" name="itinerary_day_number[]" placeholder="Día"><input name="itinerary_title[]" placeholder="Título"><textarea name="itinerary_content[]" placeholder="Contenido"></textarea><button type="button" class="mini-danger" data-remove>Eliminar</button></div>');
    }
  });
})();
</script>
