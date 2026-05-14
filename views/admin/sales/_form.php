<?php
use app\Core\Csrf;

$mode = $mode ?? 'create';
$action = $action ?? '/admin/sales/create';
$submitText = $submitText ?? 'Crear oportunidad';
$cancelUrl = $cancelUrl ?? '/admin/sales';
$item = $item ?? null;
$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function sales_form_old(array $old, string $field, string $default = ''): string
{
    return htmlspecialchars((string)($old[$field] ?? $default));
}

function sales_form_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>

<div class="sales-create-card">
  <?php if ($message): ?>
    <div class="sales-alert"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= htmlspecialchars($action) ?>">
    <?= Csrf::input(); ?>

    <?php if ($mode === 'edit' && $item): ?>
      <input type="hidden" name="id" value="<?= (int)$item->id ?>">
    <?php endif; ?>

    <div class="sales-form-grid">
      <div class="sales-field">
        <label>Nombre del cliente</label>
        <input name="customer_name" value="<?= sales_form_old($old, 'customer_name') ?>">
        <?php if (sales_form_error($errors, 'customer_name')): ?>
          <small class="sales-error"><?= htmlspecialchars(sales_form_error($errors, 'customer_name')) ?></small>
        <?php endif; ?>
      </div>

      <div class="sales-field">
        <label>Teléfono</label>
        <input name="customer_phone" value="<?= sales_form_old($old, 'customer_phone') ?>">
        <?php if (sales_form_error($errors, 'customer_phone')): ?>
          <small class="sales-error"><?= htmlspecialchars(sales_form_error($errors, 'customer_phone')) ?></small>
        <?php endif; ?>
      </div>

      <div class="sales-field">
        <label>Correo</label>
        <input type="email" name="customer_email" value="<?= sales_form_old($old, 'customer_email') ?>">
        <?php if (sales_form_error($errors, 'customer_email')): ?>
          <small class="sales-error"><?= htmlspecialchars(sales_form_error($errors, 'customer_email')) ?></small>
        <?php endif; ?>
      </div>

      <div class="sales-field">
        <label>Tipo de interés</label>
        <select name="interest_type">
          <option value="other" <?= sales_form_old($old, 'interest_type') === 'other' ? 'selected' : '' ?>>Otro</option>
          <option value="package" <?= sales_form_old($old, 'interest_type') === 'package' ? 'selected' : '' ?>>Paquete</option>
          <option value="tickets" <?= sales_form_old($old, 'interest_type') === 'tickets' ? 'selected' : '' ?>>Tiquetes</option>
          <option value="extra_service" <?= sales_form_old($old, 'interest_type') === 'extra_service' ? 'selected' : '' ?>>Servicio extra</option>
          <option value="visa_passport" <?= sales_form_old($old, 'interest_type') === 'visa_passport' ? 'selected' : '' ?>>Visa / tramites</option>
          <option value="medical_assistance" <?= sales_form_old($old, 'interest_type') === 'medical_assistance' ? 'selected' : '' ?>>Asistencia médica</option>
          <option value="simcard" <?= sales_form_old($old, 'interest_type') === 'simcard' ? 'selected' : '' ?>>Simcard</option>
          <option value="language_course" <?= sales_form_old($old, 'interest_type') === 'language_course' ? 'selected' : '' ?>>Curso de idiomas</option>
          <option value="receptive_tour" <?= sales_form_old($old, 'interest_type') === 'receptive_tour' ? 'selected' : '' ?>>Tour receptivo</option>
        </select>
      </div>

      <div class="sales-field">
        <label>Origen</label>
        <select name="source_origin">
          <option value="direct_whatsapp" <?= sales_form_old($old, 'source_origin', 'direct_whatsapp') === 'direct_whatsapp' ? 'selected' : '' ?>>WhatsApp directo</option>
          <option value="chatbot_whatsapp" <?= sales_form_old($old, 'source_origin') === 'chatbot_whatsapp' ? 'selected' : '' ?>>Chatbot</option>
          <option value="manual_admin" <?= sales_form_old($old, 'source_origin') === 'manual_admin' ? 'selected' : '' ?>>Manual admin</option>
          <option value="web_whatsapp" <?= sales_form_old($old, 'source_origin') === 'web_whatsapp' ? 'selected' : '' ?>>WhatsApp web</option>
          <option value="web_form" <?= sales_form_old($old, 'source_origin') === 'web_form' ? 'selected' : '' ?>>Formulario web</option>
          <option value="other" <?= sales_form_old($old, 'source_origin') === 'other' ? 'selected' : '' ?>>Otro</option>
        </select>
      </div>

      <div class="sales-field">
        <label>Canal</label>
        <select name="source_channel">
          <option value="whatsapp" <?= sales_form_old($old, 'source_channel', 'whatsapp') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
          <option value="phone" <?= sales_form_old($old, 'source_channel') === 'phone' ? 'selected' : '' ?>>Teléfono</option>
          <option value="facebook" <?= sales_form_old($old, 'source_channel') === 'facebook' ? 'selected' : '' ?>>Facebook</option>
          <option value="instagram" <?= sales_form_old($old, 'source_channel') === 'instagram' ? 'selected' : '' ?>>Instagram</option>
          <option value="email" <?= sales_form_old($old, 'source_channel') === 'email' ? 'selected' : '' ?>>Email</option>
          <option value="manual" <?= sales_form_old($old, 'source_channel') === 'manual' ? 'selected' : '' ?>>Manual</option>
          <option value="other" <?= sales_form_old($old, 'source_channel') === 'other' ? 'selected' : '' ?>>Otro</option>
        </select>
      </div>

      <div class="sales-field">
        <label>Slug paquete</label>
        <input name="package_slug" value="<?= sales_form_old($old, 'package_slug') ?>" placeholder="Opcional">
      </div>

      <div class="sales-field">
        <label>Slug servicio extra</label>
        <input name="extra_service_slug" value="<?= sales_form_old($old, 'extra_service_slug') ?>" placeholder="Opcional">
      </div>

      <div class="sales-field">
        <label>Temperatura</label>
        <select name="sales_temperature">
          <option value="cold" <?= sales_form_old($old, 'sales_temperature') === 'cold' ? 'selected' : '' ?>>Frío</option>
          <option value="warm" <?= sales_form_old($old, 'sales_temperature', 'warm') === 'warm' ? 'selected' : '' ?>>Medio</option>
          <option value="hot" <?= sales_form_old($old, 'sales_temperature') === 'hot' ? 'selected' : '' ?>>Caliente</option>
        </select>
      </div>

      <div class="sales-field">
        <label>Probabilidad de cierre (%)</label>
        <input type="number" name="closing_probability" value="<?= sales_form_old($old, 'closing_probability', '15') ?>">
      </div>

      <div class="sales-field">
        <label>Viajeros</label>
        <input type="number" name="travelers_count" value="<?= sales_form_old($old, 'travelers_count') ?>">
      </div>

      <div class="sales-field">
        <label>Fecha estimada de viaje</label>
        <input type="date" name="travel_date_estimate" value="<?= sales_form_old($old, 'travel_date_estimate') ?>">
      </div>

      <div class="sales-field">
        <label>Fecha estimada de regreso</label>
        <input type="date" name="return_date_estimate" value="<?= sales_form_old($old, 'return_date_estimate') ?>">
      </div>

      <div class="sales-field">
        <label>Presupuesto mínimo</label>
        <input type="number" step="0.01" name="budget_min" value="<?= sales_form_old($old, 'budget_min') ?>">
      </div>

      <div class="sales-field">
        <label>Presupuesto máximo</label>
        <input type="number" step="0.01" name="budget_max" value="<?= sales_form_old($old, 'budget_max') ?>">
        <?php if (sales_form_error($errors, 'budget_max')): ?>
          <small class="sales-error"><?= htmlspecialchars(sales_form_error($errors, 'budget_max')) ?></small>
        <?php endif; ?>
      </div>

      <div class="sales-field">
        <label>Próximo seguimiento</label>
        <input type="datetime-local" name="next_follow_up_at" value="<?= sales_form_old($old, 'next_follow_up_at') ?>">
      </div>

      <div class="sales-field full">
        <label>Referencia origen</label>
        <input name="source_reference" value="<?= sales_form_old($old, 'source_reference') ?>" placeholder="Ej: conversación directa por WhatsApp">
      </div>

      <div class="sales-field full">
        <label>Resumen / notas</label>
        <textarea name="notes_summary"><?= sales_form_old($old, 'notes_summary') ?></textarea>
      </div>
    </div>

    <div class="sales-actions">
      <a href="<?= htmlspecialchars($cancelUrl) ?>" class="btn-outline">Cancelar</a>
      <button type="submit" class="btn-main"><?= htmlspecialchars($submitText) ?></button>
    </div>
  </form>
</div>
