# Manual de Usuario
## Proyecto Over Alestur

Fecha de elaboracion: 19 de mayo de 2026

Este manual describe el uso operativo del proyecto web de Over Alestur desde la perspectiva de usuario final, cliente registrado y personal administrativo. Fue preparado con base en la estructura funcional actual del sistema y busca servir como guia de consulta para la operacion diaria.

## 1. Objetivo del sistema

La plataforma centraliza la atencion comercial y operativa de Over Alestur a traves de:

- Un sitio publico para captar solicitudes de clientes.
- Formularios especializados para contacto, tiquetes, PQRS, paquetes y servicios extra.
- Un modulo de cuentas de cliente para perfil y seguridad basica.
- Un panel administrativo para leads, ventas, PQRS, paquetes, experiencias y usuarios internos.
- Integracion de continuidad por WhatsApp para acelerar la atencion comercial.

## 2. Perfiles de uso

### 2.1 Visitante del sitio web

El visitante puede navegar por las secciones publicas, revisar servicios, enviar solicitudes y continuar la conversacion por WhatsApp.

### 2.2 Cliente registrado

El cliente puede crear una cuenta, iniciar sesion, editar su perfil y cambiar su contrasena.

### 2.3 Administrador o asesor

El administrador accede al panel interno para gestionar:

- Dashboard.
- Contactos potenciales.
- Seguimiento de ventas.
- PQRS.
- Paquetes turisticos.
- Experiencias de viajeros.
- Usuarios administrativos.

## 3. Acceso general a la plataforma

### 3.1 Paginas publicas principales

Las rutas publicas visibles para operacion normal incluyen:

- Inicio.
- Nosotros.
- Contacto.
- Tiquetes.
- Paquetes turisticos.
- Servicios extra.
- PQRS.
- Experiencias.
- Politica de datos.

### 3.2 Reglas comunes en formularios

La plataforma utiliza varias protecciones y validaciones:

- Casilla de aceptacion de tratamiento de datos personales.
- Verificacion de seguridad tipo Turnstile en formularios expuestos.
- Limites de solicitudes por conexion para evitar abuso.
- Redireccion a una vista de confirmacion cuando la solicitud se registra correctamente.

## 4. Sitio publico

### 4.1 Inicio

La pagina de inicio presenta:

- Hero principal con propuesta de valor.
- Accesos a paquetes, contacto y servicios.
- Tarjetas de servicios destacados.
- Preguntas frecuentes.
- Experiencias reales destacadas.
- Llamados a la accion para iniciar contacto comercial.

Uso recomendado:

- Llevar al usuario rapidamente hacia paquetes, tiquetes o contacto.
- Usar esta pagina como punto de entrada para ventas y posicionamiento de marca.

### 4.2 Contacto

La vista de contacto permite registrar interesados generales. El usuario diligencia:

- Nombres y apellidos.
- Correo.
- Telefono.
- Aceptacion de politica de datos.

Resultado esperado:

- La solicitud entra al CRM como lead tipo contacto.
- El sistema puede generar continuidad por WhatsApp para acelerar respuesta.

### 4.3 Tiquetes

La vista de tiquetes permite una captura mas detallada de necesidades de viaje. El formulario actual contempla:

- Datos de contacto del cliente.
- Pais destino.
- Ciudad destino.
- Modo guiado con catalogo local de paises y ciudades.
- Opcion "Ingresar mi propio destino" para captura manual.
- Cantidad de adultos.
- Cantidad de ninos.
- Cantidad de bebes.
- Fecha de ida.
- Opcion de solo ida.
- Fecha de regreso opcional.
- Viaja con mascota.
- Necesita silla de ruedas.
- Lleva articulo deportivo.
- Campo de detalles adicionales.

Ejemplos de uso:

- Solicitud individual de viaje.
- Grupo familiar con varios pasajeros.
- Casos especiales con requerimientos operativos.

Resultado esperado:

- La solicitud entra al CRM como lead tipo tickets.
- En admin queda visible el destino, el trayecto, la composicion de viajeros y los requerimientos especiales.

### 4.4 Paquetes turisticos

Las vistas publicas de paquetes permiten:

- Consultar el listado de paquetes activos.
- Ver el detalle de cada paquete.
- Enviar una consulta comercial sobre un paquete especifico.

La captura incluye contexto del paquete para que el asesor vea de inmediato el interes real del cliente.

### 4.5 Servicios extra

El modulo de servicios extra muestra categorias complementarias como:

