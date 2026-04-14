<?php
use app\Core\Flash;

$page_title = "Usuarios";
$page_subtitle = "Administración y roles";
$active = "users";

$users = $users ?? [];
$filters = $filters ?? [];
$editingUser = $editingUser ?? null;
$currentAdminId = (int) ($currentAdminId ?? 0);
$currentAdmin = $currentAdmin ?? null;

$message = '';
$messageType = '';
$flashToasts = Flash::getAll();
if (!empty($flashToasts)) {
    $firstToast = $flashToasts[0];
    $message = (string) ($firstToast['message'] ?? '');
    $messageType = ((string) ($firstToast['type'] ?? 'info')) === 'error' ? 'error' : 'success';
}

$old = Flash::get('old', []);
$errors = Flash::get('errors', []);
$createdUserInfo = Flash::get('createdUserInfo', null);

$isEditing = $editingUser !== null;
$canCreateUsers = $currentAdmin && method_exists($currentAdmin, 'isSuperAdmin') && $currentAdmin->isSuperAdmin();
$openModal = $isEditing || isset($_GET['open_modal']) || !empty($errors);

function field_error(array $errors, string $field): string
{
    return $errors[$field][0] ?? '';
}
?>

<?php if ($message !== ''): ?>
  <div class="card" style="margin-bottom:16px;padding:14px;background:<?= $messageType === 'error' ? '#fee2e2' : '#dcfce7' ?>;">
    <div><?= e($message) ?></div>

    <?php if ($messageType !== 'error' && !empty($createdUserInfo)): ?>
      <div style="margin-top:10px;font-size:14px;">
        <?php if (!empty($createdUserInfo['email'])): ?>
          <div><strong>Correo:</strong> <?= e((string) $createdUserInfo['email']) ?></div>
        <?php endif; ?>

        <?php if (!empty($createdUserInfo['generated_password'])): ?>
          <div><strong>Contraseña temporal:</strong> <?= e((string) $createdUserInfo['generated_password']) ?></div>
        <?php endif; ?>

        <?php if (!empty($createdUserInfo['verification_url'])): ?>
          <div style="margin-top:6px;">
            <strong>Enlace de verificación:</strong>
            <a href="<?= e((string) $createdUserInfo['verification_url']) ?>" target="_blank" rel="noopener">
              abrir enlace
            </a>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-head">
    <div>
      <h2>Listado de usuarios</h2>
      <div class="card-sub">Editar, eliminar y crear</div>
    </div>

    <?php if ($canCreateUsers): ?>
      <button class="btn primary" data-open="#modalUser">
        <?= $isEditing ? 'Editar usuario' : '+ Invitar usuario' ?>
      </button>
    <?php endif; ?>
  </div>

  <div class="sep"></div>

  <form method="get" action="/admin/users" style="display:grid;grid-template-columns:1fr 1fr 1.4fr auto;gap:12px;align-items:end;margin-bottom:18px;">
    <div>
      <label style="display:block;margin-bottom:6px;font-weight:600;">Estado</label>
      <select name="status" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
        <option value="">Todos</option>
        <?php foreach ([
          'active' => 'Activo',
          'inactive' => 'Inactivo',
          'blocked' => 'Bloqueado',
          'pending_verification' => 'Pendiente verificación'
        ] as $value => $label): ?>
          <option value="<?= e($value) ?>" <?= (($filters['status'] ?? '') === $value) ? 'selected' : '' ?>>
            <?= e($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="display:block;margin-bottom:6px;font-weight:600;">Rol</label>
      <select name="role" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;">
        <option value="">Todos</option>
        <option value="super_admin" <?= (($filters['role'] ?? '') === 'super_admin') ? 'selected' : '' ?>>Super administrador</option>
        <option value="seller" <?= (($filters['role'] ?? '') === 'seller') ? 'selected' : '' ?>>Vendedor</option>
      </select>
    </div>

    <div>
      <label style="display:block;margin-bottom:6px;font-weight:600;">Buscar</label>
      <input
        type="text"
        name="q"
        value="<?= e((string) ($filters['q'] ?? '')) ?>"
        placeholder="Nombre, correo, teléfono..."
        style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:10px;"
      >
    </div>

    <div>
      <button class="btn primary" type="submit">Filtrar</button>
    </div>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Correo</th>
          <th>Número</th>
          <th>Rol</th>
          <th>Status</th>
          <th>Último acceso</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <?php
            $status = (string) ($user->status ?? '');
            $badgeClass = $status === 'active'
              ? 'ok'
              : (($status === 'blocked' || $status === 'pending_verification') ? 'warn' : '');

            $statusLabel = match ($status) {
              'active' => 'Activo',
              'inactive' => 'Inactivo',
              'blocked' => 'Bloqueado',
              'pending_verification' => 'Pendiente verificación',
              default => $status,
            };

            $isCurrentUser = (int) ($user->id ?? 0) === $currentAdminId;
          ?>
          <tr>
            <td><?= e((string) ($user->first_name ?? '')) ?></td>
            <td><?= e((string) ($user->last_name ?? '')) ?></td>
            <td><?= e((string) ($user->email ?? '')) ?></td>
            <td><?= e((string) ($user->phone ?? '')) ?></td>
            <td><?= e(method_exists($user, 'roleLabel') ? $user->roleLabel() : (string) ($user->role ?? '')) ?></td>
            <td><span class="badge <?= e($badgeClass) ?>"><?= e($statusLabel) ?></span></td>
            <td><?= !empty($user->last_login_at) ? e((string) $user->last_login_at) : 'Nunca' ?></td>
            <td>
              <div class="row-actions" style="display:flex;gap:8px;align-items:center;">
                <a class="chip" title="Editar" href="/admin/users/edit?id=<?= (int) $user->id ?>">✏️</a>

                <?php if (!$isCurrentUser): ?>
                  <form method="post" action="/admin/users/delete" style="display:inline;">
                    <?= \app\Core\Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?= (int) $user->id ?>">
                    <button class="chip" type="submit" title="Eliminar" onclick="return confirm('¿Eliminar este usuario?')">🗑️</button>
                  </form>

                  <?php if ($status === 'active'): ?>
                    <form method="post" action="/admin/users/status" style="display:inline;">
                      <?= \app\Core\Csrf::input(); ?>
                      <input type="hidden" name="id" value="<?= (int) $user->id ?>">
                      <input type="hidden" name="status" value="inactive">
                      <button class="chip" type="submit" title="Desactivar">⏸️</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="/admin/users/status" style="display:inline;">
                      <?= \app\Core\Csrf::input(); ?>
                      <input type="hidden" name="id" value="<?= (int) $user->id ?>">
                      <input type="hidden" name="status" value="active">
                      <button class="chip" type="submit" title="Activar">▶️</button>
                    </form>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="chip" title="Tu cuenta">👤</span>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if ($users === []): ?>
          <tr>
            <td colspan="8" style="padding:18px;text-align:center;color:#64748b;">
              No hay usuarios registrados con esos filtros.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($canCreateUsers || $isEditing): ?>
<div class="backdrop" id="modalUser" style="<?= $openModal ? 'display:flex;' : '' ?>">
  <div class="modal">
    <div class="modal-head">
      <h3><?= $isEditing ? 'Editar usuario' : 'Invitar usuario' ?></h3>
      <a class="icon-btn" href="/admin/users">✕</a>
    </div>

    <form method="post" action="<?= $isEditing ? '/admin/users/update' : '/admin/users/create' ?>">
      <?= \app\Core\Csrf::input(); ?>

      <?php if ($isEditing): ?>
        <input type="hidden" name="id" value="<?= (int) ($editingUser->id ?? 0) ?>">
      <?php endif; ?>

      <div class="modal-body">
        <?php if (!empty($errors['general'][0] ?? null)): ?>
          <div style="margin-bottom:12px;padding:12px;border-radius:12px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;">
            <?= e((string) $errors['general'][0]) ?>
          </div>
        <?php endif; ?>

        <div class="form">
          <div class="field">
            <label>Nombre</label>
            <input name="first_name" placeholder="Nombre" value="<?= e((string) ($old['first_name'] ?? $editingUser->first_name ?? '')) ?>">
            <?php if (field_error($errors, 'first_name')): ?>
              <small style="color:#b91c1c;"><?= e(field_error($errors, 'first_name')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label>Apellido</label>
            <input name="last_name" placeholder="Apellido" value="<?= e((string) ($old['last_name'] ?? $editingUser->last_name ?? '')) ?>">
            <?php if (field_error($errors, 'last_name')): ?>
              <small style="color:#b91c1c;"><?= e(field_error($errors, 'last_name')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label>Correo</label>
            <input name="email" placeholder="correo@dominio.com" value="<?= e((string) ($old['email'] ?? $editingUser->email ?? '')) ?>">
            <?php if (field_error($errors, 'email')): ?>
              <small style="color:#b91c1c;"><?= e(field_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label>Teléfono</label>
            <input name="phone" placeholder="+57..." value="<?= e((string) ($old['phone'] ?? $editingUser->phone ?? '')) ?>">
            <?php if (field_error($errors, 'phone')): ?>
              <small style="color:#b91c1c;"><?= e(field_error($errors, 'phone')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label>Rol</label>
            <?php $currentRole = (string) ($old['role'] ?? $editingUser->role ?? 'seller'); ?>
            <select name="role">
              <option value="super_admin" <?= $currentRole === 'super_admin' ? 'selected' : '' ?>>Super administrador</option>
              <option value="seller" <?= $currentRole === 'seller' ? 'selected' : '' ?>>Vendedor</option>
            </select>
            <?php if (field_error($errors, 'role')): ?>
              <small style="color:#b91c1c;"><?= e(field_error($errors, 'role')) ?></small>
            <?php endif; ?>
          </div>

          <?php if ($isEditing): ?>
            <div class="field">
              <label>Estado</label>
              <?php $currentStatus = (string) ($old['status'] ?? $editingUser->status ?? 'active'); ?>
              <select name="status">
                <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Activo</option>
                <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactivo</option>
                <option value="blocked" <?= $currentStatus === 'blocked' ? 'selected' : '' ?>>Bloqueado</option>
                <option value="pending_verification" <?= $currentStatus === 'pending_verification' ? 'selected' : '' ?>>Pendiente verificación</option>
              </select>
            </div>
          <?php else: ?>
            <div class="field">
              <label>Estado inicial</label>
              <input value="Pendiente verificación" disabled>
            </div>
          <?php endif; ?>

          <div class="field">
            <label><?= $isEditing ? 'Nueva contraseña (opcional)' : 'Contraseña temporal (opcional)' ?></label>
            <input type="password" name="password" placeholder="<?= $isEditing ? 'Solo si deseas cambiarla' : 'Si la dejas vacía, se genera automáticamente' ?>">
            <?php if (field_error($errors, 'password')): ?>
              <small style="color:#b91c1c;"><?= e(field_error($errors, 'password')) ?></small>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!$isEditing): ?>
          <div style="margin-top:14px;padding:12px;border-radius:12px;background:#f8fafc;border:1px solid #e5e7eb;color:#475569;font-size:14px;">
            Al invitar el usuario, el sistema enviará un correo de verificación. Solo podrá iniciar sesión después de verificar su cuenta.
          </div>
        <?php endif; ?>
      </div>

      <div class="modal-foot">
        <a class="btn" href="/admin/users">Cancelar</a>
        <button class="btn primary" type="submit"><?= $isEditing ? 'Guardar cambios' : 'Invitar usuario' ?></button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
