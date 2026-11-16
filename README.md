# Clínica Odontológica API

API REST desarrollada con Laravel para la gestión de una clínica odontológica.

## 1. Requisitos

Antes de iniciar el proyecto, asegúrate de tener instaladas las siguientes herramientas:

- PHP 8.2 o superior.
- Composer 2.
- MySQL o MariaDB.
- Git.

## 2. Instalación de dependencias

Desde la terminal, dentro de la carpeta del proyecto, ejecuta:

```bash
composer install
```

## 3. Configuración del entorno

Configura el archivo `.env` con los datos de conexión a tu base de datos.

Ejemplo:

```env
APP_NAME="Clinica Odontologica API"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES
APP_TIMEZONE=America/La_Paz

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clinica_odontologica
DB_USERNAME=root
DB_PASSWORD=

API_TOKEN_TTL_MINUTES=480
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_SECONDS=900
CORS_ALLOWED_ORIGINS=http://localhost:5173
CLINIC_CURRENCY=BOB

SEED_ADMIN_EMAIL=admin@clinica.test
SEED_ADMIN_PASSWORD=CAMBIAR_POR_UNA_CLAVE_SEGURA

L5_SWAGGER_GENERATE_ALWAYS=true
```

Crea previamente la base de datos `clinica_odontologica` en MySQL o MariaDB.

Genera la clave de aplicación:

```bash
php artisan key:generate
```

**Importante:** conserva la misma `APP_KEY` en instalaciones que ya tengan información clínica cifrada.

## 4. Limpiar configuración y caché

```bash
php artisan optimize:clear
```

## 5. Ejecutar migraciones y seeders

Para crear las tablas y cargar los datos iniciales:

```bash
php artisan migrate --seed
```

Este proceso registra los roles, permisos y el usuario administrador inicial.

El administrador se configura mediante las variables:

- `SEED_ADMIN_EMAIL`
- `SEED_ADMIN_PASSWORD`

Configura una contraseña segura antes de ejecutar los seeders por primera vez.

Para consultar el estado de las migraciones:

```bash
php artisan migrate:status
```

**Importante:** `php artisan migrate:fresh --seed` elimina todas las tablas y sus datos. Utilízalo únicamente cuando quieras reconstruir una base de datos de desarrollo.

## 6. Generar documentación Swagger

Ejecuta:

```bash
php artisan l5-swagger:generate
```

## 7. Iniciar el servidor

Ejecuta:

```bash
php artisan serve
```

La API estará disponible en:

```text
http://127.0.0.1:8000
```

La documentación Swagger estará disponible en:

```text
http://127.0.0.1:8000/api/documentation
```

## 8. Autenticación

Para iniciar sesión utiliza el endpoint:

```http
POST /api/v1/auth/login
```

Ejemplo de solicitud:

```json
{
    "email": "admin@clinica.test",
    "password": "LA_CLAVE_CONFIGURADA",
    "device_name": "clinica-frontend"
}
```

La respuesta proporcionará un token de autenticación.

Para consumir los endpoints protegidos, utiliza los siguientes encabezados:

```http
Authorization: Bearer TU_TOKEN
Accept: application/json
Content-Type: application/json
```

En Swagger, utiliza el botón **Authorize** para introducir el token.

## 9. Endpoints principales

Todos los endpoints utilizan el prefijo `/api/v1`.

### Autenticación

| Método | Endpoint           | Descripción                   |
| ------ | ------------------ | ----------------------------- |
| POST   | `/auth/login`      | Iniciar sesión                |
| GET    | `/auth/me`         | Consultar usuario autenticado |
| POST   | `/auth/logout`     | Cerrar sesión actual          |
| POST   | `/auth/logout-all` | Cerrar todas las sesiones     |
| PUT    | `/auth/password`   | Cambiar contraseña            |

### Recursos del sistema

| Recurso            | Endpoint            |
| ------------------ | ------------------- |
| Usuarios           | `/users`            |
| Pacientes          | `/patients`         |
| Odontólogos        | `/dentists`         |
| Tratamientos       | `/treatments`       |
| Citas              | `/appointments`     |
| Historias clínicas | `/clinical-records` |
| Pagos              | `/payments`         |

Los recursos disponen de operaciones de consulta, creación, actualización y eliminación, según los permisos asignados.

### Endpoints adicionales

| Método | Endpoint                    | Descripción                 |
| ------ | --------------------------- | --------------------------- |
| PATCH  | `/appointments/{id}/status` | Cambiar estado de una cita  |
| GET    | `/roles`                    | Consultar roles disponibles |

## 10. Roles y permisos

El sistema contempla los siguientes roles:

| Rol            | Descripción                                                        |
| -------------- | ------------------------------------------------------------------ |
| `admin`        | Acceso administrativo completo.                                    |
| `receptionist` | Gestión de pacientes, citas y operaciones de recepción.            |
| `dentist`      | Acceso a pacientes, citas e historias clínicas según sus permisos. |

Los permisos utilizan el formato `{recurso}.{acción}`, por ejemplo:

- `patients.view`
- `patients.create`
- `appointments.update`
- `payments.view`

## 11. Ejecutar pruebas

Para ejecutar todas las pruebas automatizadas:

```bash
php artisan test
```

Para ejecutar las pruebas de la API clínica:

```bash
php artisan test --filter=ClinicApiTest
```

## 12. Comandos útiles

| Comando                             | Descripción                     |
| ----------------------------------- | ------------------------------- |
| `php artisan serve`                 | Iniciar servidor local          |
| `php artisan migrate`               | Ejecutar migraciones pendientes |
| `php artisan migrate:status`        | Consultar estado de migraciones |
| `php artisan db:seed`               | Ejecutar seeders                |
| `php artisan optimize:clear`        | Limpiar cachés                  |
| `php artisan route:list --path=api` | Consultar rutas de la API       |
| `php artisan l5-swagger:generate`   | Generar documentación Swagger   |
| `php artisan test`                  | Ejecutar pruebas                |

## 13. Configuración para producción

Antes de desplegar el sistema en un entorno de producción:

- Configurar `APP_ENV=production`.
- Establecer `APP_DEBUG=false`.
- Configurar HTTPS y el dominio correspondiente en `APP_URL`.
- Configurar los dominios autorizados en `CORS_ALLOWED_ORIGINS`.
- Utilizar credenciales seguras para la base de datos.
- Proteger la clave `APP_KEY`.
- Mantener respaldos periódicos de la base de datos.
- Desactivar la generación automática de Swagger en producción.
- Restringir el acceso a la documentación de la API según las políticas de seguridad.

---

**Clínica Odontológica API**  
Desarrollado con Laravel.
