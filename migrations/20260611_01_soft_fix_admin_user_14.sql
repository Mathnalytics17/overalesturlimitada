-- Alternativa segura si NO quieres borrar físicamente el usuario 14.
-- Lo deja inactivo, oculto por soft delete y libera el correo para poder invitarlo otra vez.

SET @admin_user_id := 14;

START TRANSACTION;

UPDATE admin_sessions
SET revoked_at = NOW()
WHERE admin_user_id = @admin_user_id
  AND revoked_at IS NULL;

UPDATE admin_users
SET status = 'inactive',
    email = CONCAT('deleted.user.', id, '.', UNIX_TIMESTAMP(), '@deleted.local'),
    phone = NULL,
    locked_until = NULL,
    failed_login_attempts = 0,
    deleted_at = COALESCE(deleted_at, NOW()),
    updated_at = NOW()
WHERE id = @admin_user_id
  AND role <> 'super_admin';

COMMIT;

SELECT id, first_name, last_name, email, role, status, deleted_at
FROM admin_users
WHERE id = @admin_user_id;
