<?php
$service = $service ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
$message = $message ?? null;
$messageType = $messageType ?? 'success';
$openModal = $openModal ?? false;
$whatsappUrl = $whatsappUrl ?? null;

if (!$service) {
    echo 'Servicio no encontrado';
    return;
}

function service_error(array $errors, string $field): ?string
{
    return $errors[$field][0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($service->displayTitle()) ?></title>
  <style>
    :root{
      --red:#b61f2a;
      --soft:#f7f7f7;
      --line:#e5e5e5;
      --text:#222;
      --green-bg:#e8f7ea;
      --green-text:#166534;
      --green-line:#bbf7d0;
      --error-bg:#fee2e2;
      --error-text:#991b1b;
      --error-line:#fecaca;
      --wa:#25D366;
    }

    * { box-sizing: border-box; }

    body{
      margin:0;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:#fff;
      color:var(--text);
    }

    .wrap{
      max-width: 1200px;
      margin: 0 auto;
      padding: 32px 20px 60px;
    }

    .back{
      display:inline-block;
      margin-bottom:20px;
      text-decoration:none;
      color:var(--red);
      font-weight:700;
    }

    .hero{
      display:grid;
      grid-template-columns: 1.2fr 1fr;
      gap: 24px;
      align-items: stretch;
    }

    .hero-image{
      min-height: 420px;
      border-radius: 16px;
      overflow: hidden;
      background:#f2f2f2;
      border:1px solid var(--line);
    }

    .hero-image img{
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
    }

    .hero-content{
      border:1px solid var(--line);
      border-radius:16px;
      padding:24px;
      background:#fff;
    }

    .hero-content h1{
      margin:0 0 16px;
      color:var(--red);
      font-size:52px;
      line-height:1.04;
      font-weight:900;
    }

    .hero-content p{
      font-size:18px;
      line-height:1.7;
      margin:0 0 20px;
    }

    .actions{
      display:flex;
      gap:12px;
      flex-wrap:wrap;
    }

    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border:none;
      border-radius:12px;
      text-decoration:none;
      padding:12px 20px;
      font-weight:800;
      cursor:pointer;
      font-size:16px;
    }

    .btn-primary{
      background:var(--red);
      color:#fff;
    }

    .btn-secondary{
      background:#ececec;
      color:#333;
    }

    .btn-whatsapp{
      background:var(--wa);
      color:#fff;
    }

    .alert{
      margin: 0 0 20px;
      padding: 14px;
      border-radius: 10px;
      font-weight: 700;
    }

    .alert-success{
      background: var(--green-bg);
      color: var(--green-text);
      border: 1px solid var(--green-line);
    }

    .alert-error{
      background: var(--error-bg);
      color: var(--error-text);
      border: 1px solid var(--error-line);
    }

    .modal{
      display:none;
      position:fixed;
      inset:0;
      background:rgba(0,0,0,.45);
      z-index:999;
      align-items:center;
      justify-content:center;
      padding:20px;
    }

    .modal.show{
      display:flex;
    }

    .modal-box{
      width:100%;
      max-width:600px;
      background:#fff;
      border-radius:16px;
      padding:24px;
      position:relative;
      max-height:90vh;
      overflow:auto;
    }

    .modal-box h2{
      margin:0 0 14px;
      color:var(--red);
      font-size:32px;
    }

    .close{
      position:absolute;
      top:10px;
      right:12px;
      border:none;
      background:none;
      font-size:28px;
      cursor:pointer;
    }

    .field{
      margin-bottom:14px;
    }

    .field label{
      display:block;
      margin-bottom:6px;
      font-weight:700;
    }

    .field input,
    .field textarea{
      width:100%;
      padding:12px;
      border:1px solid #ccc;
      border-radius:10px;
      font-size:16px;
    }

    .field textarea{
      min-height:120px;
      resize:vertical;
    }

    .error{
      color:#b91c1c;
      font-size:14px;
      margin-top:6px;
      display:block;
    }

    .check{
      display:flex;
      align-items:flex-start;
      gap:10px;
      margin:16px 0;
    }

    .check input{
      margin-top:4px;
    }

    .modal-actions{
      display:flex;
      gap:12px;
      flex-wrap:wrap;
      margin-top:10px;
    }

    @media (max-width: 900px){
      .hero{
        grid-template-columns:1fr;
      }

      .hero-content h1{
        font-size:40px;
      }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <a class="back" href="/extra-services">← Volver a servicios extras</a>

    <div class="hero">
      <div class="hero-image">
        <img src="<?= e($service->imagen) ?>" alt="<?= e($service->displayTitle()) ?>">
      </div>

      <div class="hero-content">
        <?php if ($message): ?>
          <div class="alert <?= $messageType === 'error' ? 'alert-error' : 'alert-success' ?>">
            <?= e($message) ?>
          </div>
        <?php endif; ?>

        <h1><?= e($service->displayTitle()) ?></h1>
        <p><?= nl2br(e($service->descripcion_larga ?: $service->descripcion_corta)) ?></p>

        <div class="actions">
          <button type="button" class="btn btn-primary" id="openModalBtn">Solicitar información</button>

          <?php if (!empty($whatsappUrl)): ?>
            <a href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp">
              Enviar mensaje por WhatsApp
            </a>
          <?php endif; ?>

          <a href="/contact" class="btn btn-secondary">Ir a contacto general</a>
        </div>
      </div>
    </div>
  </div>

  <div class="modal <?= $openModal ? 'show' : '' ?>" id="contactModal">
    <div class="modal-box">
      <button class="close" type="button" id="closeModalBtn">&times;</button>
      <h2>Solicitar información</h2>

      <?php if (service_error($errors, 'general')): ?>
        <div class="alert alert-error">
          <?= e(service_error($errors, 'general')) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/extra-services/contact">
        <?= \app\Core\Csrf::input(); ?>

        <input type="hidden" name="extra_service_id" value="<?= (int) $service->id ?>">
        <input type="hidden" name="subject" value="<?= e('Solicitud de información - ' . $service->displayTitle()) ?>">

        <div class="field">
          <label for="nombre">Nombre</label>
          <input type="text" id="nombre" name="nombre" value="<?= e($old['nombre'] ?? '') ?>">
          <?php if (service_error($errors, 'nombre')): ?>
            <small class="error"><?= e(service_error($errors, 'nombre')) ?></small>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="email">Correo</label>
          <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>">
          <?php if (service_error($errors, 'email')): ?>
            <small class="error"><?= e(service_error($errors, 'email')) ?></small>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="telefono">Teléfono</label>
          <input type="text" id="telefono" name="telefono" value="<?= e($old['telefono'] ?? '') ?>">
          <?php if (service_error($errors, 'telefono')): ?>
            <small class="error"><?= e(service_error($errors, 'telefono')) ?></small>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="mensaje">Mensaje</label>
          <textarea id="mensaje" name="mensaje"><?= e($old['mensaje'] ?? ('Hola, quiero recibir información sobre ' . $service->displayTitle() . '.')) ?></textarea>
          <?php if (service_error($errors, 'mensaje')): ?>
            <small class="error"><?= e(service_error($errors, 'mensaje')) ?></small>
          <?php endif; ?>
        </div>

        <label class="check">
          <input type="checkbox" name="acepta" value="1" <?= !empty($old['acepta']) ? 'checked' : '' ?>>
          <span>Acepto el tratamiento de datos personales.</span>
        </label>
        <?php if (service_error($errors, 'acepta')): ?>
          <small class="error"><?= e(service_error($errors, 'acepta')) ?></small>
        <?php endif; ?>

        <div style="margin:12px 0;">
          <?= turnstile_widget_html(); ?>
        </div>

        <div class="modal-actions">
          <button type="submit" class="btn btn-primary">Enviar solicitud</button>

          <?php if (!empty($whatsappUrl)): ?>
            <a href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp">
              Enviar por WhatsApp
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById('contactModal');
    const openBtn = document.getElementById('openModalBtn');
    const closeBtn = document.getElementById('closeModalBtn');

    if (openBtn) {
      openBtn.addEventListener('click', () => modal.classList.add('show'));
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', () => modal.classList.remove('show'));
    }

    if (modal) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.classList.remove('show');
        }
      });
    }
  </script>
</body>
</html>
