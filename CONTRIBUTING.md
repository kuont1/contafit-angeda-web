# 🛠️ Guía de Estilo y Reglas de Contribución (`CONTRIBUTING.md`)

¡Gracias por tu interés en contribuir al proyecto **ContaFit Agenda Web**! Este documento establece los estándares de codificación, flujos de trabajo con Git, convenciones de mensajes de commit y procedimientos para proponer cambios mediante Pull Requests.

---

## 📌 Tabla de Contenidos
1. [Principios Generales](#-principios-generales)
2. [Estructura de Ramas (Git Flow)](#-estructura-de-ramas-git-flow)
3. [Convenciones de Commits (Conventional Commits)](#-convenciones-de-commits-conventional-commits)
4. [Estándares de Codificación y Linters](#-estándares-de-codificación-y-linters)
5. [Flujo de Trabajo para Pull Requests (PR)](#-flujo-de-trabajo-para-pull-requests-pr)
6. [Ejecución de Pruebas Automatizadas](#-ejecución-de-pruebas-automatizadas)

---

## 🌟 Principios Generales
- Mantener la separación de responsabilidades entre el **Backend (API REST con Laravel 12)** y el **Frontend (SPA/PWA)**.
- Todas las rutas protegidas de la API deben requerir autenticación mediante **JWT (RFC 7519)** con el middleware `jwt.auth`.
- Ninguna contribución será fusionada sin pasar la suite completa de pruebas de PHPUnit (`php artisan test`).

---

## 🌿 Estructura de Ramas (Git Flow)

Adoptamos el modelo estandarizado de ramas **Git Flow**:

* `main`: Rama de producción. Solo contiene código estable y liberado.
* `develop`: Rama principal de integración.
* `feature/<nombre-de-la-funcionalidad>`: Desarrollo de nuevos requerimientos (ej: `feature/rf-10-notificaciones-brevo`).
* `bugfix/<nombre-del-error>`: Corrección de errores detectados durante QA (ej: `bugfix/cooldown-borrado-cuenta`).
* `hotfix/<incidencia-critica>`: Parches urgentes aplicados directamente sobre `main`.

### Nombres de ramas recomendados:
```bash
git checkout -b feature/rf-06-eventos-recurrentes
git checkout -b bugfix/jwt-middleware-token-validation
```

---

## 📝 Convenciones de Commits (Conventional Commits)

Los mensajes de commit deben seguir la especificación de **[Conventional Commits v1.0.0](https://www.conventionalcommits.org/)**:

### Formato:
```text
<tipo>(<alcance opcional>): <descripción corta en imperativo>

[cuerpo opcional]

[pie de página opcional]
```

### Tipos permitidos:
- **`feat`**: Una nueva funcionalidad (ej. `feat(auth): implementar autenticación JWT RFC 7519`).
- **`fix`**: Corrección de un error (ej. `fix(trash): ocultar ocurrencias excluidas de la papelera tras borrado definitivo`).
- **`docs`**: Cambios en la documentación (ej. `docs: actualizar README y CONTRIBUTING.md`).
- **`style`**: Formato de código que no afecta la lógica (espacios, comas, Laravel Pint).
- **`refactor`**: Reestructuración de código sin cambiar comportamiento ni corregir bugs.
- **`test`**: Añadir o corregir pruebas unitarias o de integración.
- **`chore`**: Tareas administrativas o actualización de dependencias (`composer.json`, `.env.example`).

### Ejemplos de Commits Válidos:
```bash
git commit -m "feat(auth): agregar verificacion por codigo de 6 digitos para borrado de cuenta (RF-03)"
git commit -m "fix(notifications): enviar alertas síncronas si scheduled_at es menor o igual al tiempo actual"
git commit -m "style: formatear codigo php usando Laravel Pint PSR-12"
git commit -m "test(jwt): agregar suite JwtAuthTest para validar estructura del token JWT"
```

---

## 🎨 Estándares de Codificación y Linters

### PHP (Backend)
- Seguimos los estándares **PSR-1, PSR-4 y PSR-12 / PER CS 2.0**.
- Se utiliza **[Laravel Pint](https://laravel.com/docs/pint)** como formateador automático de código.
- Para verificar y aplicar el formato de código automáticamente:
  ```bash
  # Verificar errores de estilo
  vendor/bin/pint --test

  # Aplicar correcciones de formato automáticamente
  vendor/bin/pint
  ```

### JavaScript / HTML / CSS (Frontend)
- Indentación estándar de 4 espacios en plantillas `.blade.php` y archivos JavaScript.
- Componentes modulares y limpios sin dependencias externas pesadas.

---

## 🔄 Flujo de Trabajo para Pull Requests (PR)

1. **Crear una rama:** Crea tu rama de trabajo desde `develop`.
2. **Desarrollar y probar:** Escribe el código y añade pruebas en `tests/Feature`.
3. **Formatear el código:** Ejecuta `vendor/bin/pint`.
4. **Ejecutar la suite de pruebas:** `php artisan test`.
5. **Hacer Push:** Envía tu rama al repositorio remoto.
   ```bash
   git push origin feature/rf-06-eventos-recurrentes
   ```
6. **Abrir Pull Request:** Abre la solicitud hacia `develop` describiendo los cambios y los requerimientos vinculados (RF-XX / RNF-XX).

---

## 🧪 Ejecución de Pruebas Automatizadas

Antes de solicitar una revisión de código, todos los tests deben pasar exitosamente:

```bash
# Ejecutar todas las pruebas del proyecto
php artisan test

# Ejecutar una prueba específica
php artisan test --filter=JwtAuthTest
```
