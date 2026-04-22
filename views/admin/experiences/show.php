<?php
use app\Core\Csrf;

$item = $item ?? null;
$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];
$images = $images ?? [];
if (!$item) {
    echo 'Experiencia no encontrada';
    return;
}

function exp_admin_value($old, $item, $field): string
{
    return htmlspecialchars((string)($old[$field] ?? $item->$field ?? ''));
}

function exp_admin_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Moderar experiencia</title>
  <link rel="stylesheet" href="/public/styles/admin.css">
  <style>
    .exp-detail-page {
      display:flex;
      flex-direction:column;
      gap:20px;
    }

    .exp-detail-actions,
    .exp-status-actions {
      display:flex;
      gap:12px;
      flex-wrap:wrap;
    }

    .exp-detail-actions > *,
    .exp-status-actions > * {
      min-width:180px;
    }

    .exp-image-grid {
      display:grid;
      grid-template-columns:repeat(3, minmax(0, 1fr));
      gap:12px;
    }

    .exp-image-card {
      border:1px solid #e5e7eb;
      border-radius:16px;
      overflow:hidden;
      background:#fff;
    }

    .exp-image-card img {
      width:100%;
      height:180px;
      object-fit:cover;
      display:block;
    }

    @media (max-width: 900px) {
      .exp-image-grid {
        grid-template-columns:repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 720px) {
      .exp-detail-actions > *,
      .exp-status-actions > * {
        width:100%;
      }

      .exp-image-grid {
        grid-template-columns:1fr;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <main class="main">
      <section class="content exp-detail-page">
        <div class="top-left">
          <div class="page-title">
            <h1>Moderar experiencia</h1>
            <p>RevisiÃ³n, ediciÃ³n y publicaciÃ³n controlada.</p>
          </div>
        </div>

        <div class="card">
          <?php if ($message): ?>
            <div style="margin-bottom:16px;color:#b91c1c;font-weight:700;">
              <?= htmlspecialchars($message) ?>
            </div>
          <?php endif; ?>

          <form method="POST" action="/admin/experiences/update" class="form">
            <?= Csrf::input(); ?>
            <input type="hidden" name="id" value="<?= (int)$item->id ?>">

            <div class="field">
              <label>Nombre cliente</label>
              <input name="customer_name" value="<?= exp_admin_value($old, $item, 'customer_name') ?>">
            </div>

            <div class="field">
              <label>Nombre visible</label>
              <input name="display_name" value="<?= exp_admin_value($old, $item, 'display_name') ?>">
            </div>

            <div class="field">
              <label>Correo</label>
              <input name="customer_email" value="<?= exp_admin_value($old, $item, 'customer_email') ?>">
            </div>

            <div class="field">
              <label>TelÃ©fono</label>
              <input name="customer_phone" value="<?= exp_admin_value($old, $item, 'customer_phone') ?>">
            </div>

            <div class="field">
              <label>Tipo</label>
              <select name="experience_type">
                <option value="general" <?= (($old['experience_type'] ?? $item->experience_type ?? '') === 'general') ? 'selected' : '' ?>>General</option>
                <option value="package" <?= (($old['experience_type'] ?? $item->experience_type ?? '') === 'package') ? 'selected' : '' ?>>Paquete</option>
                <option value="tickets" <?= (($old['experience_type'] ?? $item->experience_type ?? '') === 'tickets') ? 'selected' : '' ?>>Tiquetes</option>
                <option value="extra_service" <?= (($old['experience_type'] ?? $item->experience_type ?? '') === 'extra_service') ? 'selected' : '' ?>>Servicio extra</option>
              </select>
            </div>

            <div class="field">
              <label>Rating</label>
              <select name="rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <option value="<?= $i ?>" <?= ((int)($old['rating'] ?? $item->rating ?? 5) === $i) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
              </select>
            </div>

            <div class="field">
              <label>Ciudad destino</label>
              <input name="city_destination" value="<?= exp_admin_value($old, $item, 'city_destination') ?>">
            </div>

            <div class="field">
              <label>PaÃ­s destino</label>
              <input name="country_destination" value="<?= exp_admin_value($old, $item, 'country_destination') ?>">
            </div>

            <div class="field">
              <label>Slug paquete</label>
              <input name="package_slug" value="<?= exp_admin_value($old, $item, 'package_slug') ?>">
            </div>

            <div class="field">
              <label>Slug servicio extra</label>
              <input name="extra_service_slug" value="<?= exp_admin_value($old, $item, 'extra_service_slug') ?>">
            </div>

            <div class="field" style="grid-column:1/-1;">
              <label>TÃ­tulo</label>
              <input name="title" value="<?= exp_admin_value($old, $item, 'title') ?>">
            </div>

            <div class="field" style="grid-column:1/-1;">
              <label>Historia</label>
              <textarea name="story"><?= htmlspecialchars((string)($old['story'] ?? $item->story ?? '')) ?></textarea>
            </div>

            <div class="field" style="grid-column:1/-1;">
              <label>Notas admin</label>
              <textarea name="admin_notes"><?= htmlspecialchars((string)($old['admin_notes'] ?? $item->admin_notes ?? '')) ?></textarea>
            </div>

            <div class="field" style="grid-column:1/-1;">
              <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="is_featured" value="1" <?= !empty($old['is_featured']) || !empty($item->is_featured) ? 'checked' : '' ?>>
                Destacar pÃºblicamente
              </label>
            </div>

            <div class="exp-detail-actions" style="grid-column:1/-1;">
              <a href="/admin/experiences" class="btn" style="text-decoration:none;">Volver</a>
              <button class="btn primary" type="submit">Guardar cambios</button>
            </div>
          </form>

          <div class="exp-status-actions" style="margin-top:24px;">
            <?php if (($item->status ?? '') !== 'approved'): ?>
              <form method="POST" action="/admin/experiences/approve" style="flex:1 1 240px;">
                <?= Csrf::input(); ?>
                <input type="hidden" name="id" value="<?= (int)$item->id ?>">
                <button class="btn primary" style="width:100%;background:#16a34a;">Aprobar</button>
              </form>
            <?php else: ?>
              <div style="flex:1 1 240px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#dcfce7;color:#166534;font-weight:700;padding:12px;">
                Esta experiencia ya fue aprobada
              </div>
            <?php endif; ?>

            <?php if (($item->status ?? '') !== 'rejected'): ?>
              <form method="POST" action="/admin/experiences/reject" style="flex:1 1 240px;">
                <?= Csrf::input(); ?>
                <input type="hidden" name="id" value="<?= (int)$item->id ?>">
                <input type="hidden" name="admin_notes" value="<?= htmlspecialchars((string)($item->admin_notes ?? '')) ?>">
                <button class="btn" style="width:100%;background:#b91c1c;color:#fff;">Rechazar</button>
              </form>
            <?php else: ?>
              <div style="flex:1 1 240px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#fee2e2;color:#991b1b;font-weight:700;padding:12px;">
                Esta experiencia ya fue rechazada
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!empty($images)): ?>
          <div class="card">
            <h3 style="margin-bottom:12px;">ImÃ¡genes enviadas</h3>
            <div class="exp-image-grid">
              <?php foreach ($images as $img): ?>
                <div class="exp-image-card">
                  <img
                    src="/<?= ltrim(str_replace('\\', '/', (string)$img->image_path), '/') ?>"
                    alt=""
                  >
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>
</body>


</html>
