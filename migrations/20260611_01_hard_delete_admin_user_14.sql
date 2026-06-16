-- Limpieza manual del usuario administrador ID 14.
-- Usa este archivo SOLO si quieres eliminar físicamente el usuario de la base de datos.
-- Para uso normal del sistema, el botón Eliminar ya deja el usuario inactivo/oculto.

SET @admin_user_id := 14;

START TRANSACTION;

-- 1) Desasociar referencias comerciales/operativas para no borrar historial de negocio.
UPDATE crm_leads
SET assigned_admin_user_id = NULL
WHERE assigned_admin_user_id = @admin_user_id;

UPDATE crm_lead_interactions
SET admin_user_id = NULL
WHERE admin_user_id = @admin_user_id;

UPDATE crm_lead_tasks
SET admin_user_id = NULL
WHERE admin_user_id = @admin_user_id;

UPDATE pqrs_cases
SET assigned_admin_user_id = NULL
WHERE assigned_admin_user_id = @admin_user_id;

UPDATE pqrs_case_events
SET admin_user_id = NULL
WHERE admin_user_id = @admin_user_id;

UPDATE pqrs_case_tasks
SET admin_user_id = NULL
WHERE admin_user_id = @admin_user_id;

UPDATE sales_opportunities
SET assigned_admin_user_id = NULL
WHERE assigned_admin_user_id = @admin_user_id;

UPDATE sales_opportunities
SET created_by_admin_id = NULL
WHERE created_by_admin_id = @admin_user_id;

UPDATE sales_opportunities
SET updated_by_admin_id = NULL
WHERE updated_by_admin_id = @admin_user_id;

UPDATE sales_opportunity_events
SET admin_user_id = NULL
WHERE admin_user_id = @admin_user_id;

UPDATE sales_orders
SET assigned_admin_user_id = NULL
WHERE assigned_admin_user_id = @admin_user_id;

UPDATE sales_orders
SET created_by_admin_id = NULL
WHERE created_by_admin_id = @admin_user_id;

UPDATE sales_orders
SET updated_by_admin_id = NULL
WHERE updated_by_admin_id = @admin_user_id;

UPDATE sales_payments
SET reported_by_admin_id = NULL
WHERE reported_by_admin_id = @admin_user_id;

UPDATE sales_payments
SET verified_by_admin_id = NULL
WHERE verified_by_admin_id = @admin_user_id;

UPDATE sales_quotes
SET created_by_admin_id = NULL
WHERE created_by_admin_id = @admin_user_id;

UPDATE sales_quotes
SET updated_by_admin_id = NULL
WHERE updated_by_admin_id = @admin_user_id;

UPDATE tour_packages
SET created_by_admin_id = NULL
WHERE created_by_admin_id = @admin_user_id;

UPDATE tour_packages
SET updated_by_admin_id = NULL
WHERE updated_by_admin_id = @admin_user_id;

UPDATE tour_package_status_logs
SET changed_by_admin_id = NULL
WHERE changed_by_admin_id = @admin_user_id;

UPDATE travel_experiences
SET approved_by_admin_id = NULL
WHERE approved_by_admin_id = @admin_user_id;

UPDATE travel_experiences
SET rejected_by_admin_id = NULL
WHERE rejected_by_admin_id = @admin_user_id;

-- 2) Eliminar registros dependientes con FK directa a admin_users.
DELETE FROM admin_sessions
WHERE admin_user_id = @admin_user_id;

DELETE FROM admin_email_verifications
WHERE admin_user_id = @admin_user_id;

DELETE FROM admin_password_resets
WHERE admin_user_id = @admin_user_id;

DELETE FROM admin_login_logs
WHERE admin_user_id = @admin_user_id;

-- 3) Eliminar físicamente el usuario. No borra super administradores por seguridad.
DELETE FROM admin_users
WHERE id = @admin_user_id
  AND role <> 'super_admin';

COMMIT;

-- Verificación final: debe devolver 0 filas.
SELECT * FROM admin_users WHERE id = @admin_user_id;