- Visas y tramites de viaje.
- Simcards para viajes al exterior.
- Asistencias medicas.
- Receptivo y tours internos.
- Curso de idiomas.

Importante:

- La presentacion visible al usuario evita prometer gestion de pasaportes.
- El servicio de visas debe usarse como categoria de orientacion y apoyo comercial en tramites de viaje.

### 4.6 PQRS

La seccion de PQRS recibe peticiones, quejas, reclamos y solicitudes. Su flujo esta pensado para:

- Registrar la informacion del usuario.
- Permitir clasificacion del caso.
- Manejar adjuntos.
- Llevar trazabilidad posterior desde el panel admin.

### 4.7 Experiencias

La plataforma muestra experiencias de viajeros y tambien permite que el cliente comparta la suya. El contenido puede pasar por un flujo de revision administrativa antes de publicarse.

## 5. Cuenta de cliente

### 5.1 Registro

El cliente puede crear cuenta desde la web. El proceso incluye:

- Datos basicos.
- Credenciales.
- Verificacion de seguridad.
- Confirmacion de correo segun el flujo configurado.

### 5.2 Inicio de sesion

El acceso del cliente se hace desde el modulo de usuarios. El sistema aplica controles de seguridad y limite de intentos.

### 5.3 Recuperacion de contrasena

El flujo de recuperacion permite enviar enlace de restablecimiento y definir una nueva contrasena.

### 5.4 Perfil

El cliente autenticado puede:

- Ver su informacion.
- Editar datos del perfil.
- Cambiar contrasena.

Al cambiar contrasena, el sistema puede forzar nuevo inicio de sesion para reforzar seguridad.

## 6. Confirmacion de solicitudes y continuidad por WhatsApp

Despues de enviar un formulario exitosamente, el sistema muestra una pantalla de confirmacion con:

- Radicado interno del caso.
- Tipo de solicitud.
- Estado inicial.
- Asunto generado.
- Boton para continuar por WhatsApp cuando aplica.

Esto permite que el cliente no sienta que su solicitud se perdio y facilita continuidad comercial inmediata.

## 7. Panel administrativo

### 7.1 Acceso al panel

El panel se accede bajo rutas de administrador autenticado. Una vez dentro, el menu lateral principal incluye:

- Dashboard.
- Contactos potenciales.
- Seguimiento de ventas.
- Usuarios.
- Paquetes.
- Experiencias.
- PQRS.

### 7.2 Perfil administrativo

Desde el menu superior el usuario interno puede:

- Ver perfil.
- Editar perfil.
- Cerrar sesion.

## 8. Dashboard

El dashboard esta orientado a supervision rapida. Consolida:

- Estadisticas generales.
- Resumen del pipeline comercial.
- Alertas.
- Productos mas destacados.
- Resumen financiero.
- Resumen de PQRS.

Uso recomendado:

- Revisar diariamente al iniciar jornada.
- Identificar carga comercial, alertas y necesidad de seguimiento.

## 9. Contactos potenciales

El modulo de leads centraliza los casos provenientes de formularios web y otros canales. Permite:

- Listar casos por filtros.
- Ver detalle del lead.
- Tomar un caso.
- Liberar un caso.
- Cambiar estado.
- Agregar notas.
- Crear tareas.
- Completar tareas.
- Contactar al cliente por WhatsApp.
- Convertir el lead en oportunidad comercial.

### 9.1 Tipos de lead esperados

Los leads pueden provenir de:

- Contacto general.
- Tiquetes.
- Paquetes.
- Servicios extra.
- Otros flujos integrados de la web.

### 9.2 Buen uso operativo

- Tomar solo los casos que realmente se van a trabajar.
- Registrar notas claras y cortas.
- Crear tareas con fecha cuando el caso requiera seguimiento.
- Usar la conversion a oportunidad cuando ya exista intencion comercial real.

## 10. Seguimiento de ventas

El modulo comercial esta compuesto por oportunidades y ordenes.

### 10.1 Oportunidades comerciales

Permite:

- Crear oportunidades manualmente.
- Crear oportunidades desde leads.
- Filtrar por etapa o asesor.
- Ver la ficha completa de una oportunidad.
- Asignarla al asesor actual.
- Cambiar etapa comercial.
- Programar seguimiento.
- Agregar notas.
- Marcar como ganada.
- Marcar como perdida.
- Visualizar kanban comercial.
- Editar datos comerciales y del cliente.

### 10.2 Ordenes de venta

Permite:

