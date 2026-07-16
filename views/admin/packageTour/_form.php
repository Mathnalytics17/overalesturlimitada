<?php

use app\Core\Csrf;

$mode = $mode ?? 'create';
$action = $action ?? '/admin/packageTour/create';
$submitText = $submitText ?? 'Guardar paquete';
$cancelUrl = $cancelUrl ?? '/admin/packageTour';
$package = $package ?? null;
$tags = $tags ?? [];
$templates = $templates ?? [];
$currencies = $currencies ?? [];
$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];
$selectedTagIds = $selectedTagIds ?? [];
$cover = $cover ?? null;
$gallery = $gallery ?? [];
$includes = $includes ?? [];
$excludes = $excludes ?? [];
$conditions = $conditions ?? [];
$highlights = $highlights ?? [];
$itinerary = $itinerary ?? [];

$getValue = function (string $field, $default = '') use ($old) {
    return htmlspecialchars((string)($old[$field] ?? $default));
};

$getRawValue = function (string $field, $default = '') use ($old) {
    return $old[$field] ?? $default;
};

$getError = function (string $field) use ($errors) {
    return $errors[$field][0] ?? null;
};

$selectedTags = !empty($old['tag_ids'])
    ? array_map('intval', (array)$old['tag_ids'])
    : array_map('intval', (array)$selectedTagIds);

$isFeatured = array_key_exists('is_featured', $old)
    ? (string)$old['is_featured'] === '1'
    : (int)($package->is_featured ?? 0) === 1;

$isPopular = array_key_exists('is_popular', $old)
    ? (string)$old['is_popular'] === '1'
    : (int)($package->is_popular ?? 0) === 1;
    
$includeRows = [];
if (isset($old['includes']) && is_array($old['includes'])) {
    $includeRows = $old['includes'];
} elseif (!empty($includes)) {
    $includeRows = array_map(fn($item) => (string)($item->content ?? ''), $includes);
}
if (empty($includeRows)) {
    $includeRows = [''];
}

$excludeRows = [];
if (isset($old['excludes']) && is_array($old['excludes'])) {
    $excludeRows = $old['excludes'];
} elseif (!empty($excludes)) {
    $excludeRows = array_map(fn($item) => (string)($item->content ?? ''), $excludes);
}
if (empty($excludeRows)) {
    $excludeRows = [''];
}

$highlightRows = [];
if (isset($old['highlights']) && is_array($old['highlights'])) {
    $highlightRows = $old['highlights'];
} elseif (!empty($highlights)) {
    $highlightRows = array_map(fn($item) => (string)($item->title ?? ''), $highlights);
}
if (empty($highlightRows)) {
    $highlightRows = [''];
}

$conditionTitleRows = [];
$conditionContentRows = [];

if (isset($old['condition_titles']) && is_array($old['condition_titles'])) {
    $conditionTitleRows = $old['condition_titles'];
    $conditionContentRows = $old['condition_contents'] ?? [];
} elseif (!empty($conditions)) {
    $conditionTitleRows = array_map(fn($item) => (string)($item->title ?? ''), $conditions);
    $conditionContentRows = array_map(fn($item) => (string)($item->content ?? ''), $conditions);
}

if (empty($conditionTitleRows)) {
    $conditionTitleRows = [''];
    $conditionContentRows = [''];
}

$itineraryDayRows = [];
$itineraryTitleRows = [];
$itineraryContentRows = [];

if (isset($old['itinerary_day_number']) && is_array($old['itinerary_day_number'])) {
    $itineraryDayRows = $old['itinerary_day_number'];
    $itineraryTitleRows = $old['itinerary_title'] ?? [];
    $itineraryContentRows = $old['itinerary_content'] ?? [];
} elseif (!empty($itinerary)) {
    $itineraryDayRows = array_map(fn($item) => (string)($item->day_number ?? ''), $itinerary);
    $itineraryTitleRows = array_map(fn($item) => (string)($item->title ?? ''), $itinerary);
    $itineraryContentRows = array_map(fn($item) => (string)($item->content ?? ''), $itinerary);
}

if (empty($itineraryDayRows)) {
    $itineraryDayRows = ['1'];
    $itineraryTitleRows = [''];
    $itineraryContentRows = [''];
}

$currentStatus = (string)($getRawValue('status', 'draft') ?: 'draft');
$selectedCurrencyId = (int)($getRawValue('currency_id', 0) ?: 0);
$selectedCurrencyCode = strtoupper((string)($getRawValue('currency', 'COP') ?: 'COP'));
if ($selectedCurrencyId <= 0 && !empty($currencies)) {
    foreach ($currencies as $currencyOption) {
        if (strtoupper((string)($currencyOption->code ?? '')) === $selectedCurrencyCode) {
            $selectedCurrencyId = (int)$currencyOption->id;
            break;
        }
    }
}
$noItinerary = array_key_exists('no_itinerary', $old)
    ? !empty($old['no_itinerary'])
    : ($mode === 'edit' && empty($itinerary));

$savedTemplatePayloads = [];
foreach ($templates as $template) {
    $payload = method_exists($template, 'payload') ? $template->payload() : json_decode((string)($template->payload_json ?? '{}'), true);
    $savedTemplatePayloads[] = [
        'value' => 'saved:' . (string)($template->slug ?? ''),
        'name' => (string)($template->name ?? ''),
        'payload' => is_array($payload) ? $payload : [],
    ];
}
?>
<style>
.package-wizard {
  --accent: #e11d48;
  --accent-dark: #be123c;
  --line: #e5e7eb;
  --soft: #f8fafc;
  --text: #111827;
  --muted: #6b7280;
  --success: #16a34a;
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 24px;
}

.package-wizard .wizard-sidebar {
  position: sticky;
  top: 24px;
  align-self: start;
  background: #fff;
  border: 1px solid var(--line);
  border-radius: 20px;
  padding: 20px;
}

.package-wizard .wizard-sidebar h3 {
  margin: 0 0 6px;
  font-size: 20px;
}

.package-wizard .wizard-sidebar p {
  margin: 0 0 18px;
  color: var(--muted);
  line-height: 1.6;
  font-size: 14px;
}

