# Fase 2: CRM, WhatsApp, trazabilidad y tareas

## Qué incluye
- Captura centralizada de casos desde contacto, PQRS, tiquetes y consulta de paquetes.
- Tabla principal `crm_leads` como caso/leads operativo.
- Trazabilidad en `crm_lead_interactions`.
- Tareas en `crm_lead_tasks`.
- Panel admin para listar casos, ver detalle, cambiar estado, asignar asesor, agregar notas y completar tareas.
- Continuidad por WhatsApp usando `WHATSAPP_NUMBER` del `.env`.

## Flujo
1. Cliente envía formulario.
2. Se crea lead/caso en DB.
3. Se registra interacción automática.
4. Se intenta notificación interna por correo.
5. Se genera link de WhatsApp con mensaje contextual.
6. El admin puede gestionar el caso desde `/admin/leads`.

## Variables nuevas
- `WHATSAPP_NUMBER=57XXXXXXXXXX`

## SQL
Ejecuta manualmente:
- `migrations/20260324_01_crm_phase2.sql`

## Rutas nuevas
- `POST /tickets`
- `POST /packagesTourist/inquiry`
- `GET /admin/leads`
- `GET /admin/leads/show?id=...`
- `POST /admin/leads/status`
- `POST /admin/leads/assign`
- `POST /admin/leads/note`
- `POST /admin/leads/task`
- `POST /admin/leads/task/complete`
