<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$old = $old ?? [];

function login_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Iniciar sesión</title>
  <link rel="stylesheet" href="/styles/home.css" />
  <style>
    .auth-shell {
      min-height: calc(100vh - 220px);
      padding: 56px 20px 72px;
      background:
        radial-gradient(circle at top right, rgba(225,29,72,.08), transparent 28%),
        linear-gradient(180deg, #fff 0%, #fff5f7 100%);
    }

    .auth-wrap {
      width: min(1120px, 100%);
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1.05fr .95fr;
      gap: 28px;
      align-items: stretch;
    }

    .auth-panel,
    .auth-card {
      background: rgba(255,255,255,.96);
      border: 1px solid rgba(225,229,235,.95);
      border-radius: 28px;
      box-shadow: var(--shadow);
    }

    .auth-panel {
      position: relative;
      overflow: hidden;
      padding: 42px;
      background:
        linear-gradient(135deg, rgba(225,29,72,.95) 0%, rgba(190,18,60,.95) 100%),
        #be123c;
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 640px;
    }

    .auth-panel::after {
      content: "";
      position: absolute;
      inset: auto -40px -40px auto;
      width: 220px;
      height: 220px;
      border-radius: 999px;
      background: rgba(255,255,255,.08);
    }

    .auth-brand {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 34px;
    }

    .auth-brand-logo {
      width: 58px;
      height: 58px;
      border-radius: 18px;
      display: grid;
      place-items: center;
      font-weight: 900;
      font-size: 22px;
      color: var(--primary-dark);
      background: #fff;
      box-shadow: 0 14px 30px rgba(0,0,0,.15);
    }

    .auth-brand small {
      display: block;
      color: rgba(255,255,255,.78);
      font-size: 14px;
      margin-top: 4px;
    }

    .auth-panel h1 {
      position: relative;
      z-index: 1;
      margin: 0 0 16px;
      font-size: clamp(34px, 5vw, 58px);
      line-height: 1.02;
      font-weight: 900;
    }

    .auth-panel p {
      position: relative;
      z-index: 1;
      margin: 0;
      font-size: 17px;
      line-height: 1.8;
      color: rgba(255,255,255,.9);
      max-width: 540px;
    }

    .auth-panel-list {
      position: relative;
      z-index: 1;
      display: grid;
      gap: 14px;
      margin-top: 34px;
    }

    .auth-panel-list span {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      width: fit-content;
      padding: 11px 16px;
      border-radius: 999px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.16);
      font-size: 14px;
      font-weight: 700;
    }

    .auth-card {
      padding: 34px;
      align-self: center;
    }

    .auth-card h2 {
      margin: 0 0 8px;
      font-size: 38px;
      line-height: 1.05;
      color: var(--text);
    }

    .auth-subtitle {
      margin: 0 0 26px;
      color: var(--muted);
      line-height: 1.7;
      font-size: 16px;
    }

    .auth-alert {
      margin-bottom: 16px;
      padding: 13px 15px;
      border-radius: 16px;
      font-size: 14px;
      line-height: 1.55;
      font-weight: 700;
      border: 1px solid #fecaca;
      background: #fff1f2;
      color: #be123c;
    }

    .auth-form {
      display: grid;
      gap: 16px;
    }

    .auth-field label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      font-weight: 800;
      color: var(--text);
    }

    .auth-field input {
      width: 100%;
      min-height: 54px;
      border: 1px solid #d1d5db;
      border-radius: 16px;
      padding: 0 16px;
      font-size: 16px;
      color: var(--text);
      background: #fff;
      outline: none;
      transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .auth-field input:focus {
      border-color: #f43f5e;
      box-shadow: 0 0 0 4px rgba(225,29,72,.12);
    }

    .auth-error {
      display: block;
      margin-top: 7px;
      color: #be123c;
      font-size: 13px;
      font-weight: 700;
    }

    .auth-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }

    .auth-link {
      text-decoration: none;
      color: var(--primary-dark);
      font-weight: 800;
    }

    .auth-link:hover {
      text-decoration: underline;
    }

    .auth-submit {
      width: 100%;
      border: 0;
      cursor: pointer;
    }

    .auth-footer {
      margin-top: 18px;
      text-align: center;
      font-size: 14px;
      color: var(--muted);
    }

    @media (max-width: 980px) {
      .auth-wrap {
        grid-template-columns: 1fr;
      }

      .auth-panel {
        min-height: auto;
        padding: 34px 28px;
      }
    }

    @media (max-width: 640px) {
      .auth-shell {
        padding: 28px 16px 44px;
      }

      .auth-card,
      .auth-panel {
        padding: 24px 20px;
        border-radius: 22px;
      }

      .auth-card h2 {
        font-size: 30px;
      }
    }
  </style>
</head>
<body>
  <section class="auth-shell">
    <div class="auth-wrap">
      <aside class="auth-panel">
        <div>
          <div class="auth-brand">
            <div class="auth-brand-logo">OA</div>
            <div>
              <div style="font-weight:900; font-size:22px;">Over Alestur</div>
              <small>Expertos en viajes</small>
            </div>
          </div>

          <h1>Bienvenido de nuevo</h1>
          <p>
            Accede a tu cuenta para gestionar reservas, consultar tus solicitudes y seguir viviendo experiencias con Over Alestur.
          </p>

          <div class="auth-panel-list">
            <span>Acceso rápido y seguro</span>
            <span>Gestión de cuenta en un solo lugar</span>
            <span>Soporte y acompañamiento cercano</span>
          </div>
        </div>

        <p style="font-size:14px; color:rgba(255,255,255,.75); margin-top:28px;">
          Tu información está protegida y tu acceso está respaldado por validación segura.
        </p>
      </aside>

      <div class="auth-card">
        <h2>Inicia sesión</h2>
        <p class="auth-subtitle">Ingresa con tu correo electrónico y contraseña.</p>

        <?php if ($message): ?>
          <div class="auth-alert">
            <?= htmlspecialchars($message) ?>
          </div>
        <?php endif; ?>

        <?php if (login_error($errors, 'auth')): ?>
          <div class="auth-alert">
            <?= htmlspecialchars(login_error($errors, 'auth')) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="/users/login" class="auth-form" novalidate>
          <?= Csrf::input(); ?>

          <div class="auth-field">
            <label for="email">Correo electrónico</label>
            <input
              id="email"
              name="email"
              type="email"
              placeholder="correo@ejemplo.com"
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"
              autocomplete="email"
            />
            <?php if (login_error($errors, 'email')): ?>
              <small class="auth-error"><?= htmlspecialchars(login_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-field">
            <label for="password">Contraseña</label>
            <input
              id="password"
              name="password"
              type="password"
              placeholder="Ingresa tu contraseña"
              autocomplete="current-password"
            />
            <?php if (login_error($errors, 'password')): ?>
              <small class="auth-error"><?= htmlspecialchars(login_error($errors, 'password')) ?></small>
            <?php endif; ?>
          </div>

          <div class="auth-row">
            <span style="font-size:14px; color:var(--muted);">Acceso para usuarios registrados</span>
            <a href="/users/forgotPassword" class="auth-link">¿Olvidaste tu contraseña?</a>
          </div>

          <div>
            <?= turnstile_widget_html(); ?>
          </div>

          <button type="submit" class="btn btn-primary auth-submit">Entrar</button>

          <div class="auth-footer">
            ¿No tienes cuenta?
            <a href="/users/register" class="auth-link">Crear cuenta</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</body>
</html>