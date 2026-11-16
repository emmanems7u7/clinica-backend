# API Clínica Odontológica

API REST para la gestión de una clínica odontológica, desarrollada con
Laravel. Incluye autenticación mediante Laravel Sanctum, roles y
permisos con Spatie, documentación OpenAPI/Swagger y pruebas
automatizadas.

> Este manual está pensado para levantar el proyecto después de clonarlo
> desde GitHub. Los archivos de la API ya deben estar incorporados en el
> proyecto Laravel.

## 1. Tecnologías y requisitos

- PHP 8.2 o superior.
- Composer 2.
- Laravel 11 o 12 (según la versión usada por este repositorio).
- MySQL o MariaDB.
- Extensiones PHP requeridas por Laravel y los paquetes instalados,
  entre ellas `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`,
  `ctype`, `json`, `fileinfo` y `bcmath`.
- Git.
- Node.js y npm solo si el proyecto también contiene recursos frontend
  que deban compilarse. No son necesarios para ejecutar únicamente la
  API.

Paquetes principales:

- `laravel/sanctum`: autenticación mediante tokens Bearer.
- `spatie/laravel-permission`: roles y permisos.
- `darkaonline/l5-swagger`: documentación Swagger/OpenAPI (dependencia
  de desarrollo).

## 2. Clonar el repositorio e instalar dependencias

Desde la terminal:

```bash
git clone URL_DEL_REPOSITORIO
cd clinica-api
composer install
```

Reemplaza `URL_DEL_REPOSITORIO` por la URL real del repositorio.

Asegúrate de que `composer.json` y `composer.lock` estén versionados en
Git. No subas la carpeta `vendor`; se reconstruye con
`composer install`.

## 3. Preparar el archivo de entorno

Copia el archivo de ejemplo:

En Windows (PowerShell):

```powershell
Copy-Item .env.example .env
```

En Git Bash, Linux o macOS:

```bash
cp .env.example .env
```

Configura en `.env` los datos de la aplicación y de la base de datos.
Ejemplo para MySQL local:

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

# Configuración de la API
API_TOKEN_TTL_MINUTES=480
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_SECONDS=900
CORS_ALLOWED_ORIGINS=http://localhost:5173
CLINIC_CURRENCY=BOB

# Administrador inicial: cambiar la contraseña antes de ejecutar los seeders
SEED_ADMIN_EMAIL=admin@clinica.test
SEED_ADMIN_PASSWORD=CAMBIAR_POR_UNA_CLAVE_SEGURA

# Swagger: habilitado para desarrollo local
L5_SWAGGER_GENERATE_ALWAYS=true
```

Crea previamente la base de datos `clinica_odontologica` en
MySQL/MariaDB, o usa el nombre que hayas configurado en `DB_DATABASE`.

Genera la clave de Laravel:

```bash
php artisan key:generate
```

**Importante sobre `APP_KEY`:** algunos datos clínicos se almacenan
cifrados mediante los casts `encrypted` de Eloquent. En cada entorno
debes configurar una clave válida y conservarla de forma segura. No
cambies ni regeneres la `APP_KEY` de una instalación que ya tenga datos
clínicos cifrados, porque esos datos dejarían de poder descifrarse. No
compartas el archivo `.env` ni publiques sus claves.

## 4. Instalar o verificar los paquetes requeridos

Si los paquetes ya están declarados en `composer.json`, basta con
ejecutar `composer install`. Si todavía no están instalados, agrega los
que falten:

```bash
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require --dev darkaonline/l5-swagger
```

### Laravel Sanctum

En una instalación Laravel donde la API de Sanctum todavía no esté
preparada, ejecuta:

```bash
php artisan install:api
```

Este comando puede publicar la migración de tokens y configurar las
rutas API. Si el proyecto ya tiene Sanctum configurado y sus migraciones
publicadas, no es necesario repetirlo.

### Spatie Laravel Permission

Si las migraciones y configuración del paquete aún no están publicadas:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Comprueba que exista la migración que crea las tablas de roles y
permisos (`roles`, `permissions`, `model_has_roles`,
`model_has_permissions` y `role_has_permissions`).

### L5-Swagger

Si aún no se publicó su configuración:

```bash
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

La documentación de este proyecto utiliza atributos PHP de
`OpenApi\Attributes` (por ejemplo, `#[OA\Info(...)]`). El escáner de
L5-Swagger debe incluir la carpeta `app` para encontrar el controlador
base y los controladores API.

