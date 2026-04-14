<div class="container-contact">
  <h1>Contacto</h1>

  <?php if (!empty($message ?? null)): ?>
    <div class="alert" style="margin-bottom:16px;padding:12px;border-radius:10px;background:<?= ($messageType ?? "success") === "error" ? "#fee2e2" : "#dcfce7" ?>;color:#111;">
      <?= e($message) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="/contact">
    <?= \app\Core\Csrf::input(); ?>

    <div class="mb-3">
      <label for="firstLastName" class="form-label">Nombres y Apellidos</label>
      <input type="text" class="form-control" id="firstLastName" name="firstLastName" value="<?= e((string) (($old['firstLastName'] ?? ''))) ?>">
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email address</label>
      <input type="email" class="form-control" id="email" name="email" value="<?= e((string) (($old['email'] ?? ''))) ?>">
    </div>

    <div class="mb-3">
      <label for="telefono" class="form-label">Teléfono</label>
      <input type="text" class="form-control" id="telefono" name="telefono" value="<?= e((string) (($old['telefono'] ?? ''))) ?>">
    </div>

    <div class="mb-3">
      <label for="subject" class="form-label">Asunto</label>
      <textarea class="form-control" id="subject" name="subject"><?= e((string) (($old['subject'] ?? ''))) ?></textarea>
    </div>
<div class="mb-3 checkbox-container">
  <input 
    type="checkbox" 
    id="acepta" 
    name="acepta" 
    value="1"
    <?= !empty($old['acepta'] ?? null) ? "checked" : "" ?>
  >

  <label for="acepta">
    Acepto la 
    <a href="/politica-datos" target="_blank">
      política de tratamiento de datos personales
    </a>
  </label>
</div>

    <button type="submit" class="btn btn-primary">Enviar</button>
  </form>
</div>