.package-wizard .step-nav {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.package-wizard .step-link {
  width: 100%;
  text-align: left;
  border: 1px solid var(--line);
  background: #fff;
  border-radius: 14px;
  padding: 12px 14px;
  cursor: pointer;
  font-weight: 700;
}

.package-wizard .step-link.active {
  background: #fff1f2;
  border-color: #fecdd3;
  color: var(--accent-dark);
}

.package-wizard .wizard-main {
  background: #fff;
  border: 1px solid var(--line);
  border-radius: 20px;
  padding: 24px;
}

.package-wizard .wizard-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.package-wizard .wizard-head h2 {
  margin: 0;
  font-size: 28px;
}

.package-wizard .wizard-head p {
  margin: 6px 0 0;
  color: var(--muted);
}

.package-wizard .step-panel {
  display: none;
}

.package-wizard .step-panel.active {
  display: block;
}

.package-wizard .field-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.package-wizard .field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.package-wizard .field.full {
  grid-column: 1 / -1;
}

.package-wizard .field label {
  font-size: 14px;
  font-weight: 800;
  color: var(--text);
}

.package-wizard .field small.help {
  color: var(--muted);
  font-size: 12px;
}

.package-wizard input[type="text"],
.package-wizard input[type="number"],
.package-wizard input[type="file"],
.package-wizard select,
.package-wizard textarea {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 14px;
  padding: 12px 14px;
  font: inherit;
  background: #fff;
}

.package-wizard textarea {
  min-height: 120px;
  resize: vertical;
}

.package-wizard .error {
  color: #b91c1c;
  font-size: 13px;
  font-weight: 700;
}

.package-wizard .alert {
  margin-bottom: 16px;
  padding: 12px 14px;
  border-radius: 12px;
  font-weight: 700;
}

.package-wizard .alert.error {
  background: #fee2e2;
  border: 1px solid #fecaca;
  color: #991b1b;
}

.package-wizard .card-block {
  background: var(--soft);
  border: 1px solid var(--line);
  border-radius: 18px;
  padding: 18px;
}

.package-wizard .block-title {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  margin-bottom: 14px;
  flex-wrap: wrap;
}

.package-wizard .block-title h3 {
  margin: 0;
  font-size: 18px;
}

.package-wizard .repeat-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.package-wizard .repeat-item {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 10px;
  align-items: start;
}

.package-wizard .repeat-item.condition {
  grid-template-columns: 1fr;
}

.package-wizard .condition-grid {
  display: grid;
  grid-template-columns: 1fr 2fr auto;
  gap: 10px;
  align-items: start;
}

.package-wizard .mini-btn,
.package-wizard .action-btn {
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 800;
  font: inherit;
}

.package-wizard .mini-btn {
  padding: 11px 12px;
  background: #fee2e2;
  color: #b91c1c;
}

.package-wizard .add-btn {
  padding: 10px 14px;
  background: #fff1f2;
  color: var(--accent-dark);
}

.package-wizard .template-select {
  max-width: 360px;
}

.package-wizard .check-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px 18px;
}

.package-wizard .check-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid var(--line);
  border-radius: 999px;
  padding: 10px 14px;
}

.package-wizard .media-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.package-wizard .preview-box {
  border: 1px dashed #cbd5e1;
  border-radius: 16px;
  padding: 14px;
  min-height: 170px;
  background: #fff;
}

.package-wizard .preview-box img {
  width: 100%;
  max-height: 220px;
  object-fit: cover;
  border-radius: 12px;
  display: block;
}

.package-wizard .gallery-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.package-wizard .gallery-card {
  border: 1px solid var(--line);
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
}

.package-wizard .gallery-card img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  display: block;
}

.package-wizard .gallery-card .gallery-meta {
  padding: 10px;
}

.package-wizard .status-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.package-wizard .btn-main,
.package-wizard .btn-outline,
.package-wizard .btn-publish {
  border: none;
  border-radius: 14px;
  padding: 12px 18px;
  cursor: pointer;
  font-weight: 800;
  font: inherit;
  text-decoration: none;
}

.package-wizard .btn-main {
  background: #0ea5e9;
  color: #fff;
}

.package-wizard .btn-outline {
  background: #fff;
  color: #4c1d95;
  border: 1px solid #d1d5db;
}

.package-wizard .btn-publish {
  background: var(--success);
  color: #fff;
}

.package-wizard .wizard-footer {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-top: 24px;
  flex-wrap: wrap;
}

