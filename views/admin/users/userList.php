<?php
use app\Core\Flash;

$page_title = "Usuarios";
$page_subtitle = "Administracion y roles";
$active = "users";

$users = $users ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? ['page' => 1, 'per_page' => 20, 'total' => count($users), 'last_page' => 1, 'from' => count($users) > 0 ? 1 : 0, 'to' => count($users)];
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

$isEditing = $editingUser !== null;
$canCreateUsers = $currentAdmin && method_exists($currentAdmin, 'isSuperAdmin') && $currentAdmin->isSuperAdmin();
$openModal = $isEditing || isset($_GET['open_modal']) || !empty($errors);

function field_error(array $errors, string $field): string
{
    return $errors[$field][0] ?? '';
}



function admin_user_last_access($user): string
{
    $value = (string) ($user->last_login_at ?? '');
    if ($value === '' && !empty($user->id)) {
        try {
            $value = (string) (\app\Models\AdminLoginLog::lastSuccessfulLoginAt((int) $user->id) ?? '');
        } catch (\Throwable $e) {
            $value = '';
        }
    }

    return $value !== '' ? $value : 'Nunca';
}

function users_page_url(int $page, array $filters, array $pagination): string
{
    $query = array_filter([
        'status' => $filters['status'] ?? '',
        'role' => $filters['role'] ?? '',
        'q' => $filters['q'] ?? '',
        'per_page' => $pagination['per_page'] ?? 20,
        'page' => $page,
    ], static fn($value): bool => $value !== '');

    return '/admin/users?' . http_build_query($query);
}
?>

