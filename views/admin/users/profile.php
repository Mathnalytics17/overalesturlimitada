<?php $user = $user ?? null; ?>

<div class="grid two-col">
  <div class="card">
    <div class="card-head">
      <div>
        <h2>Información personal</h2>
        <p class="card-sub">Datos del administrador autenticado.</p>
      </div>
      <a href="/admin/profile/edit" class="btn small">Editar</a>
    </div>

    <div class="sep"></div>

    <div class="form">
      <div class="field">
        <label>Nombres</label>
        <input type="text" value="<?= htmlspecialchars($user->first_name ?? '') ?>" readonly>
      </div>

      <div class="field">
        <label>Apellidos</label>
        <input type="text" value="<?= htmlspecialchars($user->last_name ?? '') ?>" readonly>
      </div>

      <div class="field">
        <label>Correo</label>
        <input type="text" value="<?= htmlspecialchars($user->email ?? '') ?>" readonly>
      </div>

      <div class="field">
        <label>Rol</label>
        <input type="text" value="<?= htmlspecialchars($user->role ?? '') ?>" readonly>
      </div>

      <div class="field">
        <label>Estado</label>
        <input type="text" value="<?= htmlspecialchars($user->status ?? '') ?>" readonly>
      </div>

      <div class="field">
        <label>Último acceso</label>
        <input type="text" value="<?= htmlspecialchars($user->last_login_at ?? 'Sin registro') ?>" readonly>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head">
      <div>
        <h2>Estado de cuenta</h2>
        <p class="card-sub">Resumen rápido del perfil.</p>
      </div>
    </div>

    <div class="sep"></div>

    <p>
      <strong>Estado:</strong>
      <span class="badge ok"><?= htmlspecialchars($user->status ?? 'Sin estado') ?></span>
    </p>

    <p>
      <strong>Verificación:</strong>
      <?php if (($user->email_verified_at ?? null) !== null && ($user->email_verified_at ?? '') !== ''): ?>
        <span class="badge info">Correo confirmado</span>
      <?php else: ?>
        <span class="badge warn">Pendiente</span>
      <?php endif; ?>
    </p>

    <p>
      <strong>Cambiar contraseña:</strong><br>
      <a href="/admin/profile/change-password" class="btn small" style="margin-top:10px;">Ir a cambiar contraseña</a>
    </p>
  </div>
</div>