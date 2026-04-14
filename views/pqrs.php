<section class="pqrs">
  <div class="container-pqrs">
    <h1>PQRS</h1>
    <p>Queremos escucharte. Selecciona el tipo de solicitud y cuéntanos tu caso.</p>

    <?php if (!empty($message ?? null)): ?>
      <div class="alert" style="margin-bottom:16px;padding:12px;border-radius:10px;background:<?= ($messageType ?? "success") === "error" ? "#fee2e2" : "#dcfce7" ?>;color:#111;">
        <?= e($message) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="/pqrs" enctype="multipart/form-data">
      <?= \app\Core\Csrf::input(); ?>

      <div>
        <label>Nombre completo</label>
        <input type="text" name="name" value="<?= e((string) (($old['name'] ?? ''))) ?>" required>
      </div>

      <div>
        <label>Email</label>
        <input type="email" name="email" value="<?= e((string) (($old['email'] ?? ''))) ?>" required>
      </div>

      <div>
        <label>Tipo de solicitud</label>
        <select name="type" required>
          <option value="">Selecciona</option>
          <option value="peticion" <?= (($old['type'] ?? '') === 'peticion') ? "selected" : "" ?>>Petición</option>
          <option value="queja" <?= (($old['type'] ?? '') === 'queja') ? "selected" : "" ?>>Queja</option>
          <option value="reclamo" <?= (($old['type'] ?? '') === 'reclamo') ? "selected" : "" ?>>Reclamo</option>
          <option value="sugerencia" <?= (($old['type'] ?? '') === 'sugerencia') ? "selected" : "" ?>>Sugerencia</option>
        </select>
      </div>

      <div>
        <label>Asunto</label>
        <input type="text" name="subject" value="<?= e((string) (($old['subject'] ?? ''))) ?>" required>
      </div>

      <div>
        <label>Descripción</label>
        <textarea name="message" rows="5" required><?= e((string) (($old['message'] ?? ''))) ?></textarea>
      </div>

      <div>
        <label>Adjuntar archivos (opcional)</label>
        <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        <small>Puedes adjuntar PDF o imágenes. (Máx recomendado: 5MB por archivo)</small>
      </div>

      <div>
        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= e((string) (($old['telefono'] ?? ''))) ?>" required>
      </div>

      <div>
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

        <button type="submit">Enviar solicitud</button>
      </div>
    </form>
  </div>
</section>