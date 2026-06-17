<?php

use app\Core\Csrf;

$errors = $errors ?? [];
$message = $message ?? null;
$notice = $notice ?? null;
$old = $old ?? [];

function login_error(array $errors, string $field): ?string
{
  return $errors[$field][0] ?? null;
}
?>

<style>
  :root {
    --alestur-red: #e9003f;
    --alestur-red-dark: #c90036;
    --text-dark: #111827;
    --text-muted: #60708a;
    --border: #d7e0ef;
    --bg-soft: #f7f9fc;
  }

  html,
  body {
    margin: 0;
    padding: 0;
  }

  body {
    background: #ffffff;
  }

  .auth-login-page {
    width: 100%;
    max-width: 100%;
    min-height: calc(100vh - 0px);
    display: grid;
    grid-template-columns: minmax(420px, 1fr) minmax(520px, 1fr);
    align-items: stretch;
    background: #f8fafc;
    margin: 0;
    padding: 0;
    overflow: hidden;
  }

  .site-footer {
    margin-top: 0 !important;
  }

  .auth-login-left {
    background:
      radial-gradient(circle at 0% 100%, rgba(255, 255, 255, .16) 0 0, rgba(255, 255, 255, .16) 210px, transparent 211px),
      radial-gradient(circle at 96% 20%, rgba(255, 255, 255, .16) 0 0, rgba(255, 255, 255, .16) 78px, transparent 79px),
      linear-gradient(135deg, var(--alestur-red), var(--alestur-red-dark));
    color: #ffffff;
    min-height: 100%;
    display: flex;
    align-items: center;
    padding: 72px 7vw;
    box-sizing: border-box;
    overflow: hidden;
  }

  .auth-login-left-inner {
    width: 100%;
    max-width: 780px;
  }

  .auth-pill {
    display: inline-flex;
    align-items: center;
    padding: 12px 22px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .18);
    font-weight: 900;
    margin-bottom: 46px;
  }

  .auth-login-title {
    font-size: clamp(54px, 5.6vw, 94px);
    line-height: .98;
    letter-spacing: -3px;
    margin: 0 0 32px 0;
    font-weight: 1000;
  }

  .auth-login-text {
    font-size: clamp(22px, 1.6vw, 31px);
    line-height: 1.55;
    max-width: 760px;
    margin: 0 0 70px 0;
    font-weight: 500;
  }

  .auth-benefits {
    display: flex;
    flex-direction: column;
    gap: 34px;
    margin: 0;
    padding: 0;
  }

  .auth-benefit {
    display: flex;
    align-items: center;
    gap: 24px;
    font-size: clamp(23px, 1.8vw, 34px);
    font-weight: 1000;
    line-height: 1.25;
  }

  .auth-check {
    width: 68px;
    height: 68px;
    min-width: 68px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .25);
    display: grid;
    place-items: center;
    font-size: 42px;
    line-height: 1;
  }

  .auth-login-right {
    min-height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 72px 5vw;
    box-sizing: border-box;
    background:
      radial-gradient(circle at 82% 12%, rgba(233, 0, 63, .10), transparent 34%),
      #f8fafc;
  }

  .auth-card {
    width: 100%;
    max-width: 760px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 34px;
    padding: 48px 56px;
    box-shadow: 0 38px 90px rgba(15, 23, 42, .13);
    box-sizing: border-box;
  }

  .auth-brand {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 30px;
  }

  .auth-logo {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: var(--alestur-red);
    color: white;
    display: grid;
    place-items: center;
    font-weight: 1000;
    font-size: 20px;
  }

  .auth-brand-title {
    font-weight: 1000;
    color: var(--text-dark);
    font-size: 18px;
  }

  .auth-brand-sub {
    color: var(--text-muted);
    margin-top: 3px;
    font-size: 15px;
  }

  .auth-card h1 {
    margin: 0 0 12px 0;
    font-size: clamp(40px, 3.4vw, 58px);
    line-height: 1;
    letter-spacing: -2px;
    color: var(--text-dark);
    font-weight: 1000;
  }

  .auth-card-sub {
    margin: 0 0 36px 0;
    color: var(--text-muted);
    font-size: 19px;
  }

  .auth-field {
    margin-bottom: 22px;
  }

  .auth-field label {
    display: block;
    color: #263445;
    font-weight: 1000;
    margin-bottom: 10px;
  }

  .auth-field input {
    width: 100%;
    height: 64px;
    border: 1px solid #c9d6e8;
    border-radius: 18px;
    padding: 0 22px;
    font-size: 18px;
    color: var(--text-dark);
    outline: none;
    box-sizing: border-box;
    background: #fff;
  }

  .auth-field input:focus {
    border-color: var(--alestur-red);
    box-shadow: 0 0 0 4px rgba(233, 0, 63, .10);
  }

  .auth-error {
    display: block;
    margin-top: 8px;
    color: #b91c1c;
    font-weight: 800;
    font-size: 14px;
  }

  .auth-message {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 14px;
    background: #fef2f2;
    color: #b91c1c;
    font-weight: 900;
    border: 1px solid #fecaca;
  }

  .auth-message.success {
    background: #ecfdf5;
    color: #166534;
    border-color: #bbf7d0;
  }

  .auth-message.warning {
    background: #fffbeb;
    color: #92400e;
    border-color: #fde68a;
  }

  .auth-message strong {
    display: block;
    margin-bottom: 4px;
    color: inherit;
  }

  .auth-message .auth-message-actions {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .auth-message .auth-message-actions a {
    color: inherit;
    font-weight: 1000;
    text-decoration: underline;
    text-underline-offset: 4px;
  }

  .auth-forgot {
    text-align: right;
    margin-top: -8px;
    margin-bottom: 26px;
  }

  .auth-forgot a,
  .auth-bottom a {
    color: #0f172a;
    font-weight: 1000;
    text-decoration: underline;
    text-underline-offset: 4px;
  }

  .auth-turnstile {
    margin-bottom: 18px;
  }

  .auth-submit {
    width: 100%;
    height: 66px;
    border: none;
    border-radius: 999px;
    background: var(--alestur-red);
    color: #ffffff;
    font-size: 19px;
    font-weight: 1000;
    cursor: pointer;
    box-shadow: 0 18px 34px rgba(233, 0, 63, .22);
  }

  .auth-submit:hover {
    background: var(--alestur-red-dark);
  }

  .auth-separator {
    height: 1px;
    background: #dbe3ef;
    margin: 32px 0 24px 0;
  }

  .auth-bottom {
    text-align: center;
    color: #64748b;
    font-weight: 900;
    font-size: 17px;
  }

  @media (max-width: 1100px) {
    .auth-login-page {
      grid-template-columns: minmax(0, 1fr);
    }

    .auth-login-left {
      min-height: auto;
      min-width: 0;
      padding: 56px 32px;
    }

    .auth-login-right {
      padding: 42px 24px 60px;
    }

    .auth-card {
      padding: 34px 26px;
      border-radius: 28px;
    }

    .auth-login-left-inner,
    .auth-benefit span:last-child {
      min-width: 0;
      overflow-wrap: anywhere;
    }
  }

  @media (max-width: 640px) {
    .auth-login-title {
      font-size: 46px;
      letter-spacing: -1px;
    }

    .auth-login-text {
      font-size: 19px;
      margin-bottom: 40px;
    }

    .auth-benefit {
      font-size: 20px;
      gap: 16px;
    }

    .auth-check {
      width: 50px;
      height: 50px;
      min-width: 50px;
      font-size: 32px;
    }

    .auth-card h1 {
      font-size: 38px;
    }
  }
</style>

<main class="auth-login-page">
  <section class="auth-login-left">
    <div class="auth-login-left-inner">
      <div class="auth-pill">Alestur Ltda.</div>

      <h1 class="auth-login-title">
        Inicia sesión y continúa tu viaje.
      </h1>

      <p class="auth-login-text">
        Accede a tus consultas, favoritos y preferencias para recibir
        una atención más rápida por nuestros canales.
      </p>

      <div class="auth-benefits">
        <div class="auth-benefit">
          <span class="auth-check">✓</span>
          <span>Acceso a tus consultas y favoritos.</span>
        </div>

        <div class="auth-benefit">
          <span class="auth-check">✓</span>
          <span>Preferencias de viaje personalizadas.</span>
        </div>

        <div class="auth-benefit">
          <span class="auth-check">✓</span>
          <span>Contacto directo con el equipo Alestur.</span>
        </div>
      </div>
    </div>
  </section>

  <section class="auth-login-right">
    <div class="auth-card">
      <div class="auth-brand">
        <div class="auth-logo">OA</div>
        <div>
          <div class="auth-brand-title">Over Alestur</div>
          <div class="auth-brand-sub">Expertos en viajes</div>
        </div>
      </div>

      <h1>Inicia sesión</h1>
      <p class="auth-card-sub">Accede a tu cuenta para gestionar tus solicitudes.</p>

      <?php if (is_array($notice)): ?>
        <?php $noticeType = in_array(($notice['type'] ?? ''), ['success', 'warning'], true) ? $notice['type'] : 'success'; ?>
        <div class="auth-message <?= htmlspecialchars($noticeType) ?>">
          <strong><?= htmlspecialchars($notice['title'] ?? 'Información importante') ?></strong>
          <span><?= htmlspecialchars($notice['message'] ?? '') ?></span>
          <div class="auth-message-actions">
            <a href="/users/resendVerification<?= !empty($notice['email']) ? '?email=' . urlencode((string) $notice['email']) : '' ?>">Reenviar correo de verificación</a>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($message): ?>
        <div class="auth-message">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/users/login">
        <?= Csrf::input(); ?>

        <div class="auth-field">
          <label for="email">Correo electrónico</label>
          <input
            id="email"
            name="email"
            type="email"
            placeholder="correo@ejemplo.com"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            autocomplete="email" />
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
            autocomplete="current-password" />
          <?php if (login_error($errors, 'password')): ?>
            <small class="auth-error"><?= htmlspecialchars(login_error($errors, 'password')) ?></small>
          <?php endif; ?>
        </div>

        <div class="auth-forgot">
          <a href="/users/forgotPassword">¿Olvidaste tu contraseña?</a>
        </div>

        <?php if (login_error($errors, 'auth')): ?>
          <div class="auth-message">
            <?= htmlspecialchars(login_error($errors, 'auth')) ?>
          </div>
        <?php endif; ?>

        <div class="auth-turnstile">
          <?= turnstile_widget_html(); ?>
        </div>

        <button type="submit" class="auth-submit">Entrar</button>

        <div class="auth-separator"></div>

        <div class="auth-bottom">
          ¿No tienes cuenta? <a href="/users/register">Crear cuenta</a>
        </div>
      </form>
    </div>
  </section>
</main>
