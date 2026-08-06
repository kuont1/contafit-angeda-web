<p align="center">
  <h1 align="center">📅 ContaFit Agenda Web</h1>
  <p align="center">
    <strong>Sistema Web Inteligente de Gestión de Tareas, Calendario, Feriados y Recordatorios por Correo</strong>
  </p>
  <p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
    <img src="https://img.shields.io/badge/PostgreSQL-15.0-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
    <img src="https://img.shields.io/badge/JWT-RFC_7519-000000?style=for-the-badge&logo=jsonwebtokens&logoColor=white" alt="JWT Auth">
    <img src="https://img.shields.io/badge/PHPUnit-34%2F34_Pass-2496ED?style=for-the-badge&logo=php&logoColor=white" alt="PHPUnit Pass">
    <img src="https://img.shields.io/badge/Release-v1.1.0--Stable-success?style=for-the-badge" alt="v1.1.0 Stable">
  </p>
</p>

---

## 📌 Visión General del Proyecto

**ContaFit Agenda Web** es una plataforma web integral construida bajo una **Arquitectura N-Capas** que separa el motor del backend (API REST en Laravel 12) de la interfaz de usuario visual (Single Page Application - SPA/PWA).

El sistema permite a los usuarios gestionar su tiempo de forma eficiente mediante un **calendario interactivo**, **dashboard tipo checklist**, **creación de eventos recurrentes**, **notificaciones asíncronas por correo electrónico vía Brevo API v3** y **papelera de reciclaje con purga automática**.

---

## 🛠️ Requerimientos Cumplidos y Funcionalidades Clave

### 🔐 Módulo 1: Gestión de Usuarios y Autenticación
- **RF-01 (Registro):** Validación estricta de correo único y complejidad de contraseñas.
- **RF-02 / RNF-02 (Autenticación JWT):** Autenticación mediante **Tokens JWT (RFC 7519)** firmados criptográficamente con algoritmo `HS256`.
- **RF-03 (Borrado de Cuenta):** Solicitud de baja con confirmación de contraseña, código de verificación de 6 dígitos por correo y **Hard Delete** en cascada de usuario, eventos, notificaciones y tokens.

### 📅 Módulo 2: Calendario y Eventos
- **RF-04 / RF-05 (Creación y Categorización):** Tipos de evento (*Tarea*, *Recordatorio*, *Fecha Importante*) con paleta de colores HSL/HEX, notas y hora de inicio/fin.
- **RF-06 (Eventos Recurrentes):** Repeticiones diarias, semanales, mensuales o anuales con soporte para editar o eliminar ocurrencias individuales sin alterar la serie.
- **RF-07 (Búsqueda y Filtros):** Búsqueda por palabra clave y filtrado en tiempo real sin recargar la página.
- **RF-08 (Dashboard Checklist):** Vista diaria de tareas con estados (*Pendiente*, *En Progreso*, *Completada*).

### 🇪🇨 Módulo 3: Feriados Nacionales de Ecuador
- **RF-09 (Feriados):** Integración con la API oficial de Feriados de Ecuador (`Feriados.io`) mostrados con distintivo visual y opción para mostrar/ocultar.

### ⏰ Módulo 4: Notificaciones y Papelera de Reciclaje
- **RF-10 / RNF-04 (Notificaciones Asíncronas):** Envío de alertas por correo electrónico antes del evento mediante **Brevo API REST v3** y trabajadores en segundo plano (`Jobs & Queues`).
- **RF-11 (Papelera & Soft Delete):** Borrado lógico temporal (`SoftDeletes`) con vista de restauración/eliminación definitiva y comando de purga automática para eventos con más de 30 días (`php artisan events:purge-trash`).

---

## 💻 Requisitos Previos

Asegúrate de contar con los siguientes elementos instalados en tu entorno de desarrollo:

- **PHP** >= 8.2 (con extensiones `pdo`, `pdo_pgsql`, `mbstring`, `openssl`, `curl`)
- **Composer** >= 2.5
- **PostgreSQL** >= 15.0
- **Node.js** >= 18.0 (opcional para bundling con Vite)
- **Docker & Docker Compose** (opcional para despliegue en contenedores)

---

## 🚀 Despliegue en Entorno Local (Sin Docker)

