# Backend — Sistema LF-ITSE

API del sistema de **Licencias de Funcionamiento (LF)** e **Inspecciones Técnicas de Seguridad en Edificaciones
(ITSE)** de la Municipalidad Distrital de Morales. La consume el frontend
([MDM_Frontend](https://github.com/Leoncio-Sanchez/MDM_Frontend)).

Reemplaza al backend anterior en Django y conserva su base de datos: las tablas heredadas no se alteran y lo nuevo
vive en tablas propias.

## Stack

| Pieza | Versión |
|---|---|
| PHP | 8.3+ (el CI usa 8.4) |
| Laravel | 13 |
| PostgreSQL | 16 |
| Redis | caché y límites de intentos |
| JWT | compatible con los tokens de Django (`/auth/jwt/create/`) |
| Pest, PHPStan nivel 8, Pint | tests y calidad |

## Puesta en marcha (local)

Requiere Docker. No hace falta PHP instalado: se usa Laravel Sail.

```bash
cp .env.example .env            # completar JWT_SECRET y la conexión a la BD
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan usuarios:administrador admin --nombre="Nombres Apellidos"
```

| Servicio | Puerto local |
|---|---|
| API | http://localhost:8001/api |
| PostgreSQL | 5433 |
| Redis | 6380 |
| Documentación OpenAPI | http://localhost:8001/api/documentation (solo con `APP_ENV=local`) |

## Comandos

### Calidad (los mismos que corre el CI)

```bash
./vendor/bin/sail artisan test --compact                       # Pest
./vendor/bin/sail php ./vendor/bin/phpstan analyse --memory-limit=1G
./vendor/bin/sail php ./vendor/bin/pint <archivos>             # solo sobre los archivos que se tocan
```

### Administración de usuarios

| Comando | Para qué |
|---|---|
| `usuarios:administrador <usuario> --nombre="…"` | Crea un superusuario. Pide la contraseña sin mostrarla (mínimo 12 caracteres) |
| `usuarios:revisar-accesos` | Lista al personal activo que no tiene ningún acceso |
| `usuarios:prueba [--archivo=ruta]` | **Solo pruebas y capacitación** (no corre en producción): un usuario `prueba_*` por perfil |
| `usuarios:prueba --eliminar` | Borra los usuarios `prueba_*`; si alguno registró datos, lo desactiva |

## Configuración

Variables propias del sistema (el resto son las estándar de Laravel; ver `.env.example`):

| Variable | Para qué |
|---|---|
| `JWT_SECRET` | Firma de los tokens. **Larga, aleatoria y distinta en cada entorno** |
| `JWT_ACCESS_TOKEN_LIFETIME_HOURS` / `JWT_REFRESH_TOKEN_LIFETIME_DAYS` | Vida del token de acceso (1 h) y del de renovación (7 días) |
| `RENIEC_URL`, `RENIEC_NUDNIUSUARIO`, `RENIEC_NURUCUSUARIO`, `RENIEC_PASSWORD`, `RENIEC_TIMEOUT` | Consulta de DNI por la PIDE. Las credenciales son de la municipalidad: solo en el servidor |
| `SUNAT_URL` | Consulta de RUC por la PIDE |
| `MEDIA_DISK_ROOT` | Carpeta privada de adjuntos y fotos de actas (por defecto `storage/media`; nunca dentro de `public/`) |
| `ITSE_ACTA_OBLIGATORIA_DESDE` | Fecha (AAAA-MM-DD) desde la que el certificado ITSE exige un acta **Aprobada**. Vacío = a todos los expedientes |
| `CORS_ALLOWED_ORIGINS` | Dominios del frontend, separados por comas. Sin ella, en local se permite `http://localhost:5173` y en producción ninguno. `*` se ignora |
| `TRUSTED_PROXIES` | IP o subred del proxy de confianza, para detectar HTTPS y la IP real del cliente. Vacía = ninguno |
| `L5_SWAGGER_PUBLICO` | `true` publica `/api/documentation` fuera de `APP_ENV=local`. En producción debe quedar en `false` |

## Arquitectura

Monolito modular con arquitectura hexagonal. Cada módulo en `app/Modules/<Modulo>/` tiene:

```text
Domain/          entidades, value objects, excepciones y contratos (sin Laravel)
Application/     casos de uso (uno por acción), DTOs y mappers
Infrastructure/  repositorios (Eloquent o SQL), clientes externos y providers
Presentation/    controladores, form requests, resources y rutas
Tests/           Unit y Feature del módulo
```

Las reglas de dependencia y las convenciones están en [`ARCHITECTURE.md`](ARCHITECTURE.md). Las excepciones de
dominio se traducen a códigos HTTP en `bootstrap/app.php` (404 no encontrado, 409 conflicto, 422 dato inválido).

### Módulos

| Módulo | Qué resuelve |
|---|---|
| `Seguridad` | Inicio de sesión JWT, roles y permisos (middleware `rol:` y `permiso:`) |
| `Usuarios` | Cuentas del personal y sus casillas de acceso |
| `Personas` | Administrados (personas naturales y jurídicas) y sus documentos |
| `Pide` | Consulta de DNI (RENIEC) y RUC (SUNAT) |
| `Expedientes` | Mesa de partes: expedientes TUPA, plazos, ampliaciones, anulaciones y denegatorias |
| `Archivos` | Adjuntos de expedientes, licencias e ITSE |
| `Inspecciones` | Asignación de inspecciones, equipo de inspección y actas con fotos |
| `Inspectores` | Padrón de inspectores técnicos |
| `ITSE` | Certificados ITSE: emisión, inactivación, renovación y notificación |
| `Licencias` | Licencias de funcionamiento: emisión, inactivación y notificación |
| `ConsultaPublica` | Consulta de trámites sin sesión y cuentas de ciudadanos ("Mis trámites") |
| `Reportes` | Padrón de licencias, ITSE por vencer, bajas y denegatorias |
| `Dashboard` | Resumen del panel |
| `Configuracion` | Datos institucionales de los informes |
| Catálogos | `Estados`, `Giros`, `NivelRiesgo`, `TipoDocumentoIdentidad`, `TipoLicencia`, `TipoProcedimientoTupa`, `UnidadesOrganicas`, `Zonificacion` |

### Base de datos

- Las fechas se guardan en UTC, como en el sistema heredado, y la zona global de la aplicación no se cambia. Para "hoy" se
  usa `App\Shared\Domain\Calendario::hoy()`, y los días de un `TIMESTAMPTZ` se calculan con `America/Lima`.
- La migración `2026_01_01_000000_crear_esquema_produccion` recrea el esquema heredado de Django. Sirve para
  instalaciones nuevas y para los tests; sobre la base existente no cambia nada.
- Las migraciones posteriores agregan tablas nuevas (anulaciones, asignaciones, actas, acompañantes, cuentas de
  ciudadanos…) sin modificar las heredadas.

## Documentación

| Documento | Contenido |
|---|---|
| [`DEPLOY.md`](DEPLOY.md) | Instalación, migración, actualización y respaldos en producción con Docker |
| [`_docs/PERFILES_Y_ACCESOS.md`](_docs/PERFILES_Y_ACCESOS.md) | Qué ve y hace cada perfil, y qué casillas marcar al crear cuentas |
| [`_docs/FLUJO_DEL_TRAMITE.md`](_docs/FLUJO_DEL_TRAMITE.md) | El trámite de principio a fin: expediente, inspección, ITSE, licencia y ciudadano |
| [`_docs/API.md`](_docs/API.md) | Endpoints agrupados por módulo, con el acceso que exige cada uno |
| [`ARCHITECTURE.md`](ARCHITECTURE.md) | Reglas de la arquitectura hexagonal |
| [`_docs/README.md`](_docs/README.md) | Índice de toda la carpeta `_docs`, incluidos los informes históricos |

## Integración continua

`.github/workflows` corre en cada push y PR: instala dependencias, PHPStan nivel 8 y la suite completa contra
PostgreSQL 16.