<style>
  .users-filters {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    align-items: end;
    margin-bottom: 18px;
  }
  .users-filters label { display:block; margin-bottom:6px; font-weight:700; }
  .users-control { width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:10px; background:#fff; }
  .users-filter-actions { display:flex; gap:8px; flex-wrap:wrap; }
  .users-summary { color:#64748b; font-size:14px; margin:0 0 14px; }
  .users-pagination { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top:18px; }
  .users-pages { display:flex; gap:6px; flex-wrap:wrap; }
  .users-pages a, .users-pages span { min-width:38px; min-height:38px; display:inline-flex; align-items:center; justify-content:center; padding:0 10px; border:1px solid #dbe3ef; border-radius:10px; text-decoration:none; color:#334155; background:#fff; font-weight:800; }
  .users-pages .active { background:#e11d48; border-color:#e11d48; color:#fff; }
  .users-table td::before { display:none; }
  @media (min-width: 720px) {
    .users-filters { grid-template-columns:1fr 1fr minmax(220px, 1.4fr) 130px auto; }
  }
  @media (max-width: 760px) {
    .users-table, .users-table tbody, .users-table tr, .users-table td { display:block; width:100%; }
    .users-table thead { display:none; }
    .users-table tr { padding:14px; border:1px solid #e2e8f0; border-radius:16px; margin-bottom:12px; background:#fff; }
    .users-table td { display:grid; grid-template-columns:minmax(94px, .75fr) minmax(0, 1fr); gap:10px; padding:7px 0; border:0; overflow-wrap:anywhere; }
    .users-table td::before { display:block; content:attr(data-label); color:#64748b; font-size:12px; font-weight:900; text-transform:uppercase; }
    .users-table td:last-child { display:block; padding-top:12px; }
    .users-table td:last-child::before { margin-bottom:8px; }
    .row-actions { flex-wrap:wrap; }
    .users-pagination { align-items:flex-start; flex-direction:column; }
  }
</style>

<?php if ($message !== ''): ?>
  <div class="card" style="margin-bottom:16px;padding:14px;background:<?= $messageType === 'error' ? '#fee2e2' : '#dcfce7' ?>;">
    <div><?= e($message) ?></div>
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

  <form method="get" action="/admin/users" class="users-filters">
    <div>
      <label style="display:block;margin-bottom:6px;font-weight:600;">Estado</label>
      <select name="status" class="users-control">
        <option value="">Todos</option>
        <?php foreach ([
          'active' => 'Activo',
          'inactive' => 'Inactivo',
          'blocked' => 'Bloqueado',
          'pending_verification' => 'Pendiente verificacion'
        ] as $value => $label): ?>
          <option value="<?= e($value) ?>" <?= (($filters['status'] ?? '') === $value) ? 'selected' : '' ?>>
            <?= e($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="display:block;margin-bottom:6px;font-weight:600;">Rol</label>
      <select name="role" class="users-control">
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
        placeholder="Nombre, correo, telefono..."
        class="users-control"
      >
    </div>

    <div>
      <label>Cantidad</label>
      <select name="per_page" class="users-control">
        <?php foreach ([10, 20, 50, 100] as $size): ?>
          <option value="<?= $size ?>" <?= (int) ($pagination['per_page'] ?? 20) === $size ? 'selected' : '' ?>><?= $size ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="users-filter-actions">
      <button class="btn primary" type="submit">Filtrar</button>
      <a class="btn" href="/admin/users">Limpiar</a>
    </div>
  </form>

  <p class="users-summary">
    Mostrando <?= (int) $pagination['from'] ?>-<?= (int) $pagination['to'] ?> de <?= (int) $pagination['total'] ?> usuarios.
  </p>

  <div class="table-wrap">
    <table class="users-table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Correo</th>
          <th>Numero</th>
          <th>Rol</th>
          <th>Status</th>
          <th>Ultimo acceso</th>
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
              'pending_verification' => 'Pendiente verificacion',
              default => $status,
            };

            $isCurrentUser = (int) ($user->id ?? 0) === $currentAdminId;
          ?>
          <tr>
            <td data-label="Nombre"><?= e((string) ($user->first_name ?? '')) ?></td>
            <td data-label="Apellido"><?= e((string) ($user->last_name ?? '')) ?></td>
            <td data-label="Correo"><?= e((string) ($user->email ?? '')) ?></td>
            <td data-label="Numero"><?= e((string) ($user->phone ?? '')) ?></td>
            <td data-label="Rol"><?= e(method_exists($user, 'roleLabel') ? $user->roleLabel() : (string) ($user->role ?? '')) ?></td>
            <td data-label="Estado"><span class="badge <?= e($badgeClass) ?>"><?= e($statusLabel) ?></span></td>
            <td data-label="Ultimo acceso"><?= e(admin_user_last_access($user)) ?></td>
            <td data-label="Acciones">
              <div class="row-actions" style="display:flex;gap:8px;align-items:center;">
                <a class="chip" title="Editar" href="/admin/users/edit?id=<?= (int) $user->id ?>">Editar</a>

                <?php if (!$isCurrentUser): ?>
                  <form method="post" action="/admin/users/delete" style="display:inline;">
                    <?= \app\Core\Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?= (int) $user->id ?>">
                    <button class="chip" type="submit" title="Eliminar" onclick="return confirm('Eliminar este usuario?')">Eliminar</button>
                  </form>

                  <?php if ($status === 'active'): ?>
                    <form method="post" action="/admin/users/status" style="display:inline;">
                      <?= \app\Core\Csrf::input(); ?>
                      <input type="hidden" name="id" value="<?= (int) $user->id ?>">
                      <input type="hidden" name="status" value="inactive">
                      <button class="chip" type="submit" title="Desactivar">Desactivar</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="/admin/users/status" style="display:inline;">
                      <?= \app\Core\Csrf::input(); ?>
                      <input type="hidden" name="id" value="<?= (int) $user->id ?>">
                      <input type="hidden" name="status" value="active">
                      <button class="chip" type="submit" title="Activar">Activar</button>
                    </form>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="chip" title="Tu cuenta">Actual</span>
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

  <?php if ((int) $pagination['last_page'] > 1): ?>
    <nav class="users-pagination" aria-label="Paginacion de usuarios">
      <span class="users-summary">Pagina <?= (int) $pagination['page'] ?> de <?= (int) $pagination['last_page'] ?></span>
      <div class="users-pages">
        <?php if ((int) $pagination['page'] > 1): ?>
          <a href="<?= e(users_page_url((int) $pagination['page'] - 1, $filters, $pagination)) ?>">Anterior</a>
        <?php endif; ?>
        <?php
          $startPage = max(1, (int) $pagination['page'] - 2);
          $endPage = min((int) $pagination['last_page'], (int) $pagination['page'] + 2);
        ?>
        <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
          <a class="<?= $page === (int) $pagination['page'] ? 'active' : '' ?>" href="<?= e(users_page_url($page, $filters, $pagination)) ?>"><?= $page ?></a>
        <?php endfor; ?>
        <?php if ((int) $pagination['page'] < (int) $pagination['last_page']): ?>
          <a href="<?= e(users_page_url((int) $pagination['page'] + 1, $filters, $pagination)) ?>">Siguiente</a>
        <?php endif; ?>
      </div>
    </nav>
  <?php endif; ?>
</div>

<?php if ($canCreateUsers || $isEditing): ?>
<div class="backdrop" id="modalUser" style="<?= $openModal ? 'display:flex;' : '' ?>">
  <div class="modal">
    <div class="modal-head">
      <h3><?= $isEditing ? 'Editar usuario' : 'Invitar usuario' ?></h3>
      <a class="icon-btn" href="/admin/users">Cerrar</a>
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
            <?php if ($isEditing): ?>
              <small style="display:block;color:#64748b;margin-top:6px;">Si cambias el correo, la cuenta quedará pendiente y se enviará una nueva verificación al correo nuevo.</small>
            <?php endif; ?>
            <?php if (field_error($errors, 'email')): ?>
              <small style="color:#b91c1c;"><?= e(field_error($errors, 'email')) ?></small>
            <?php endif; ?>
          </div>

          <div class="field">
            <label>Telefono</label>
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
                <option value="pending_verification" <?= $currentStatus === 'pending_verification' ? 'selected' : '' ?>>Pendiente verificacion</option>
              </select>
            </div>
          <?php else: ?>
            <div class="field">
              <label>Estado inicial</label>
              <input value="Pendiente verificacion" disabled>
            </div>
          <?php endif; ?>

          <div class="field">
            <label><?= $isEditing ? 'Nueva contraseña (opcional)' : 'Contraseña temporal (opcional)' ?></label>
            <input type="password" name="password" placeholder="<?= $isEditing ? 'Solo si deseas cambiarla' : 'Déjala vacía para que el usuario cree su contraseña por enlace' ?>">
            <small style="display:block;color:#64748b;margin-top:6px;">
              <?= $isEditing
                ? 'Si defines una nueva contraseña, se cerrarán las sesiones activas de ese usuario.'
                : 'Por seguridad, si la dejas vacía no se muestra contraseña. El usuario recibirá enlace para verificar correo y crear contraseña.' ?>
            </small>
            <?php if (field_error($errors, 'password')): ?>
              <small style="color:#b91c1c;"><?= e(field_error($errors, 'password')) ?></small>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!$isEditing): ?>
          <div style="margin-top:14px;padding:12px;border-radius:12px;background:#f8fafc;border:1px solid #e5e7eb;color:#475569;font-size:14px;line-height:1.45;">
            Al invitar el usuario, el sistema enviará un correo de verificación. Si no escribes contraseña, también enviará un enlace para que el usuario cree su propia contraseña. La contraseña generada internamente no se muestra ni se envía en texto plano.
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
