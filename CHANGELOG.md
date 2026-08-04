# Historial de Cambios (CHANGELOG)

Todas las modificaciones notables de este proyecto serán documentadas en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/), y este proyecto adhiere a [Semantic Versioning 2.0.0](https://semver.org/lang/es/).

---

## [Unreleased]
### Planificado para [v1.1.0]
- Rediseño de interfaz de usuario según la línea gráfica corporativa de Contafit.
- Renovación del selector de colores para categorización de eventos.
- Localización del formato de visualización de fechas al estándar ecuatoriano (`dd/mm/yyyy` / UTC-5).

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
