<?php
$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];
$success = $success ?? false;

function exp_old(array $old, string $field): string
{
    return htmlspecialchars((string)($old[$field] ?? ''));
}

function exp_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Compartir experiencia</title>
  <style>
    .wrap{max-width:820px;margin:0 auto;padding:32px 20px 60px;}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:22px;padding:24px;}
    .head h1{margin:0;color:#0f172a;font-size:36px;}
    .head p{color:#64748b;line-height:1.7;}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .field{display:flex;flex-direction:column;gap:8px;}
    .field.full{grid-column:1/-1;}
    .field label{font-weight:800;color:#0f172a;}
    .field input,.field select,.field textarea{width:100%;border:1px solid #cbd5e1;border-radius:14px;padding:12px 14px;font:inherit;}
    .field textarea{min-height:140px;resize:vertical;}
    .btn{background:#b61f2a;color:#fff;border:none;border-radius:14px;padding:12px 18px;font-weight:800;cursor:pointer;}
    .alert{margin-bottom:16px;padding:12px 14px;border-radius:12px;font-weight:700;}
    .alert.ok{background:#ecfdf5;color:#166534;border:1px solid #bbf7d0;}
    .alert.err{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;}
    .err{color:#b91c1c;font-size:13px;font-weight:700;}
    @media (max-width:700px){.grid{grid-template-columns:1fr;}}
  </style>
</head>
<body>
  <main class="wrap">
    <div class="card">
      <div class="head">
        <h1>Compartir mi experiencia</h1>
        <p>Cuéntanos cómo fue tu experiencia. El contenido quedará pendiente de revisión antes de publicarse.</p>
      </div>

      <?php if ($message): ?>
        <div class="alert <?= $success ? 'ok' : 'err' ?>"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST" action="/experiences/share" enctype="multipart/form-data">
        <?= \app\Core\Csrf::input(); ?>

        <div class="grid">
          <div class="field">
            <label>Nombre</label>
            <input name="customer_name" value="<?= exp_old($old, 'customer_name') ?>">
            <?php if (exp_error($errors, 'customer_name')): ?><small class="err"><?= htmlspecialchars(exp_error($errors, 'customer_name')) ?></small><?php endif; ?>
          </div>

          <div class="field">
            <label>Nombre visible</label>
            <input name="display_name" value="<?= exp_old($old, 'display_name') ?>" placeholder="Ej: María P.">
          </div>

          <div class="field">
            <label>Correo</label>
            <input type="email" name="customer_email" value="<?= exp_old($old, 'customer_email') ?>">
            <?php if (exp_error($errors, 'customer_email')): ?><small class="err"><?= htmlspecialchars(exp_error($errors, 'customer_email')) ?></small><?php endif; ?>
          </div>

          <div class="field">
            <label>Teléfono</label>
            <input name="customer_phone" value="<?= exp_old($old, 'customer_phone') ?>">
          </div>

          <div class="field">
            <label>Tipo de experiencia</label>
            <select name="experience_type">
              <option value="general">General</option>
              <option value="package">Paquete</option>
              <option value="tickets">Tiquetes</option>
              <option value="extra_service">Servicio extra</option>
            </select>
          </div>

          <div class="field">
            <label>Calificación</label>
            <select name="rating">
              <option value="5">5</option>
              <option value="4">4</option>
              <option value="3">3</option>
              <option value="2">2</option>
              <option value="1">1</option>
            </select>
          </div>

          <div class="field">
            <label>Ciudad destino</label>
            <input name="city_destination" value="<?= exp_old($old, 'city_destination') ?>">
          </div>

          <div class="field">
            <label>País destino</label>
            <input name="country_destination" value="<?= exp_old($old, 'country_destination') ?>">
          </div>

          <div class="field full">
            <label>Título</label>
            <input name="title" value="<?= exp_old($old, 'title') ?>" placeholder="Ej: Un viaje inolvidable con mi familia">
            <?php if (exp_error($errors, 'title')): ?><small class="err"><?= htmlspecialchars(exp_error($errors, 'title')) ?></small><?php endif; ?>
          </div>

          <div class="field full">
            <label>Cuéntanos tu experiencia</label>
            <textarea name="story"><?= exp_old($old, 'story') ?></textarea>
            <?php if (exp_error($errors, 'story')): ?><small class="err"><?= htmlspecialchars(exp_error($errors, 'story')) ?></small><?php endif; ?>
          </div>
          <input type="hidden" name="package_id" value="<?= exp_old($old, 'package_id') ?>">
<input type="hidden" name="package_slug" value="<?= exp_old($old, 'package_slug') ?>">
<input type="hidden" name="extra_service_id" value="<?= exp_old($old, 'extra_service_id') ?>">
<input type="hidden" name="extra_service_slug" value="<?= exp_old($old, 'extra_service_slug') ?>">
              <div class="field full">
  <label>Fotos (opcional)</label>
  <input type="file" name="experience_images[]" accept="image/*" multiple>
</div>
          <?php if (exp_error($errors, 'contact')): ?>
            <div class="field full">
              <small class="err"><?= htmlspecialchars(exp_error($errors, 'contact')) ?></small>
            </div>
          <?php endif; ?>

          <div class="field full">
            <button class="btn" type="submit">Enviar experiencia</button>
          </div>
        </div>
      </form>
    </div>
  </main>
</body>
</html>