.package-wizard .footer-left,
.package-wizard .footer-right {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.package-wizard .preview-summary {
  display: grid;
  grid-template-columns: 1.2fr .8fr;
  gap: 20px;
}

.package-wizard .summary-card {
  border: 1px solid var(--line);
  border-radius: 18px;
  padding: 18px;
  background: #fff;
}

.package-wizard .summary-card h3,
.package-wizard .summary-card h4 {
  margin: 0 0 12px;
}

.package-wizard .summary-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.package-wizard .summary-badge {
  background: #f3f4f6;
  border-radius: 999px;
  padding: 7px 11px;
  font-size: 12px;
  font-weight: 800;
}

@media (max-width: 1100px) {
  .package-wizard {
    grid-template-columns: 1fr;
  }

  .package-wizard .wizard-sidebar {
    position: static;
  }

  .package-wizard .preview-summary,
  .package-wizard .media-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 760px) {
  .package-wizard .field-grid,
  .package-wizard .condition-grid {
    grid-template-columns: 1fr;
  }

  .package-wizard .gallery-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 560px) {
  .package-wizard .gallery-grid {
    grid-template-columns: 1fr;
  }

  .package-wizard .wizard-main {
    padding: 18px;
  }
}
</style>

<div class="package-wizard" id="packageWizard">
  <aside class="wizard-sidebar">
    <h3><?= $mode === 'edit' ? 'Editar paquete' : 'Nuevo paquete' ?></h3>
    <p>Completa el contenido por bloques. Puedes guardar como borrador y publicar después.</p>

    <div class="field" style="margin-bottom:16px;">
      <label>Plantilla rápida</label>
      <select id="templateSelect" class="template-select">
        <option value="">Sin plantilla</option>
        <optgroup label="Plantillas base">
          <option value="nacional">Paquete nacional</option>
          <option value="internacional">Paquete internacional</option>
          <option value="escapada">Escapada corta</option>
          <option value="familia">Vacaciones familiares</option>
        </optgroup>
        <?php if (!empty($templates)): ?>
          <optgroup label="Mis plantillas guardadas">
            <?php foreach ($templates as $template): ?>
              <option value="saved:<?= htmlspecialchars((string)($template->slug ?? '')) ?>">
                <?= htmlspecialchars((string)($template->name ?? 'Plantilla')) ?>
              </option>
            <?php endforeach; ?>
          </optgroup>
        <?php endif; ?>
      </select>
      <small class="help">Selecciona una plantilla para llenar textos, servicios, condiciones, itinerario, etiquetas y datos sugeridos.</small>
      <small class="help"><a href="/admin/packageTour/templates" style="color:#4b148c;font-weight:900;">Gestionar plantillas</a></small>
    </div>

    <div class="field" style="margin-bottom:16px;">
      <label>Guardar este contenido como plantilla</label>
      <input type="text" name="template_name" id="templateNameInput" form="packageForm" placeholder="Ej: Paquete playa internacional">
      <small class="help">Guarda el contenido reutilizable: textos, ubicación/precio sugeridos, moneda, duración, servicios, condiciones, itinerario, etiquetas, destacado y popular. No guarda imágenes, slug, estado ni posición.</small>
      <button type="submit" form="packageForm" class="btn-outline" data-submit-action="save_template" style="margin-top:10px;width:100%;justify-content:center;">Guardar plantilla</button>
    </div>

    <div class="step-nav">
      <button type="button" class="step-link active" data-step-target="1">1. Básico</button>
      <button type="button" class="step-link" data-step-target="2">2. Comercial</button>
      <button type="button" class="step-link" data-step-target="3">3. Contenido</button>
      <button type="button" class="step-link" data-step-target="4">4. Servicios y condiciones</button>
      <button type="button" class="step-link" data-step-target="5">5. Itinerario</button>
<button type="button" class="step-link" data-step-target="6">6. Imágenes</button>
<button type="button" class="step-link" data-step-target="7">7. Vista previa y publicación</button>
    </div>
  </aside>

  <div class="wizard-main">
    <div class="wizard-head">
      <div>
        <h2><?= $mode === 'edit' ? 'Actualiza el paquete' : 'Crea un paquete sin fricción' ?></h2>
        <p>Lo importante primero. Luego completas detalles, imágenes y publicación.</p>
      </div>

      <div class="status-actions">
        <span class="summary-badge">Modo: <?= $mode === 'edit' ? 'edición' : 'creación' ?></span>
        <span class="summary-badge">Estado actual: <?= htmlspecialchars($currentStatus) ?></span>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($action) ?>" enctype="multipart/form-data" id="packageForm" novalidate>
      <?= Csrf::input(); ?>

      <?php if ($mode === 'edit' && $package): ?>
        <input type="hidden" name="id" value="<?= (int)$package->id ?>">
      <?php endif; ?>

      <input type="hidden" name="submit_action" id="submitActionInput" value="draft">

      <section class="step-panel active" data-step-panel="1">
        <div class="field-grid">
          <div class="field">
            <label>Título <small class="help">obligatorio</small></label>
            <input name="title" id="pkgTitle" value="<?= $getValue('title') ?>" placeholder="Ej: Santa Marta Mágica">
            <?php if ($getError('title')): ?><small class="error"><?= htmlspecialchars($getError('title')) ?></small><?php endif; ?>
          </div>

          <div class="field">
            <label>Slug <small class="help">se genera si lo dejas vacío</small></label>
            <input name="slug" id="pkgSlug" value="<?= $getValue('slug') ?>" placeholder="santa-marta-magica">
            <?php if ($getError('slug')): ?><small class="error"><?= htmlspecialchars($getError('slug')) ?></small><?php endif; ?>
          </div>

          <div class="field full">
            <label>Subtítulo <small class="help">opcional</small></label>
            <input name="subtitle" id="pkgSubtitle" value="<?= $getValue('subtitle') ?>" placeholder="Una experiencia inolvidable frente al mar">
          </div>

          <div class="field">
            <label>Ubicación <small class="help">recomendada para publicar</small></label>
            <input name="location_name" id="pkgLocation" value="<?= $getValue('location_name') ?>" placeholder="Santa Marta, Colombia">
            <?php if ($getError('location_name')): ?><small class="error"><?= htmlspecialchars($getError('location_name')) ?></small><?php endif; ?>
          </div>

          <div class="field">
            <label>Status</label>
            <select name="status" id="pkgStatus">
              <option value="draft" <?= $currentStatus === 'draft' ? 'selected' : '' ?>>Draft</option>
              <option value="published" <?= $currentStatus === 'published' ? 'selected' : '' ?>>Published</option>
              <option value="archived" <?= $currentStatus === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
          </div>

          <div class="field full">
            <label>Etiquetas</label>
            <?php if (empty($tags)): ?>
              <div class="empty-state" style="padding:14px; text-align:left;">
                No hay etiquetas activas. Primero créalas en
                <a href="/admin/packageTour/tags">Etiquetas de paquetes</a>.
              </div>
            <?php else: ?>
              <div class="check-grid">
                <?php foreach ($tags as $tag): ?>
                  <label class="check-chip">
                    <input
                      type="checkbox"
                      name="tag_ids[]"
                      value="<?= (int)$tag->id ?>"
                      <?= in_array((int)$tag->id, $selectedTags, true) ? 'checked' : '' ?>
                    >
                    <span style="display:inline-flex; align-items:center; gap:7px;">
                      <span style="width:10px; height:10px; border-radius:999px; background:<?= htmlspecialchars($tag->color ?? '#1FA4CF') ?>; border:1px solid #e5e7eb;"></span>
                      <?= htmlspecialchars($tag->name ?? '') ?>
                    </span>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="field full">
            <div class="check-grid">
              <label class="check-chip">
                
                <input type="checkbox" name="is_featured" value="1" <?= $isFeatured ? 'checked' : '' ?>>
                <span>Destacado</span>
              </label>

              <label class="check-chip">
                <input type="checkbox" name="is_popular" value="1" <?= $isPopular ? 'checked' : '' ?>>
                <span>Popular</span>
              </label>
            </div>
          </div>
        </div>
      </section>

      <section class="step-panel" data-step-panel="2">
        <div class="field-grid">
          <div class="field">
            <label>Precio desde <small class="help">requerido para publicar</small></label>
            <input name="price_from" id="pkgPrice" type="number" step="0.01" value="<?= $getValue('price_from') ?>" placeholder="1500000">
            <?php if ($getError('price_from')): ?><small class="error"><?= htmlspecialchars($getError('price_from')) ?></small><?php endif; ?>
          </div>

          <div class="field">
            <label>Moneda</label>
            <?php if (empty($currencies)): ?>
              <div class="empty-state" style="text-align:left; padding:14px;">
                No hay monedas activas. Primero créalas en
                <a href="/admin/currencies">Monedas</a>.
              </div>
              <input type="hidden" name="currency" id="pkgCurrency" value="">
              <input type="hidden" name="currency_id" id="pkgCurrencyId" value="">
            <?php else: ?>
              <select name="currency" id="pkgCurrency">
                <?php foreach ($currencies as $currencyOption): ?>
                  <?php
                    $optionId = (int)($currencyOption->id ?? 0);
                    $optionCode = strtoupper((string)($currencyOption->code ?? ''));
                    $selected = $selectedCurrencyId === $optionId || ($optionCode === $selectedCurrencyCode);
                  ?>
                  <option
                    value="<?= htmlspecialchars($optionCode) ?>"
                    data-id="<?= $optionId ?>"
                    data-code="<?= htmlspecialchars($optionCode) ?>"
                    data-symbol="<?= htmlspecialchars((string)($currencyOption->symbol ?? '')) ?>"
                    <?= $selected ? 'selected' : '' ?>
                  >
                    <?= htmlspecialchars($optionCode . ' - ' . (string)($currencyOption->name ?? '') . ' (' . (string)($currencyOption->symbol ?? '') . ')') ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <input type="hidden" name="currency_id" id="pkgCurrencyId" value="<?= (int)$selectedCurrencyId ?>">
              <small class="help">Gestiona nuevas monedas desde <a href="/admin/currencies">Monedas</a>.</small>
            <?php endif; ?>
            <?php if ($getError('currency_id')): ?><small class="error"><?= htmlspecialchars($getError('currency_id')) ?></small><?php endif; ?>
          </div>

          <div class="field">
            <label>Días</label>
            <input name="duration_days" id="pkgDays" type="number" value="<?= $getValue('duration_days') ?>" placeholder="4">
          </div>

          <div class="field">
            <label>Noches</label>
            <input name="duration_nights" id="pkgNights" type="number" value="<?= $getValue('duration_nights') ?>" placeholder="3">
          </div>

          <div class="field">
            <label>Posición en listado</label>
            <input name="sort_order" type="number" min="1" value="<?= $getValue('sort_order', '1') ?>" placeholder="Ej: 1">
            <small class="help">Si eliges una posición ocupada, los demás paquetes se corren automáticamente hacia abajo. Por defecto entra en la posición 1. Si eliges una posición ocupada, los demás se corren automáticamente hacia abajo.</small>
            <?php if ($getError('sort_order')): ?><small class="error"><?= htmlspecialchars($getError('sort_order')) ?></small><?php endif; ?>
          </div>
        </div>
      </section>

      <section class="step-panel" data-step-panel="3">
        <div class="field-grid">
          <div class="field full">
            <label>Descripción corta <small class="help">requerida para publicar</small></label>
            <textarea name="short_description" id="pkgShortDescription" placeholder="Describe el paquete en pocas líneas..."><?= htmlspecialchars((string)$getRawValue('short_description')) ?></textarea>
            <?php if ($getError('short_description')): ?><small class="error"><?= htmlspecialchars($getError('short_description')) ?></small><?php endif; ?>
          </div>

          <div class="field full">
            <label>Descripción general <small class="help">requerida para publicar</small></label>
            <textarea name="general_description" id="pkgGeneralDescription" placeholder="Cuenta la experiencia, el valor del paquete y por qué vale la pena..."><?= htmlspecialchars((string)$getRawValue('general_description')) ?></textarea>
            <?php if ($getError('general_description')): ?><small class="error"><?= htmlspecialchars($getError('general_description')) ?></small><?php endif; ?>
          </div>
        </div>
      </section>

      <section class="step-panel" data-step-panel="4">
        <div class="field-grid">
          <div class="field full">
            <div class="card-block">
              <div class="block-title">
                <h3>Incluye</h3>
                <button type="button" class="action-btn add-btn" data-add-repeat="includes">+ Agregar item</button>
              </div>
              <div class="repeat-list" id="includesList">
                <?php foreach ($includeRows as $item): ?>
                  <div class="repeat-item">
                    <input type="text" name="includes[]" value="<?= htmlspecialchars((string)$item) ?>" placeholder="Ej: Tiquetes ida y regreso">
                    <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="field full">
            <div class="card-block">
              <div class="block-title">
                <h3>No incluye</h3>
                <button type="button" class="action-btn add-btn" data-add-repeat="excludes">+ Agregar item</button>
              </div>
              <div class="repeat-list" id="excludesList">
                <?php foreach ($excludeRows as $item): ?>
                  <div class="repeat-item">
                    <input type="text" name="excludes[]" value="<?= htmlspecialchars((string)$item) ?>" placeholder="Ej: Gastos no especificados">
                    <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="field full">
            <div class="card-block">
              <div class="block-title">
                <h3>Highlights</h3>
                <button type="button" class="action-btn add-btn" data-add-repeat="highlights">+ Agregar highlight</button>
              </div>
              <div class="repeat-list" id="highlightsList">
                <?php foreach ($highlightRows as $item): ?>
                  <div class="repeat-item">
                    <input type="text" name="highlights[]" value="<?= htmlspecialchars((string)$item) ?>" placeholder="Ej: Desayuno incluido">
                    <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <div class="field full">
            <div class="card-block">
              <div class="block-title">
                <h3>Condiciones</h3>
                <button type="button" class="action-btn add-btn" data-add-repeat="conditions">+ Agregar condición</button>
              </div>
              <div class="repeat-list" id="conditionsList">
                <?php foreach ($conditionTitleRows as $i => $title): ?>
                  <div class="repeat-item condition">
                    <div class="condition-grid">
                      <input type="text" name="condition_titles[]" value="<?= htmlspecialchars((string)$title) ?>" placeholder="Título de la condición">
                      <textarea name="condition_contents[]" placeholder="Contenido de la condición"><?= htmlspecialchars((string)($conditionContentRows[$i] ?? '')) ?></textarea>
                      <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </section>
<section class="step-panel" data-step-panel="5">
  <div class="field-grid">
    <div class="field full">
      <div class="card-block">
        <div class="block-title">
          <div>
            <h3>Itinerario por días</h3>
            <small class="help">Si el itinerario aún no está definido, márcalo como no disponible.</small>
          </div>
          <button type="button" class="action-btn add-btn" data-add-repeat="itinerary" id="addItineraryBtn">+ Agregar día</button>
        </div>

        <label class="check-chip" style="margin-bottom:14px;">
          <input type="checkbox" name="no_itinerary" id="noItineraryInput" value="1" <?= $noItinerary ? 'checked' : '' ?>>
          <span>No mostrar itinerario por ahora</span>
        </label>

        <div class="empty-state" id="itineraryUnavailableBox" style="<?= $noItinerary ? '' : 'display:none;' ?> text-align:left; padding:14px; margin-bottom:12px;">
          En el detalle del paquete el cliente verá: <strong>El itinerario no está disponible.</strong>
        </div>

        <div class="repeat-list" id="itineraryList" style="<?= $noItinerary ? 'display:none;' : '' ?>">
          <?php foreach ($itineraryDayRows as $i => $dayNumber): ?>
            <div class="repeat-item condition">
              <div class="condition-grid" style="grid-template-columns:140px 1fr 2fr auto;">
                <input
                  type="number"
                  min="1"
                  name="itinerary_day_number[]"
                  value="<?= htmlspecialchars((string)$dayNumber) ?>"
                  placeholder="Día"
                >
                <input
                  type="text"
                  name="itinerary_title[]"
                  value="<?= htmlspecialchars((string)($itineraryTitleRows[$i] ?? '')) ?>"
                  placeholder="Título del día"
                >
                <textarea
                  name="itinerary_content[]"
                  placeholder="Describe las actividades o detalles del día"
                ><?= htmlspecialchars((string)($itineraryContentRows[$i] ?? '')) ?></textarea>
                <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>
      <section class="step-panel" data-step-panel="6">
        <div class="media-grid">
          <div class="card-block">
            <div class="block-title">
              <h3>Portada</h3>
            </div>

            <?php if ($mode === 'edit' && $cover): ?>
              <div class="preview-box" style="margin-bottom:12px;">
                <img src="/<?= ltrim(str_replace('\\', '/', (string)$cover->image_path), '/') ?>" alt="Portada actual">
              </div>
              <label class="check-chip" style="margin-bottom:12px;">
                <input type="checkbox" name="remove_cover" value="1">
                <span>Eliminar portada actual</span>
              </label>
            <?php endif; ?>

            <input type="file" name="cover_image_file" id="coverImageInput" accept="image/*">
            <?php if ($getError('cover_image_file')): ?><small class="error"><?= htmlspecialchars($getError('cover_image_file')) ?></small><?php endif; ?>

            <div class="preview-box" id="coverPreviewBox" style="margin-top:14px;">
              <span style="color:#6b7280;">Aquí verás la nueva portada seleccionada.</span>
            </div>
          </div>

          <div class="card-block">
            <div class="block-title">
              <h3>Galería</h3>
            </div>

            <?php if ($mode === 'edit' && !empty($gallery)): ?>
              <div class="gallery-grid" style="margin-bottom:14px;">
                <?php foreach ($gallery as $img): ?>
                  <div class="gallery-card">
                    <img src="/<?= ltrim(str_replace('\\', '/', (string)$img->image_path), '/') ?>" alt="">
                    <div class="gallery-meta">
                      <label style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="remove_gallery_ids[]" value="<?= (int)$img->id ?>">
                        <span>Eliminar</span>
                      </label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <label class="check-chip" style="margin-bottom:12px;">
                <input type="checkbox" name="clear_gallery" value="1">
                <span>Vaciar galería actual</span>
              </label>
            <?php endif; ?>

            <input type="file" name="gallery_files[]" id="galleryInput" accept="image/*" multiple>
            <?php if ($getError('gallery_files')): ?><small class="error"><?= htmlspecialchars($getError('gallery_files')) ?></small><?php endif; ?>

            <div class="gallery-grid" id="galleryPreviewGrid" style="margin-top:14px;"></div>
          </div>
        </div>
      </section>

      <section class="step-panel" data-step-panel="7">
        <div class="preview-summary">
          <div class="summary-card">
            <h3 id="previewTitle"><?= htmlspecialchars((string)$getRawValue('title', 'Tu paquete aparecerá aquí')) ?></h3>
            <p id="previewSubtitle" style="color:#6b7280; margin-top:0;">
              <?= htmlspecialchars((string)$getRawValue('subtitle', 'Subtítulo del paquete')) ?>
            </p>

            <div class="summary-badges" id="previewTagsBox" style="margin-bottom:16px;"></div>

            <p><strong>Ubicación:</strong> <span id="previewLocation"><?= htmlspecialchars((string)$getRawValue('location_name', '-')) ?></span></p>
            <p><strong>Precio desde:</strong> <span id="previewPrice"><?= htmlspecialchars((string)$getRawValue('price_from', '0')) ?></span> <span id="previewCurrency"><?= htmlspecialchars((string)$getRawValue('currency', 'COP')) ?></span></p>
            <p><strong>Duración:</strong> <span id="previewDuration"><?= htmlspecialchars((string)$getRawValue('duration_days', '-')) ?></span> días / <span id="previewNights"><?= htmlspecialchars((string)$getRawValue('duration_nights', '-')) ?></span> noches</p>

            <h4>Descripción corta</h4>
            <p id="previewShort"><?= nl2br(htmlspecialchars((string)$getRawValue('short_description', 'Sin descripción corta todavía.'))) ?></p>

            <h4>Descripción general</h4>
            <p id="previewGeneral"><?= nl2br(htmlspecialchars((string)$getRawValue('general_description', 'Sin descripción general todavía.'))) ?></p>
        <h4>Itinerario</h4>
<div id="previewItinerary">
  <p style="color:#6b7280;">Aún no has agregado días al itinerario.</p>
</div>  
        </div>

          <div class="summary-card">
            <h4>Checklist de publicación</h4>
            <ul style="padding-left:18px; line-height:1.9; color:#475569;">
              <li>Título</li>
              <li>Slug</li>
              <li>Ubicación</li>
              <li>Precio</li>
              <li>Descripción corta</li>
              <li>Descripción general</li>
              <li>Portada</li>
            </ul>

            <h4>Consejo</h4>
            <p style="color:#6b7280; line-height:1.7;">
              Guarda primero como borrador cuando estés armando el contenido. Usa publicar solo cuando el paquete ya esté listo visual y comercialmente.
            </p>
          </div>
        </div>
      </section>

      <div class="wizard-footer">
        <div class="footer-left">
          <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn-outline">Cancelar</a>
          <button type="button" class="btn-outline" id="prevStepBtn">Atrás</button>
        </div>

        <div class="footer-right">
          <button type="button" class="btn-main" id="nextStepBtn">Siguiente</button>
          <button type="submit" class="btn-outline" data-submit-action="draft">Guardar borrador</button>
          <button type="submit" class="btn-publish" data-submit-action="publish">Guardar y publicar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<template id="includeTemplate">
  <div class="repeat-item">
    <input type="text" name="includes[]" placeholder="Nuevo item incluido">
    <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
  </div>
</template>

<template id="excludeTemplate">
  <div class="repeat-item">
    <input type="text" name="excludes[]" placeholder="Nuevo item no incluido">
    <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
  </div>
</template>

<template id="highlightTemplate">
  <div class="repeat-item">
    <input type="text" name="highlights[]" placeholder="Nuevo highlight">
    <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
  </div>
</template>

<template id="conditionTemplate">
  <div class="repeat-item condition">
    <div class="condition-grid">
      <input type="text" name="condition_titles[]" placeholder="Título de la condición">
      <textarea name="condition_contents[]" placeholder="Contenido de la condición"></textarea>
      <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
    </div>
  </div>
</template>
<template id="itineraryTemplate">
  <div class="repeat-item condition">
    <div class="condition-grid" style="grid-template-columns:140px 1fr 2fr auto;">
      <input type="number" min="1" name="itinerary_day_number[]" placeholder="Día">
      <input type="text" name="itinerary_title[]" placeholder="Título del día">
      <textarea name="itinerary_content[]" placeholder="Describe las actividades o detalles del día"></textarea>
      <button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>
    </div>
  </div>
</template>
<script>
(function () {
  const wizard = document.getElementById('packageWizard');
  if (!wizard) return;

  const stepLinks = Array.from(wizard.querySelectorAll('.step-link'));
  const stepPanels = Array.from(wizard.querySelectorAll('.step-panel'));
  const prevBtn = document.getElementById('prevStepBtn');
  const nextBtn = document.getElementById('nextStepBtn');
  const form = document.getElementById('packageForm');
  const submitActionInput = document.getElementById('submitActionInput');
  const templateNameInput = document.getElementById('templateNameInput');
  const savedTemplates = <?= json_encode($savedTemplatePayloads, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  let currentStep = 1;

  function goToStep(step) {
    currentStep = Math.max(1, Math.min(step, stepPanels.length));

    stepLinks.forEach((btn) => {
      btn.classList.toggle('active', Number(btn.dataset.stepTarget) === currentStep);
    });

    stepPanels.forEach((panel) => {
      panel.classList.toggle('active', Number(panel.dataset.stepPanel) === currentStep);
    });

    prevBtn.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
    nextBtn.style.display = currentStep === stepPanels.length ? 'none' : 'inline-flex';
  }

  stepLinks.forEach((btn) => {
    btn.addEventListener('click', () => goToStep(Number(btn.dataset.stepTarget)));
  });

  prevBtn.addEventListener('click', () => goToStep(currentStep - 1));
  nextBtn.addEventListener('click', () => goToStep(currentStep + 1));

  goToStep(1);

  let clickedSubmitButton = null;

  document.querySelectorAll('[data-submit-action]').forEach((btn) => {
    btn.addEventListener('click', function (event) {
      clickedSubmitButton = this;
      const action = this.dataset.submitAction || 'draft';
      submitActionInput.value = action;

      if (action === 'save_template' && templateNameInput && !templateNameInput.value.trim()) {
        const suggested = (titleInput?.value?.trim() || 'Nueva plantilla');
        const value = window.prompt('Nombre de la plantilla:', suggested);
        if (!value || !value.trim()) {
          event.preventDefault();
          clickedSubmitButton = null;
          return;
        }
        templateNameInput.value = value.trim();
      }
    });
  });

  if (form) {
    form.addEventListener('submit', () => {
      updatePreview();

      const buttons = Array.from(form.querySelectorAll('button[type="submit"], button[form="packageForm"]'));
      buttons.forEach((button) => {
        button.disabled = true;
        button.style.opacity = '0.72';
        button.style.cursor = 'wait';
      });

      if (clickedSubmitButton) {
        const action = submitActionInput?.value || clickedSubmitButton.dataset.submitAction || 'draft';
        clickedSubmitButton.textContent = action === 'publish'
          ? 'Publicando...'
          : (action === 'save_template' ? 'Guardando plantilla...' : 'Guardando...');
      }
    });
  }

 const repeatMap = {
  includes: { listId: 'includesList', templateId: 'includeTemplate' },
  excludes: { listId: 'excludesList', templateId: 'excludeTemplate' },
  highlights: { listId: 'highlightsList', templateId: 'highlightTemplate' },
  conditions: { listId: 'conditionsList', templateId: 'conditionTemplate' },
  itinerary: { listId: 'itineraryList', templateId: 'itineraryTemplate' },
};

  const noItineraryInput = document.getElementById('noItineraryInput');
  const itineraryList = document.getElementById('itineraryList');
  const itineraryUnavailableBox = document.getElementById('itineraryUnavailableBox');
  const addItineraryBtn = document.getElementById('addItineraryBtn');

  function syncItineraryVisibility() {
    const disabled = !!noItineraryInput?.checked;
    if (itineraryList) itineraryList.style.display = disabled ? 'none' : '';
    if (itineraryUnavailableBox) itineraryUnavailableBox.style.display = disabled ? '' : 'none';
    if (addItineraryBtn) addItineraryBtn.style.display = disabled ? 'none' : '';
    updatePreview();
  }

  if (noItineraryInput) {
    noItineraryInput.addEventListener('change', syncItineraryVisibility);
  }

  document.querySelectorAll('[data-add-repeat]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const key = btn.dataset.addRepeat;
      const config = repeatMap[key];
      if (!config) return;

      if (key === 'itinerary' && noItineraryInput) {
        noItineraryInput.checked = false;
        syncItineraryVisibility();
      }

      const list = document.getElementById(config.listId);
      const tpl = document.getElementById(config.templateId);
      if (!list || !tpl) return;

      list.appendChild(tpl.content.cloneNode(true));
    });
  });

  document.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('[data-remove-repeat]');
    if (!removeBtn) return;

    const item = removeBtn.closest('.repeat-item');
    const list = item?.parentElement;
    if (!item || !list) return;

    if (list.children.length <= 1) {
      const fields = item.querySelectorAll('input[type="text"], input[type="number"], textarea');
      fields.forEach((field) => field.value = '');
      return;
    }

    item.remove();
  });

  const coverInput = document.getElementById('coverImageInput');
  const coverPreviewBox = document.getElementById('coverPreviewBox');

  if (coverInput && coverPreviewBox) {
    coverInput.addEventListener('change', () => {
      const file = coverInput.files && coverInput.files[0];
      if (!file) {
        coverPreviewBox.innerHTML = '<span style="color:#6b7280;">Aquí verás la nueva portada seleccionada.</span>';
        return;
      }

      const url = URL.createObjectURL(file);
      coverPreviewBox.innerHTML = '<img src="' + url + '" alt="Preview portada">';
    });
  }

  const galleryInput = document.getElementById('galleryInput');
  const galleryPreviewGrid = document.getElementById('galleryPreviewGrid');

  if (galleryInput && galleryPreviewGrid) {
    galleryInput.addEventListener('change', () => {
      galleryPreviewGrid.innerHTML = '';
      const files = Array.from(galleryInput.files || []);

      files.forEach((file) => {
        const url = URL.createObjectURL(file);
        const div = document.createElement('div');
        div.className = 'gallery-card';
        div.innerHTML = '<img src="' + url + '" alt=""><div class="gallery-meta">' + file.name + '</div>';
        galleryPreviewGrid.appendChild(div);
      });
    });
  }

  const titleInput = document.getElementById('pkgTitle');
  const slugInput = document.getElementById('pkgSlug');
  const subtitleInput = document.getElementById('pkgSubtitle');
  const locationInput = document.getElementById('pkgLocation');
  const priceInput = document.getElementById('pkgPrice');
  const currencyInput = document.getElementById('pkgCurrency');
  const currencyIdInput = document.getElementById('pkgCurrencyId');
  const daysInput = document.getElementById('pkgDays');
  const nightsInput = document.getElementById('pkgNights');
  const shortInput = document.getElementById('pkgShortDescription');
  const generalInput = document.getElementById('pkgGeneralDescription');

  const previewTitle = document.getElementById('previewTitle');
  const previewSubtitle = document.getElementById('previewSubtitle');
  const previewLocation = document.getElementById('previewLocation');
  const previewPrice = document.getElementById('previewPrice');
  const previewCurrency = document.getElementById('previewCurrency');
  const previewDuration = document.getElementById('previewDuration');
  const previewNights = document.getElementById('previewNights');
  const previewShort = document.getElementById('previewShort');
  const previewGeneral = document.getElementById('previewGeneral');
  const previewTagsBox = document.getElementById('previewTagsBox');