- Generar orden desde oportunidad.
- Consultar ordenes.
- Ver detalle operativo.
- Actualizar estado operativo.
- Actualizar notas.

### 10.3 Pagos y cotizaciones

Dentro del flujo comercial se incluyen acciones para:

- Registrar pagos.
- Verificar pagos.
- Rechazar pagos.
- Crear cotizaciones.
- Marcar cotizacion como enviada, aceptada o rechazada.

## 11. PQRS en el panel admin

El modulo de PQRS permite una gestion completa del caso:

- Listado con filtros.
- Vista detalle del caso.
- Toma y liberacion.
- Cambio de estado.
- Notas internas.
- Tareas.
- Descarga de adjuntos.
- Trazabilidad de eventos.

Buenas practicas:

- Cambiar estado apenas el caso pase a atencion activa.
- Registrar cada avance relevante.
- Usar tareas para compromisos con fecha.
- Descargar adjuntos solo cuando sea necesario para el caso.

## 12. Paquetes turisticos en admin

El modulo de paquetes permite administrar el catalogo comercial:

- Ver listado de paquetes.
- Crear paquete nuevo.
- Duplicar un paquete existente.
- Editar paquete.
- Activar o desactivar estado.
- Marcar destacados.

La configuracion del paquete puede incluir:

- Titulo y slug.
- Ubicacion.
- Precio desde.
- Duracion.
- Descripciones corta y general.
- Inclusiones y exclusiones.
- Highlights.
- Condiciones.
- Itinerario.
- Etiquetas.
- Imagen de portada y galeria.

## 13. Experiencias en admin

Este modulo administra testimonios y experiencias de viaje:

- Filtrar por texto, estado o tipo.
- Revisar experiencia.
- Aprobar.
- Rechazar con observaciones.
- Editar contenido.

Uso recomendado:

- Revisar ortografia, claridad y pertinencia antes de aprobar.
- Mantener una linea de contenido alineada con la marca.

## 14. Usuarios administrativos

El modulo de usuarios internos permite:

- Listar usuarios.
- Filtrar por estado, rol o texto.
- Crear usuario.
- Editar usuario.
- Cambiar estado.
- Eliminar usuario.

Buenas practicas:

- Otorgar roles segun necesidad real.
- Desactivar usuarios inactivos.
- Evitar compartir credenciales.

## 15. Seguridad y buenas practicas de operacion

- Usar contrasenas robustas.
- No compartir sesiones administrativas.
- Cerrar sesion al terminar la jornada.
- No reenviar enlaces de restablecimiento o verificacion por canales inseguros.
- Registrar notas objetivas y sin datos innecesarios.
- Verificar adjuntos antes de descargarlos o reenviarlos.
- Mantener actualizados los datos visibles de contacto institucional.

## 16. Problemas frecuentes y solucion basica

### 16.1 El formulario no se envia

Revisar:

- Campos obligatorios.
- Politica de datos.
- Verificacion de seguridad.
- Fechas coherentes en tickets.

### 16.2 El cliente no aparece en ventas

Revisar:

- Si el lead fue creado correctamente.
- Si ya fue tomado por un asesor.
- Si hace falta convertirlo manualmente a oportunidad.

### 16.3 No aparece una ciudad en tickets

Alternativas:

- Usar el modo guiado con lista.
- Activar "Ingresar mi propio destino".
- Registrar manualmente pais y ciudad.

### 16.4 Un caso PQRS no permite avanzar

Revisar:

- Que el asesor haya tomado el caso.
- Que exista informacion suficiente.
- Que las tareas pendientes queden registradas.

## 17. Checklist diaria recomendada para administracion

Al iniciar el dia:

- Revisar dashboard.
- Revisar leads nuevos.
- Revisar oportunidades con seguimiento pendiente.
- Revisar PQRS activas.

Durante el dia:

- Tomar casos asignables.
- Actualizar estados.
- Registrar notas y tareas.
- Convertir leads de alto interes en oportunidades.

Al cerrar el dia:

- Validar tareas pendientes para el dia siguiente.
- Confirmar que no queden casos criticos sin responsable.
- Cerrar sesion del panel.

## 18. Datos institucionales de referencia

- Nombre operativo: Over Alestur.
- Telefono de contacto principal: 3157337390.
- Correo institucional principal: gerencia@overalestur.com.co.

## 19. Observacion final

Este manual puede seguir creciendo con capturas de pantalla, politicas internas, flujos operativos por area y procedimientos mas detallados de ventas o PQRS. La presente version busca ser una base clara, util y mantenible para el uso diario del sistema.
