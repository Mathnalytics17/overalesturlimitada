# Despliegue rapido

1. Copia `.env.example` a `.env` y completa credenciales reales.
2. Si tu hosting lo permite, apunta el document root a `/public`.
3. Si tu hosting no lo permite, deja el document root en la raiz y usa el `.htaccess` incluido.
4. Verifica que `mod_rewrite` este habilitado en Apache.
5. Asegura permisos de escritura sobre `runtime/` y `public/img/packageTourist/`.
6. Fuerza HTTPS desde el panel del hosting o desde Apache.
7. Prueba formularios, login, reset de clave y subida de imagenes con credenciales reales.