const previewItinerary = document.getElementById('previewItinerary');
  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function nl2brSafe(str) {
    return escapeHtml(str).replace(/\n/g, '<br>');
  }

  function updatePreview() {
    previewTitle.textContent = titleInput?.value.trim() || 'Tu paquete aparecerá aquí';
    previewSubtitle.textContent = subtitleInput?.value.trim() || 'Subtítulo del paquete';
    previewLocation.textContent = locationInput?.value.trim() || '-';
    previewPrice.textContent = priceInput?.value.trim() || '0';
    const selectedCurrencyOption = currencyInput?.selectedOptions ? currencyInput.selectedOptions[0] : null;
    const currencyCode = selectedCurrencyOption?.dataset?.code || currencyInput?.value?.trim() || 'COP';
    previewCurrency.textContent = currencyCode;
    if (currencyInput && currencyCode) currencyInput.value = currencyCode;
    if (currencyIdInput && selectedCurrencyOption?.dataset?.id) currencyIdInput.value = selectedCurrencyOption.dataset.id;
    previewDuration.textContent = daysInput?.value.trim() || '-';
    previewNights.textContent = nightsInput?.value.trim() || '-';
    previewShort.innerHTML = nl2brSafe(shortInput?.value.trim() || 'Sin descripción corta todavía.');
    previewGeneral.innerHTML = nl2brSafe(generalInput?.value.trim() || 'Sin descripción general todavía.');

    previewTagsBox.innerHTML = '';
    document.querySelectorAll('input[name="tag_ids[]"]:checked').forEach((checkbox) => {
      const text = checkbox.closest('label')?.innerText?.trim() || 'Tag';
      const span = document.createElement('span');
      span.className = 'summary-badge';
      span.textContent = text;
      previewTagsBox.appendChild(span);
    });
    

    if (previewItinerary) {
  if (noItineraryInput?.checked) {
    previewItinerary.innerHTML = '<p style="color:#6b7280;">El itinerario no está disponible.</p>';
    return;
  }

  const dayInputs = Array.from(document.querySelectorAll('input[name="itinerary_day_number[]"]'));
  const titleInputs = Array.from(document.querySelectorAll('input[name="itinerary_title[]"]'));
  const contentInputs = Array.from(document.querySelectorAll('textarea[name="itinerary_content[]"]'));

  const items = [];

  for (let i = 0; i < Math.max(dayInputs.length, titleInputs.length, contentInputs.length); i++) {
    const day = dayInputs[i]?.value?.trim() || '';
    const title = titleInputs[i]?.value?.trim() || '';
    const content = contentInputs[i]?.value?.trim() || '';

    if (!day && !title && !content) continue;

    items.push({
      day: day || String(i + 1),
      title: title || ('Día ' + (day || (i + 1))),
      content: content || ''
    });
  }

  if (!items.length) {
    previewItinerary.innerHTML = '<p style="color:#6b7280;">Aún no has agregado días al itinerario.</p>';
  } else {
    previewItinerary.innerHTML = items.map((item) => {
      return '<div style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;">' +
        '<strong>Día ' + escapeHtml(item.day) + ': ' + escapeHtml(item.title) + '</strong>' +
        '<div style="margin-top:6px;color:#475569;">' + nl2brSafe(item.content || 'Sin descripción') + '</div>' +
      '</div>';
    }).join('');
  }
}
  }

  [titleInput, subtitleInput, locationInput, priceInput, daysInput, nightsInput, shortInput, generalInput]
    .forEach((el) => {
      if (el) el.addEventListener('input', updatePreview);
    });

  if (currencyInput) {
    currencyInput.addEventListener('change', updatePreview);
  }

  document.querySelectorAll('input[name="tag_ids[]"]').forEach((el) => {
    el.addEventListener('change', updatePreview);
  });

  document.addEventListener('input', (e) => {
  if (
    e.target.matches('input[name="itinerary_day_number[]"]') ||
    e.target.matches('input[name="itinerary_title[]"]') ||
    e.target.matches('textarea[name="itinerary_content[]"]')
  ) {
    updatePreview();
  }
});
  updatePreview();

  function slugify(value) {
    return String(value)
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  if (titleInput && slugInput) {
    titleInput.addEventListener('input', () => {
      if (!slugInput.dataset.manual || slugInput.dataset.manual !== '1') {
        slugInput.value = slugify(titleInput.value);
      }
      updatePreview();
    });

    slugInput.addEventListener('input', () => {
      if (slugInput.value.trim() !== '') {
        slugInput.dataset.manual = '1';
      } else {
        delete slugInput.dataset.manual;
      }
    });
  }

  const templateSelect = document.getElementById('templateSelect');

  const presets = {
    nacional: {
      currency: 'COP',
      includes: ['Tiquetes ida y regreso', 'Alojamiento', 'Desayunos', 'Asistencia médica'],
      excludes: ['Gastos no especificados', 'Traslados no indicados'],
      highlights: ['Plan nacional', 'Ideal para descanso', 'Atención personalizada'],
      conditions: [
        ['Reserva con anticipación', 'Tarifas sujetas a disponibilidad y cambios sin previo aviso.'],
        ['Documentación', 'El viajero debe portar sus documentos de identidad vigentes.']
      ]
    },
    internacional: {
      currency: 'USD',
      includes: ['Tiquetes aéreos', 'Alojamiento', 'Desayunos', 'Asistencia médica internacional'],
      excludes: ['Impuestos no especificados', 'Trámite de visa', 'Gastos personales'],
      highlights: ['Viaje internacional', 'Acompañamiento de agencia', 'Ideal para vacaciones'],
      conditions: [
        ['Documentación internacional', 'El pasajero debe cumplir requisitos migratorios del destino.'],
        ['Tarifa', 'Tarifa sujeta a cambios según disponibilidad aérea y hotelera.']
      ]
    },
    escapada: {
      currency: 'COP',
      includes: ['Alojamiento', 'Desayuno', 'Seguro básico'],
      excludes: ['Transporte no especificado', 'Alimentación no mencionada'],
      highlights: ['Escapada corta', 'Plan flexible', 'Perfecto para fin de semana'],
      conditions: [
        ['Vigencia', 'Tarifas válidas para fechas seleccionadas y sujetas a confirmación.']
      ]
    },
    familia: {
      currency: 'COP',
      includes: ['Alojamiento familiar', 'Desayuno', 'Actividades recreativas'],
      excludes: ['Gastos personales', 'Servicios no mencionados'],
      highlights: ['Ideal para familia', 'Experiencia cómoda', 'Ambiente seguro'],
      conditions: [
        ['Ocupación', 'Las tarifas aplican según acomodación y disponibilidad del hotel.']
      ]
    }
  };

  savedTemplates.forEach((template) => {
    if (template && template.value) {
      presets[template.value] = template.payload || {};
    }
  });

  function fillSimpleList(listId, name, items) {
    const list = document.getElementById(listId);
    if (!list) return;

    list.innerHTML = '';
    items.forEach((item) => {
      const div = document.createElement('div');
      div.className = 'repeat-item';
      div.innerHTML =
        '<input type="text" name="' + name + '[]" value="' + escapeHtml(item) + '">' +
        '<button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>';
      list.appendChild(div);
    });

    if (!items.length) {
      const div = document.createElement('div');
      div.className = 'repeat-item';
      div.innerHTML =
        '<input type="text" name="' + name + '[]" value="">' +
        '<button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>';
      list.appendChild(div);
    }
  }

  function fillConditions(items) {
    const list = document.getElementById('conditionsList');
    if (!list) return;

    list.innerHTML = '';
    items.forEach((item) => {
      const div = document.createElement('div');
      div.className = 'repeat-item condition';
      div.innerHTML =
        '<div class="condition-grid">' +
          '<input type="text" name="condition_titles[]" value="' + escapeHtml(item[0] || '') + '">' +
          '<textarea name="condition_contents[]">' + escapeHtml(item[1] || '') + '</textarea>' +
          '<button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>' +
        '</div>';
      list.appendChild(div);
    });

    if (!items.length) {
      const div = document.createElement('div');
      div.className = 'repeat-item condition';
      div.innerHTML =
        '<div class="condition-grid">' +
          '<input type="text" name="condition_titles[]" value="">' +
          '<textarea name="condition_contents[]"></textarea>' +
          '<button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>' +
        '</div>';
      list.appendChild(div);
    }
  }

  function fillItinerary(items, noItinerary) {
    const list = document.getElementById('itineraryList');
    if (!list) return;

    if (noItineraryInput) {
      noItineraryInput.checked = !!noItinerary;
    }

    list.innerHTML = '';

    (items || []).forEach((item, index) => {
      const day = item.day_number || item.day || (index + 1);
      const title = item.title || ('Día ' + day);
      const content = item.content || '';
      const div = document.createElement('div');
      div.className = 'repeat-item condition';
      div.innerHTML =
        '<div class="condition-grid" style="grid-template-columns:140px 1fr 2fr auto;">' +
          '<input type="number" min="1" name="itinerary_day_number[]" value="' + escapeHtml(day) + '" placeholder="Día">' +
          '<input type="text" name="itinerary_title[]" value="' + escapeHtml(title) + '" placeholder="Título del día">' +
          '<textarea name="itinerary_content[]" placeholder="Describe las actividades o detalles del día">' + escapeHtml(content) + '</textarea>' +
          '<button type="button" class="mini-btn" data-remove-repeat>Eliminar</button>' +
        '</div>';
      list.appendChild(div);
    });

    if (!items || !items.length) {
      const tpl = document.getElementById('itineraryTemplate');
      if (tpl) list.appendChild(tpl.content.cloneNode(true));
    }

    syncItineraryVisibility();
  }

  function setInputValue(input, value, onlyIfEmpty = false) {
    if (!input) return;
    if (onlyIfEmpty && input.value.trim()) return;
    input.value = value ?? '';
  }

  function setCurrencyByCode(code, onlyIfEmpty = false) {
    if (!currencyInput) return;
    const currentCode = currencyInput.selectedOptions?.[0]?.dataset?.code || currencyInput.value || '';
    if (onlyIfEmpty && currentCode) return;

    const normalized = String(code || '').toUpperCase();
    const option = Array.from(currencyInput.options || []).find((item) => (item.dataset.code || '').toUpperCase() === normalized);
    if (option) {
      currencyInput.value = option.value;
      if (currencyIdInput && option.dataset.id) currencyIdInput.value = option.dataset.id;
    }
  }

  if (templateSelect) {
    templateSelect.addEventListener('change', () => {
      const preset = presets[templateSelect.value];
      if (!preset) return;

      setInputValue(titleInput, preset.title || '', true);
      setInputValue(subtitleInput, preset.subtitle || '', true);
      setInputValue(locationInput, preset.location_name || '', true);
      setInputValue(priceInput, preset.price_from || '', true);
      setInputValue(shortInput, preset.short_description || '', true);
      setInputValue(generalInput, preset.general_description || '', true);
      setCurrencyByCode(preset.currency || 'COP', true);
      setInputValue(daysInput, preset.duration_days || '', true);
      setInputValue(nightsInput, preset.duration_nights || '', true);

      fillSimpleList('includesList', 'includes', preset.includes || []);
      fillSimpleList('excludesList', 'excludes', preset.excludes || []);
      fillSimpleList('highlightsList', 'highlights', preset.highlights || []);
      fillConditions(preset.conditions || []);
      fillItinerary(preset.itinerary || [], !!preset.no_itinerary);

      if (Array.isArray(preset.tag_ids)) {
        document.querySelectorAll('input[name="tag_ids[]"]').forEach((checkbox) => {
          checkbox.checked = preset.tag_ids.map(Number).includes(Number(checkbox.value));
        });
      }

      const featuredInput = document.querySelector('input[name="is_featured"]');
      const popularInput = document.querySelector('input[name="is_popular"]');
      if (featuredInput && preset.is_featured !== undefined) featuredInput.checked = !!Number(preset.is_featured);
      if (popularInput && preset.is_popular !== undefined) popularInput.checked = !!Number(preset.is_popular);

      updatePreview();
    });
  }

  syncItineraryVisibility();
})();
</script>