### Paso 1: Clonar el Repositorio
```bash
git clone https://github.com/kuont1/contafit-angeda-web.git
cd contafit-agenda
```

### Paso 2: Instalar Dependencias de PHP
```bash
composer install
```

### Paso 3: Configurar Variables de Entorno
Copia el archivo de ejemplo `.env.example` a `.env`:
```bash
cp .env.example .env
```

### Paso 4: Generar la Clave de Aplicación (APP_KEY)
```bash
php artisan key:generate
```

### Paso 5: Configurar la Base de Datos PostgreSQL
Asegúrate de haber creado la base de datos en PostgreSQL y actualiza las credenciales en tu archivo `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=contafit_agenda
DB_USERNAME=postgres
DB_PASSWORD=tu_contrasena
```

### Paso 6: Ejecutar las Migraciones y Seeders
```bash
php artisan migrate --seed
```

### Paso 7: Iniciar el Servidor de Desarrollo
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Paso 8: Iniciar el Trabajador de Colas (Workers)
En una segunda terminal, inicia el worker para procesar las notificaciones asíncronas:
```bash
php artisan queue:work
```

Accede a la aplicación desde tu navegador en **`http://127.0.0.1:8000`**.

---

## 🐳 Despliegue en Entorno Local con Docker

Si prefieres desplegar utilizando contenedores Docker:

```bash
# 1. Iniciar los contenedores de la aplicación y PostgreSQL
docker-compose up -d --build

# 2. Ejecutar las migraciones dentro del contenedor
docker-compose exec app php artisan migrate --seed
```

Accede a la aplicación en **`http://127.0.0.1:8000`**.

---

## ⚙️ Tabla de Variables de Entorno (`.env.example`)

| Variable | Descripción | Valor por Defecto |
| :--- | :--- | :--- |
| `APP_NAME` | Nombre oficial del sistema | `"ContaFit Agenda Web"` |
| `APP_ENV` | Entorno de ejecución (`local`, `production`) | `local` |
| `APP_KEY` | Clave criptográfica de la aplicación | *Generado con key:generate* |
| `APP_URL` | URL base del servidor | `http://127.0.0.1:8000` |
| `APP_TIMEZONE` | Zona horaria para manejo de fechas (RNF-03) | `America/Guayaquil` |
| `DB_CONNECTION` | Driver de base de datos | `pgsql` |
| `DB_HOST` | Host de PostgreSQL | `127.0.0.1` |
| `DB_PORT` | Puerto de PostgreSQL | `5432` |
| `DB_DATABASE` | Nombre de la base de datos | `contafit_agenda` |
| `DB_USERNAME` | Usuario de PostgreSQL | `postgres` |
| `DB_PASSWORD` | Contraseña de PostgreSQL | `root` |
| `QUEUE_CONNECTION` | Driver de colas para procesamiento pesado (RNF-04) | `database` |
| `CACHE_STORE` | Driver de caché del sistema | `file` |
| `BREVO_API_KEY` | Clave API v3 para envío de notificaciones (RF-10) | `xkeysib-...` |
| `FERIADOS_IO_API_KEY` | Clave API para feriados de Ecuador (RF-09) | `frd_...` |
| `FERIADOS_IO_COUNTRY` | Código ISO del país para feriados | `ec` |

---

## 🧪 Pruebas Automatizadas y Mantenimiento

### Ejecutar Suite de Pruebas PHPUnit
El proyecto cuenta con una suite completa de **33 pruebas automatizadas (140 aserciones)** que cubren autenticación JWT, eventos recurrentes, papelera y notificaciones:

```bash
php artisan test
```

### Formateo Automático de Código (PSR-12)
Para verificar y aplicar las reglas de estilo oficiales de Laravel Pint:
```bash
vendor/bin/pint
```

### Comando de Purga de Papelera (> 30 días)
Para ejecutar manualmente la limpieza de eventos que superen 30 días en la papelera:
```bash
php artisan events:purge-trash
```

---

## 📄 Licencia y Contribución

Por favor revisa la [Guía de Contribución (`CONTRIBUTING.md`)](CONTRIBUTING.md) para conocer los estándares de código, reglas de commits (`Conventional Commits`) y flujo de Pull Requests.

Desarrollado con ❤️ para el proyecto de **Ingeniería de Software II - UTMACH**.