## 5. Ejecutar migraciones y seeders

Una vez configurada la conexión a la base de datos, ejecuta:

```bash
php artisan optimize:clear
php artisan migrate --seed
```

Las migraciones de la aplicación crean o amplían las siguientes
estructuras:

---

Migración Estructuras

---

`add_clinic_fields_to_users_table` Agrega teléfono, estado activo y
último acceso a `users`.

`create_patients_table` Pacientes.

`create_dentists_table` Odontólogos vinculados a usuarios.

`create_treatments_table` Tratamientos.

`create_appointments_table` Citas y relación cita-tratamiento
(`appointment_treatment`).

`create_clinical_records_table` Historias clínicas.

`create_payments_table` Pagos.

---

Además, deben ejecutarse las migraciones base de Laravel y las
publicadas por Sanctum y Spatie Permission.

El seeder `RolesAndPermissionsSeeder`, llamado desde `DatabaseSeeder`,
registra:

- Roles: `admin`, `receptionist` y `dentist`.
- Permisos por recurso y acción: `view`, `create`, `update` y
  `delete`.
- Un usuario administrador inicial, usando `SEED_ADMIN_EMAIL` y
  `SEED_ADMIN_PASSWORD`.

**No ejecutes `migrate:fresh` en una base de datos que contenga
información que quieras conservar.** Ese comando elimina todas las
tablas y vuelve a crearlas. Úsalo únicamente en desarrollo cuando puedas
perder todos los datos.

Si necesitas volver a ejecutar únicamente los roles, permisos y
administrador:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

El seeder no reemplaza la contraseña de un administrador que ya exista.
Si cambias `SEED_ADMIN_PASSWORD` después de haber creado ese usuario, la
contraseña existente no se actualizará automáticamente.

## 6. Levantar la API

Inicia el servidor local:

```bash
php artisan serve
```

Por defecto, la API estará disponible en:

```text
http://127.0.0.1:8000
```

Comprobación de salud de Laravel:

```text
GET http://127.0.0.1:8000/up
```

Prefijo de la API:

```text
/api/v1
```

## 7. Documentación Swagger

Genera la documentación:

```bash
php artisan l5-swagger:generate
```

Abre Swagger UI:

```text
http://127.0.0.1:8000/api/documentation
```

Para probar endpoints protegidos:

1.  Ejecuta `POST /api/v1/auth/login`.
2.  Envía el correo y la contraseña del administrador configurados antes
    del seeding.
3.  Copia el valor `token` de la respuesta.
4.  En Swagger, pulsa **Authorize**.
5.  Pega únicamente el token, sin anteponer la palabra `Bearer`.
6.  Ejecuta los endpoints protegidos.

Si Swagger indica que no encuentra `OA\Info`, verifica que L5-Swagger
esté escaneando `app` y que el archivo
`app/Http/Controllers/Controller.php` (que contiene `#[OA\Info(...)]`)
esté incluido en el análisis.

## 8. Autenticación y formato de solicitudes

El inicio de sesión es público:

```http
POST /api/v1/auth/login
Content-Type: application/json
Accept: application/json
```

Ejemplo de cuerpo:

```json
{
    "email": "admin@clinica.test",
    "password": "LA_CLAVE_CONFIGURADA",
    "device_name": "clinica-frontend"
}
```

Las rutas protegidas requieren el token de Sanctum:

```http
Authorization: Bearer TU_TOKEN
Accept: application/json
Content-Type: application/json
```

Al consumir la API desde Vue u otro frontend, envía
`Accept: application/json` y el encabezado `Authorization` en las
solicitudes autenticadas.

## 9. Endpoints disponibles

Todos los endpoints de negocio están bajo `/api/v1`.

### Autenticación

---

Método Endpoint Descripción Acceso

---

POST `/api/v1/auth/login` Iniciar sesión y Público
obtener token

GET `/api/v1/auth/me` Consultar Autenticado
usuario, roles y  
 permisos

POST `/api/v1/auth/logout` Revocar el token Autenticado
actual

POST `/api/v1/auth/logout-all` Revocar todos los Autenticado
tokens del  
 usuario

PUT `/api/v1/auth/password` Cambiar Autenticado
contraseña

---

### Recursos CRUD

Cada recurso dispone de `GET` para listar, `POST` para crear,
`GET /{id}` para consultar, `PUT /{id}` para actualizar y `DELETE /{id}`
para eliminar o aplicar la acción definida por el controlador.

