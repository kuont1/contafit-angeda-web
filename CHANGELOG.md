# Historial de Cambios (CHANGELOG)

Todas las modificaciones notables de este proyecto serán documentadas en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y este proyecto adhiere a [Semantic Versioning 2.0.0](https://semver.org/lang/es/).

---

## [1.1.0] - 2026-08-06
### Añadido y Mejorado
- **Navegación Inteligente y Búsqueda Case-Insensitive:** La búsqueda en el calendario ya no distingue entre mayúsculas y minúsculas (`LOWER(title)` / `LOWER(description)`). Auto-navegación automática al mes/año de la fecha encontrada y resaltado visual con la insignia `ENCONTRADO`.
- **Despliegue Dinámico de Requisitos de Contraseña:** El panel de requisitos de seguridad de contraseña ahora se oculta por defecto y aparece dinámicamente al escribir o al presionar Enter/validar, indicando requisitos cumplidos (`✓`) y pendientes (`❌`).
- **Validación Estricta de Nombres y Apellidos:** Restricción de tipeo de números y caracteres especiales en nombres y apellidos en frontend (`oninput`) y backend (`regex`). Mensajes de error formateados en español claro.
- **Favicon Corporativo Contafit:** Adición de isotipo SVG corporativo en el `<head>` del sitio.
- **Pruebas de Carga K6:** Suite de pruebas de rendimiento con 400 solicitudes en 4 endpoints críticos (`/api/login`, `/api/events`, `/api/holidays`, `/api/dashboard/today`), logrando **100% de éxito (0.00% errores)** y latencia promedio de 270 ms - 350 ms en lecturas.
- **Evaluación de Usabilidad SUS/SEQ:** Aplicación de matriz ISO 9241-11 con 10 evaluadores, obteniendo un puntaje global **SUS de 69.75 / 100 (Aceptable / Grado B+)** y 90% de efectividad.
- **Pruebas Automatizadas:** Cobertura ampliada a **34/34 pruebas en PHPUnit (100% de éxito)**.

---

## [1.0.0] - 2026-07-31
### Añadido (Versión Inicial / MVP Funcional)
- **Autenticación y Seguridad:** Autenticación JWT (RFC 7519) con algoritmo `HS256`, encriptación de contraseñas con Bcrypt (cost=12) y middleware `JwtMiddleware`.
- **Eliminación Definitiva de Cuenta:** Confirmación con contraseña, código de verificación al correo vía Brevo API v3, bloqueo Rate Limit HTTP 429 de 5 minutos e eliminación física en cascada en PostgreSQL.
- **Gestión de Eventos y Recurrencia:** CRUD completo de eventos (Tarea, Recordatorio, Fecha Importante), repeticiones (diaria, semanal, mensual, anual) y manejo de exclusión de instancias individuales (`status='excluded'`).
- **Filtros Avanzados y Búsqueda:** Búsqueda en tiempo real por título/descripción y filtros dinámicos por tipo, estado y rango de fechas.
- **Dashboard y Feriados de Ecuador:** Vista diaria tipo checklist con integración de API `Feriados.io` (Ecuador).
- **Notificaciones Asíncronas:** Encolamiento asíncrono con trabajadoras en segundo plano (`SendNotificationJob`) y envío de correos vía Brevo REST API v3.
- **Papelera de Reciclaje:** Borrado suave (`SoftDeletes`), ocultamiento automático tras borrado definitivo y comando CLI de purga automática a 30 días (`php artisan events:purge-trash`).
- **Pruebas y Calidad de Código:** Suite de 33 pruebas automatizadas en PHPUnit (100% de éxito), 100% de cumplimiento PSR-12 con Laravel Pint y latencia promedio de 411.36 ms bajo concurrencia.
