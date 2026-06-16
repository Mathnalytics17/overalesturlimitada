<?php

use app\Core\Csrf;

$account = $account ?? null;
$customer = $customer ?? null;
$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function ep_value($old, $customer, $field)
{
  return htmlspecialchars($old[$field] ?? $customer->$field ?? '');
}

function ep_error(array $errors, string $field): ?string
{
  return $errors[$field][0] ?? null;
}

$profilePhotoUrl = profile_photo_url($customer->profile_photo_path ?? null);
$fullName = trim((string) (($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')));
$fullName = $fullName !== '' ? $fullName : 'Usuario Alestur';
$email = (string) ($account->email ?? '');
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar usuario</title>
  <link rel="stylesheet" href="/styles/mainLayout.css">

  <style>
    :root {
      --alestur-primary: #e4053b;
      --alestur-primary-dark: #c70434;
      --alestur-soft: #fff1f5;
      --alestur-text: #101828;
      --alestur-muted: #64748b;
      --alestur-border: #dbe3ef;
      --alestur-bg: #f6f8fb;
      --alestur-card: #ffffff;
      --alestur-shadow: 0 24px 70px rgba(15, 23, 42, .12);
    }

    body {
      background:
        radial-gradient(circle at top right, rgba(228, 5, 59, .08), transparent 34%),
        linear-gradient(180deg, #ffffff 0%, var(--alestur-bg) 100%);
      color: var(--alestur-text);
    }

    .edit-profile-page {
      min-height: 100vh;
      padding: 34px 18px 56px;
    }

    .edit-profile-shell {
      width: min(1180px, 100%);
      margin: 0 auto;
    }

    .edit-profile-hero {
      display: grid;
      grid-template-columns: .9fr 1.4fr;
      gap: 22px;
      align-items: stretch;
    }

    .edit-profile-panel {
      position: relative;
      overflow: hidden;
      border-radius: 34px;
      padding: 34px;
      background: linear-gradient(135deg, var(--alestur-primary), #c80036);
      color: #fff;
      min-height: 100%;
      box-shadow: var(--alestur-shadow);
    }

    .edit-profile-panel::before,
    .edit-profile-panel::after {
      content: "";
      position: absolute;
      border-radius: 999px;
      border: 44px solid rgba(255, 255, 255, .14);
      pointer-events: none;
    }

    .edit-profile-panel::before {
      width: 270px;
      height: 270px;
      right: -100px;
      top: -80px;
    }

    .edit-profile-panel::after {
      width: 390px;
      height: 390px;
      left: -170px;
      bottom: -210px;
      background: rgba(255, 255, 255, .05);
    }

    .edit-profile-panel-inner {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      min-height: 100%;
    }

    .edit-profile-badge {
      display: inline-flex;
      align-items: center;
      width: fit-content;
      padding: 10px 18px;
      border-radius: 999px;
      background: rgba(255, 255, 255, .16);
      font-weight: 900;
      letter-spacing: -.02em;
      margin-bottom: 42px;
    }

    .edit-profile-panel h1 {
      margin: 0;
      font-size: clamp(42px, 5.2vw, 76px);
      line-height: .94;
      letter-spacing: -.06em;
      max-width: 620px;
    }

    .edit-profile-panel p {
      margin: 24px 0 0;
      font-size: 20px;
      line-height: 1.65;
      max-width: 570px;
      color: rgba(255, 255, 255, .92);
    }

    .edit-profile-benefits {
      margin-top: auto;
      padding-top: 44px;
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .edit-profile-benefit {
      display: flex;
      align-items: center;
      gap: 16px;
      font-weight: 900;
      font-size: 18px;
      line-height: 1.35;
    }

    .edit-profile-check {
      width: 46px;
      height: 46px;
      border-radius: 999px;
      display: grid;
      place-items: center;
      background: rgba(255, 255, 255, .20);
      flex: 0 0 auto;
      font-size: 28px;
      font-weight: 900;
    }

    .edit-profile-card {
      background: rgba(255, 255, 255, .94);
      border: 1px solid var(--alestur-border);
      border-radius: 34px;
      padding: 34px;
      box-shadow: var(--alestur-shadow);
      backdrop-filter: blur(10px);
    }

    .edit-profile-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      margin-bottom: 24px;
    }

    .edit-profile-title h2 {
      margin: 0;
      font-size: clamp(32px, 3vw, 48px);
      line-height: 1;
      letter-spacing: -.05em;
      color: var(--alestur-text);
    }

    .edit-profile-title p {
      margin: 10px 0 0;
      color: var(--alestur-muted);
      font-size: 17px;
      line-height: 1.5;
    }

    .edit-profile-avatar-box {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 12px;
      border-radius: 24px;
      background: #f8fafc;
      border: 1px solid var(--alestur-border);
    }

    .edit-profile-avatar {
      width: 72px;
      height: 72px;
      border-radius: 999px;
      object-fit: cover;
      border: 3px solid #fff;
      box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
      background: var(--alestur-primary);
      color: #fff;
      display: grid;
      place-items: center;
      font-weight: 1000;
      font-size: 24px;
    }

    .edit-profile-avatar-info strong {
      display: block;
      font-size: 15px;
      color: var(--alestur-text);
      max-width: 220px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .edit-profile-avatar-info span {
      display: block;
      margin-top: 4px;
      color: var(--alestur-muted);
      font-size: 13px;
      max-width: 220px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .edit-profile-alert {
      margin-bottom: 18px;
      padding: 14px 16px;
      border-radius: 16px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
      font-weight: 800;
    }

    .edit-profile-form {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 18px 20px;
    }

    .edit-profile-field {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .edit-profile-field.full {
      grid-column: 1 / -1;
    }

    .edit-profile-field label {
      font-weight: 900;
      color: #334155;
      font-size: 14px;
    }

    .edit-profile-field input {
      width: 100%;
      min-height: 54px;
      border: 1px solid #cbd7e6;
      border-radius: 18px;
      padding: 0 18px;
      background: #fff;
      color: var(--alestur-text);
      font-size: 16px;
      outline: none;
      transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .edit-profile-field input:focus {
      border-color: var(--alestur-primary);
      box-shadow: 0 0 0 4px rgba(228, 5, 59, .10);
    }

    .edit-profile-field input[readonly] {
      background: #f8fafc;
      color: #64748b;
      cursor: not-allowed;
    }

    .edit-profile-field input[type="file"] {
      padding: 14px;
      min-height: auto;
      cursor: pointer;
    }

    .edit-profile-help {
      color: var(--alestur-muted);
      font-size: 13px;
      line-height: 1.4;
    }

    .edit-profile-error {
      color: #b91c1c;
      font-size: 13px;
      font-weight: 800;
    }

    .edit-profile-photo-row {
      display: flex;
      align-items: center;
      gap: 18px;
      padding: 16px;
      border-radius: 22px;
      background: #f8fafc;
      border: 1px solid var(--alestur-border);
    }

    .edit-profile-photo-preview {
      width: 92px;
      height: 92px;
      border-radius: 999px;
      object-fit: cover;
      border: 4px solid #fff;
      box-shadow: 0 14px 34px rgba(15, 23, 42, .14);
      background: var(--alestur-primary);
      color: #fff;
      display: grid;
      place-items: center;
      font-weight: 1000;
      font-size: 30px;
      flex: 0 0 auto;
    }

    .edit-profile-actions {
      grid-column: 1 / -1;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      padding-top: 18px;
      margin-top: 6px;
      border-top: 1px solid #e2e8f0;
    }

    .edit-profile-btn {
      min-height: 54px;
      border-radius: 999px;
      padding: 0 26px;
      border: 1px solid #cbd7e6;
      background: #fff;
      color: var(--alestur-text);
      font-weight: 1000;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .edit-profile-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 30px rgba(15, 23, 42, .10);
    }

    .edit-profile-btn.primary {
      border-color: var(--alestur-primary);
      background: var(--alestur-primary);
      color: #fff;
      box-shadow: 0 18px 36px rgba(228, 5, 59, .24);
    }

    .edit-profile-btn.primary:hover {
      background: var(--alestur-primary-dark);
    }

    @media (max-width: 980px) {
      .edit-profile-hero {
        grid-template-columns: 1fr;
      }

      .edit-profile-panel {
        min-height: auto;
      }

      .edit-profile-benefits {
        margin-top: 34px;
      }
    }

    @media (max-width: 720px) {
      .edit-profile-page {
        padding: 18px 12px 34px;
      }

      .edit-profile-panel,
      .edit-profile-card {
        border-radius: 24px;
        padding: 24px;
      }

      .edit-profile-card-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .edit-profile-avatar-box {
        width: 100%;
      }

      .edit-profile-form {
        grid-template-columns: 1fr;
      }

      .edit-profile-photo-row {
        flex-direction: column;
        align-items: flex-start;
      }

      .edit-profile-actions {
        flex-direction: column-reverse;
      }

      .edit-profile-btn {
        width: 100%;
      }
    }
  </style>
</head>

<body>
  <div class="edit-profile-page">
    <div class="edit-profile-shell">
      <div class="edit-profile-hero">

        <aside class="edit-profile-panel">
          <div class="edit-profile-panel-inner">
            <div class="edit-profile-badge">Alestur Ltda.</div>

            <h1>Actualiza tu perfil de viaje.</h1>

            <p>
              Mantén tus datos al día para recibir una atención más rápida,
              conservar tus solicitudes y facilitar el contacto con nuestro equipo.
            </p>

            <div class="edit-profile-benefits">
              <div class="edit-profile-benefit">
                <span class="edit-profile-check">✓</span>
                <span>Información personal organizada.</span>
              </div>

              <div class="edit-profile-benefit">
                <span class="edit-profile-check">✓</span>
                <span>Contacto directo con asesores.</span>
              </div>

              <div class="edit-profile-benefit">
                <span class="edit-profile-check">✓</span>
                <span>Datos listos para tus próximas solicitudes.</span>
              </div>
            </div>
          </div>
        </aside>

        <main class="edit-profile-card">
          <div class="edit-profile-card-header">
            <div class="edit-profile-title">
              <h2>Editar usuario</h2>
              <p>Actualiza la información principal de tu cuenta.</p>
            </div>

            <div class="edit-profile-avatar-box">
              <?php if ($profilePhotoUrl): ?>
                <img
                  class="edit-profile-avatar"
                  src="<?= htmlspecialchars($profilePhotoUrl) ?>"
                  alt="Foto de perfil">
              <?php else: ?>
                <div class="edit-profile-avatar">
                  <?= htmlspecialchars(mb_strtoupper(mb_substr($fullName, 0, 1))) ?>
                </div>
              <?php endif; ?>

              <div class="edit-profile-avatar-info">
                <strong><?= htmlspecialchars($fullName) ?></strong>
                <span><?= htmlspecialchars($email) ?></span>
              </div>
            </div>
          </div>

          <?php if ($message): ?>
            <div class="edit-profile-alert">
              <?= htmlspecialchars($message) ?>
            </div>
          <?php endif; ?>

          <form class="edit-profile-form" method="POST" action="/users/editUser" enctype="multipart/form-data">
            <?= Csrf::input(); ?>

            <div class="edit-profile-field full">
              <label for="profile_photo">Foto de perfil</label>

              <div class="edit-profile-photo-row">
                <?php if ($profilePhotoUrl): ?>
                  <img
                    class="edit-profile-photo-preview"
                    src="<?= htmlspecialchars($profilePhotoUrl) ?>"
                    alt="Foto de perfil actual">
                <?php else: ?>
                  <div class="edit-profile-photo-preview">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($fullName, 0, 1))) ?>
                  </div>
                <?php endif; ?>

                <div style="width:100%;">
                  <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                  <small class="edit-profile-help">JPG, PNG o WEBP. Tamaño máximo: 5MB.</small>

                  <?php if (ep_error($errors, 'profile_photo')): ?>
                    <small class="edit-profile-error"><?= htmlspecialchars(ep_error($errors, 'profile_photo')) ?></small>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="edit-profile-field">
              <label for="first_name">Nombres</label>
              <input
                type="text"
                id="first_name"
                name="first_name"
                value="<?= ep_value($old, $customer, 'first_name') ?>"
                placeholder="Tus nombres">
              <?php if (ep_error($errors, 'first_name')): ?>
                <small class="edit-profile-error"><?= htmlspecialchars(ep_error($errors, 'first_name')) ?></small>
              <?php endif; ?>
            </div>

            <div class="edit-profile-field">
              <label for="last_name">Apellidos</label>
              <input
                type="text"
                id="last_name"
                name="last_name"
                value="<?= ep_value($old, $customer, 'last_name') ?>"
                placeholder="Tus apellidos">
              <?php if (ep_error($errors, 'last_name')): ?>
                <small class="edit-profile-error"><?= htmlspecialchars(ep_error($errors, 'last_name')) ?></small>
              <?php endif; ?>
            </div>

            <div class="edit-profile-field full">
              <label for="email">Correo electrónico</label>
              <input
                type="email"
                id="email"
                value="<?= htmlspecialchars($email) ?>"
                readonly>
            </div>

            <div class="edit-profile-field">
              <label for="phone">Teléfono</label>
              <input
                type="text"
                id="phone"
                name="phone"
                value="<?= ep_value($old, $customer, 'phone') ?>"
                placeholder="+57 3000000000">
            </div>

            <div class="edit-profile-field">
              <label for="whatsapp">WhatsApp</label>
              <input
                type="text"
                id="whatsapp"
                name="whatsapp"
                value="<?= ep_value($old, $customer, 'whatsapp') ?>"
                placeholder="+57 3000000000">
            </div>

            <div class="edit-profile-field">
              <label for="document_type">Tipo de documento</label>
              <input
                type="text"
                id="document_type"
                name="document_type"
                value="<?= ep_value($old, $customer, 'document_type') ?>"
                placeholder="CC, CE, Pasaporte">
            </div>

            <div class="edit-profile-field">
              <label for="document_number">Número de documento</label>
              <input
                type="text"
                id="document_number"
                name="document_number"
                value="<?= ep_value($old, $customer, 'document_number') ?>"
                placeholder="Número de identificación">
            </div>

            <div class="edit-profile-field">
              <label for="birth_date">Fecha de nacimiento</label>
              <input
                type="date"
                id="birth_date"
                name="birth_date"
                value="<?= ep_value($old, $customer, 'birth_date') ?>">
              <?php if (ep_error($errors, 'birth_date')): ?>
                <small class="edit-profile-error"><?= htmlspecialchars(ep_error($errors, 'birth_date')) ?></small>
              <?php endif; ?>
            </div>

            <div class="edit-profile-field">
              <label for="address">Dirección</label>
              <input
                type="text"
                id="address"
                name="address"
                value="<?= ep_value($old, $customer, 'address') ?>"
                placeholder="Dirección de contacto">
            </div>

            <div class="edit-profile-actions">
              <a href="/users/user" class="edit-profile-btn">Cancelar</a>
              <button class="edit-profile-btn primary" type="submit">Guardar cambios</button>
            </div>
          </form>
        </main>

      </div>
    </div>
  </div>
</body>

</html>