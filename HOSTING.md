# Hosting seguro

## Document root

Configura el dominio para servir esta aplicacion desde:

`public/`

Ruta esperada en este proyecto:

`C:\xampp\htdocs\AlesturTesting\public`

Si el hosting apunta por error a la raiz del proyecto, la regla de `/.htaccess`
actua como contencion adicional, pero no reemplaza una configuracion correcta
del document root.

## Variables y entorno

- Usa `APP_ENV=production`
- Usa `APP_DEBUG=false`
- Ajusta `APP_URL` al dominio real publicado
- Usa `FORCE_HTTPS=true`
- Usa `TRUST_PROXY_HEADERS=true` si el hosting pasa trafico por proxy o balanceador
- Usa `SESSION_SECURE_COOKIE=true`
- Usa `SESSION_SAMESITE=Lax`
- Define `SESSION_LIFETIME_MINUTES=120`
- Define `ADMIN_SESSION_IDLE_MINUTES=30`
- Define `CUSTOMER_SESSION_IDLE_MINUTES=120`
- Si quieres anti-bot visual, define `TURNSTILE_SITE_KEY` y `TURNSTILE_SECRET_KEY`
- No reutilices credenciales locales en produccion

## Carpetas sensibles

No deben ser publicas:

- `core/`
- `controllers/`
- `models/`
- `services/`
- `views/`
- `routes/`
- `vendor/`
- `runtime/`
- `.env`

## Checklist de despliegue

1. El dominio apunta a `public/`.
2. `https://tu-dominio/.env` devuelve 403 o 404.
3. `https://tu-dominio/runtime/logs/app.log` devuelve 403 o 404.
4. `APP_DEBUG` esta en `false`.
5. `APP_URL` usa el dominio real y HTTPS.
6. Los formularios y assets cargan sin `/public` en la URL.
7. Las respuestas HTML incluyen `Content-Security-Policy`, `X-Content-Type-Options`, `Referrer-Policy`, `Cross-Origin-Opener-Policy` y `Cache-Control: no-store`.
8. Si algo deja de cargar en produccion, revisa primero la consola del navegador por bloqueos CSP.
9. Si activas Turnstile, confirma que el widget aparece en login, registro, recuperacion, contacto y PQRS.
10. Revisa `runtime/logs/security.log` para eventos sensibles y validaciones rechazadas.