Recurso Endpoint base

---

Usuarios `/api/v1/users`
Pacientes `/api/v1/patients`
Odontólogos `/api/v1/dentists`
Tratamientos `/api/v1/treatments`
Citas `/api/v1/appointments`
Historias clínicas `/api/v1/clinical-records`
Pagos `/api/v1/payments`

Endpoints adicionales:

---

Método Endpoint Descripción

---

PATCH `/api/v1/appointments/{id}/status` Cambiar el estado de
una cita

GET `/api/v1/roles` Consultar roles
disponibles (requiere
`users.view`)

---

Los listados admiten parámetros comunes como `search`, `page`,
`per_page` y `order=asc|desc`. Las citas también contemplan filtros como
`dentist_id`, `patient_id`, `status`, `date_from` y `date_to`, según la
implementación del controlador.

## 10. Roles y permisos

Los permisos siguen el formato `{recurso}.{acción}`.

---

Rol Accesos principales

---

`admin` Todos los permisos registrados.

`receptionist` CRUD de pacientes y citas; consulta
de odontólogos y tratamientos;
consulta, creación y actualización
de pagos.

`dentist` Consulta, creación y actualización
de pacientes; consulta de
odontólogos y tratamientos;
consulta y actualización de citas;
consulta, creación y actualización
de historias clínicas.

---

La API aplica permisos en las rutas. También incluye restricciones de
acceso a información propia del odontólogo en las operaciones
contempladas por los servicios de la aplicación.

## 11. Configuración del frontend y CORS

Si el frontend se ejecuta, por ejemplo, en Vue con Vite en
`http://localhost:5173`, configura:

```env
CORS_ALLOWED_ORIGINS=http://localhost:5173
```

Si el frontend utiliza otro host o puerto, reemplaza el origen por el
correspondiente. En producción configura únicamente los dominios
autorizados; no uses una lista abierta de orígenes.

Después de cambiar variables de entorno:

```bash
php artisan optimize:clear
```

## 12. Ejecutar pruebas

Ejecuta la suite de pruebas:

```bash
php artisan test
```

Para ejecutar únicamente las pruebas de la API clínica:

```bash
php artisan test --filter=ClinicApiTest
```

Las pruebas de esta funcionalidad cubren autenticación, permisos por
rol, restricciones de eliminación y validación de solapamiento de citas.
Revisa `phpunit.xml` para confirmar la configuración de base de datos de
pruebas antes de ejecutar la suite.

## 13. Comandos útiles

```bash
# Ver rutas registradas
php artisan route:list --path=api

# Limpiar cachés de Laravel
php artisan optimize:clear

# Ejecutar migraciones pendientes
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Ver estado de migraciones
php artisan migrate:status

# Regenerar documentación Swagger
php artisan l5-swagger:generate

# Ejecutar pruebas
php artisan test
```

## 14. Seguridad antes de producción

Antes de publicar la API:

- Configura `APP_ENV=production` y `APP_DEBUG=false`.
- Usa HTTPS.
- Configura `APP_URL` con el dominio real.
- Define `CORS_ALLOWED_ORIGINS` con los dominios reales y autorizados
  del frontend.
- Usa una contraseña segura para el administrador y cámbiala después
  del primer acceso.
- Mantén `APP_KEY` protegida y respáldala de forma segura. Es
  necesaria para descifrar información clínica.
- No subas `.env`, credenciales, tokens, claves privadas ni datos
  reales de pacientes a Git.
- Mantén actualizadas las dependencias y revisa los avisos de
  seguridad.
- Ejecuta `composer install --no-dev --optimize-autoloader` en el
  despliegue de producción si Swagger está declarado como dependencia
  de desarrollo.
- Deshabilita la generación automática de Swagger en producción
  (`L5_SWAGGER_GENERATE_ALWAYS=false`) y restringe o deshabilita el
  acceso público a la documentación según la política del proyecto.
- Configura respaldos periódicos y protegidos de la base de datos.

## 15. Archivos que no deben subirse a Git

Verifica que `.gitignore` excluya, como mínimo:

```gitignore
.env
.env.*
!.env.example
/vendor
/node_modules
/public/build
/storage/*.key
```

El repositorio sí debe incluir `composer.json`, `composer.lock`,
migraciones, seeders, controladores, modelos, configuración necesaria,
pruebas y este `README.md`, para que otra persona pueda instalar las
dependencias y levantar el proyecto.
