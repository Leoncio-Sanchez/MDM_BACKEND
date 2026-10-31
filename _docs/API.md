# API — Sistema LF-ITSE

Referencia de los endpoints del backend. La generan las rutas reales (`php artisan route:list --path=api`); si se
agrega o cambia una ruta, actualizar este documento. Con `APP_ENV=local` también está la documentación OpenAPI
interactiva en `/api/documentation`.

## Convenciones

- **Base**: `https://<dominio>/api` (local: `http://localhost:8001/api`).
- **Formato**: JSON. Enviar `Accept: application/json`.
- **Barra final**: el frontend usa las rutas con `/` al final, como en Django. Laravel acepta ambas formas.
- **Fechas**: `AAAA-MM-DD`; fechas con hora en ISO 8601 con zona `-05:00` (Lima).

## Sesión

1. `POST /auth/jwt/create/` con `{ "username", "password" }` → `{ "access", "refresh" }`.
2. Cada pedido lleva `Authorization: Bearer <access>`. El token de acceso dura 1 hora.
3. Al vencer, `POST /auth/jwt/refresh/` con `{ "refresh" }` entrega un token nuevo (el de renovación dura 7 días).
4. `GET /auth/users/me/` devuelve el usuario con su `role` y sus `permisos`.

Los ciudadanos inician sesión con `POST /publico/cuenta/ingresar/` (documento y clave) y reciben el mismo par de
tokens.

## Roles y accesos

Cada ruta exige un **rol** y, en el personal, una o más **casillas de acceso**:

| Rol | Quién |
|---|---|
| `ADMIN_MUNICIPALIDAD` | Personal de oficina (`is_staff`) |
| `INSPECTOR` | Inspector de campo que no es personal de oficina |
| `CIUDADANO` | Cuenta de ciudadano (`ciu_<documento>`) |

| Casilla | Se muestra en la tabla como |
|---|---|
| `expedientes` | Expedientes |
| `licencias` | Licencias |
| `itse` | ITSE |
| `jefe_itse` | Jefe de ITSE |
| `inspector` | Inspector |
| `admin` | Administrador |
| `reportes` | Reportes completos |

- "Expedientes o Licencias" significa que basta **una** de esas casillas.
- "Personal" = cualquier cuenta de oficina, sin casilla particular (por ejemplo, leer los catálogos).
- El **superusuario** pasa todas las casillas salvo Inspector, que es una función y no un privilegio.
- Qué casillas corresponden a cada puesto: [`PERFILES_Y_ACCESOS.md`](PERFILES_Y_ACCESOS.md).

## Respuestas de error

| Código | Cuándo | Cuerpo |
|---|---|---|
| `401` | Sin token o token vencido | `{ "message": "Unauthenticated." }` |
| `401` | Usuario o contraseña incorrectos, o token de renovación inválido | `{ "detail": "…" }` |
| `403` | El rol o las casillas no alcanzan | `{ "error": "No tiene permisos para realizar esta operación." }` |
| `404` | El registro no existe | `{ "error": "…" }` |
| `409` | Conflicto con el estado actual (duplicado, ya asignado, en uso…) | `{ "error": "…" }` |
| `422` | Regla de negocio | `{ "error": "…" }` |
| `422` | Validación de campos | `{ "message": "…", "errors": { "campo": ["…"] } }` |
| `429` | Demasiados intentos | `{ "detail": "…" }` en el login; `{ "error": "…" }` en lo público |

## Límites de intentos

| Endpoint | Límite por minuto |
|---|---|
| `POST /auth/jwt/create/` | 5 por usuario e IP; 20 por IP |
| `POST /publico/cuenta/*` | 5 por documento e IP; 20 por IP |
| `GET /publico/consulta/` | 10 por IP |

## Endpoints públicos (detalle)

**`GET /publico/consulta/`** — consulta un trámite sin sesión.
Parámetros: `tipo` (`expediente`, `licencia` o `itse`), `numero`, `documento` (DNI o RUC del titular o solicitante).
Devuelve estado, fechas, giros y, en expedientes, el avance de la inspección. Nunca datos personales.

**`POST /publico/cuenta/registro/`** y **`/restablecer/`** — crean la cuenta del ciudadano o cambian su clave.
Cuerpo: `documento`, `numero_expediente` (uno de sus expedientes, como prueba de que el documento es suyo),
`clave` y `clave_confirmation` (mínimo 8 caracteres, con letras y números).

**`GET /ciudadano/mis-tramites/`** — todo lo que está a nombre del documento del ciudadano: expedientes donde es
solicitante o representante, y licencias e ITSE donde es titular o conductor.

## Endpoints

### Sesión

| Método | Ruta | Acceso |
|---|---|---|
| `POST` | `/auth/jwt/create/` | Público |
| `POST` | `/auth/jwt/refresh/` | Público |
| `POST` | `/auth/jwt/verify/` | Público |
| `GET` | `/auth/users/me/` | Cualquier sesión |

### Público y ciudadanos

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/ciudadano/mis-tramites/` | Ciudadano |
| `GET` | `/publico/consulta/` | Público |
| `POST` | `/publico/cuenta/ingresar/` | Público |
| `POST` | `/publico/cuenta/registro/` | Público |
| `POST` | `/publico/cuenta/restablecer/` | Público |

### Expedientes

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/lf-itse/expedientes/` | Expedientes o Licencias o ITSE |
| `POST` | `/lf-itse/expedientes/` | Expedientes |
| `POST` | `/lf-itse/expedientes/archivos/{pk}/inactivar/` | Expedientes |
| `GET` | `/lf-itse/expedientes/archivos/{uuid}/descargar/` | Expedientes o Licencias o ITSE |
| `GET` | `/lf-itse/expedientes/buscar/` | Expedientes o Licencias o ITSE |
| `GET` | `/lf-itse/expedientes/consulta/` | Expedientes o Licencias o ITSE |
| `GET` | `/lf-itse/expedientes/pendientes/` | Expedientes o Licencias o ITSE o Reportes completos |
| `GET` | `/lf-itse/expedientes/{pk}/` | Expedientes o Licencias o ITSE |
| `PUT` | `/lf-itse/expedientes/{pk}/` | Expedientes |
| `POST` | `/lf-itse/expedientes/{pk}/ampliacion-plazo/` | Expedientes |
| `POST` | `/lf-itse/expedientes/{pk}/anular/` | Expedientes |
| `GET` | `/lf-itse/expedientes/{pk}/archivos/` | Expedientes o Licencias o ITSE |
| `POST` | `/lf-itse/expedientes/{pk}/archivos/` | Expedientes |
| `GET` | `/lf-itse/expedientes/{pk}/autorizacion-improcedente/` | Expedientes o Licencias o ITSE |
| `POST` | `/lf-itse/expedientes/{pk}/denegar-itse/` | ITSE |
| `POST` | `/lf-itse/expedientes/{pk}/denegar-licencia/` | Licencias |

### Personas

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/lf-itse/personas/` | Expedientes o Licencias o ITSE o Administrador |
| `POST` | `/lf-itse/personas/` | Expedientes o Licencias o ITSE o Administrador |
| `GET` | `/lf-itse/personas/buscar/` | Expedientes o Licencias o ITSE o Administrador |
| `GET` | `/lf-itse/personas/sexos/` | Expedientes o Licencias o ITSE o Administrador |
| `GET` | `/lf-itse/personas/{pk}/` | Expedientes o Licencias o ITSE o Administrador |
| `PUT` | `/lf-itse/personas/{pk}/` | Expedientes o Licencias o ITSE o Administrador |
| `DELETE` | `/lf-itse/personas/{pk}/` | Expedientes o Licencias o ITSE o Administrador |
| `GET` | `/lf-itse/personas/{pk}/documentos/` | Expedientes o Licencias o ITSE o Administrador |

### PIDE (RENIEC y SUNAT)

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/lf-itse/reniec/consultar/` | Expedientes o Licencias o ITSE o Administrador |
| `GET` | `/lf-itse/sunat/consultar/` | Expedientes o Licencias o ITSE o Administrador |

### Inspecciones

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/lf-itse/inspecciones/actas/{actaId}/` | ITSE o Jefe de ITSE |
| `POST` | `/lf-itse/inspecciones/actas/{actaId}/anular/` | Jefe de ITSE |
| `GET` | `/lf-itse/inspecciones/actas/{actaId}/fotos/{fotoId}/` | ITSE o Jefe de ITSE |
| `POST` | `/lf-itse/inspecciones/asignaciones/` | Jefe de ITSE |
| `PUT` | `/lf-itse/inspecciones/asignaciones/{pk}/` | Jefe de ITSE |
| `POST` | `/lf-itse/inspecciones/asignaciones/{pk}/reasignar/` | Jefe de ITSE |
| `GET` | `/lf-itse/inspecciones/expedientes/{expedienteId}/actas/` | ITSE o Jefe de ITSE |
| `GET` | `/lf-itse/inspecciones/expedientes/{expedienteId}/historial/` | Jefe de ITSE |
| `GET` | `/lf-itse/inspecciones/inspectores/` | Jefe de ITSE |
| `GET` | `/lf-itse/inspecciones/mi-bandeja/` | Inspector |
| `GET` | `/lf-itse/inspecciones/mi-bandeja/actas/{actaId}/` | Inspector |
| `GET` | `/lf-itse/inspecciones/mi-bandeja/actas/{actaId}/fotos/{fotoId}/` | Inspector |
| `POST` | `/lf-itse/inspecciones/mi-bandeja/{asignacionId}/acta/` | Inspector |
| `GET` | `/lf-itse/inspecciones/por-inspeccionar/` | Jefe de ITSE |

### Inspectores

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/lf-itse/inspectores/` | ITSE |
| `POST` | `/lf-itse/inspectores/` | Jefe de ITSE |
| `GET` | `/lf-itse/inspectores/buscar/` | ITSE |
| `GET` | `/lf-itse/inspectores/{pk}/` | ITSE |
| `PUT` | `/lf-itse/inspectores/{pk}/` | Jefe de ITSE |
| `DELETE` | `/lf-itse/inspectores/{pk}/` | Jefe de ITSE |

### Certificados ITSE

| Método | Ruta | Acceso |
|---|---|---|
| `POST` | `/lf-itse/itse/` | ITSE |
| `POST` | `/lf-itse/itse/archivos/{pk}/inactivar/` | ITSE |
| `GET` | `/lf-itse/itse/archivos/{uuid}/descargar/` | ITSE |
| `GET` | `/lf-itse/itse/buscar/` | ITSE |
| `GET` | `/lf-itse/itse/consulta/` | ITSE |
| `GET` | `/lf-itse/itse/expedientes/{expedienteId}/inspeccion/` | ITSE |
| `POST` | `/lf-itse/itse/inactivar/` | ITSE |
| `GET` | `/lf-itse/itse/por-renovar/` | ITSE o Reportes completos |
| `GET` | `/lf-itse/itse/verificar-expediente/` | ITSE |
| `POST` | `/lf-itse/itse/verificar-expediente/` | ITSE |
| `GET` | `/lf-itse/itse/{pk}/` | ITSE |
| `PUT` | `/lf-itse/itse/{pk}/` | ITSE |
| `GET` | `/lf-itse/itse/{pk}/archivos/` | ITSE |
| `POST` | `/lf-itse/itse/{pk}/archivos/` | ITSE |
| `GET` | `/lf-itse/itse/{pk}/estados/` | ITSE |
| `GET` | `/lf-itse/itse/{pk}/giros/` | ITSE |
| `GET` | `/lf-itse/itse/{pk}/inspectores/` | ITSE |
| `POST` | `/lf-itse/itse/{pk}/notificacion/` | ITSE |
| `PATCH` | `/lf-itse/itse/{pk}/notificacion/` | ITSE |

### Licencias de funcionamiento

| Método | Ruta | Acceso |
|---|---|---|
| `POST` | `/lf-itse/licencias-funcionamiento/` | Licencias |
| `POST` | `/lf-itse/licencias-funcionamiento/archivos/{pk}/inactivar/` | Licencias |
| `GET` | `/lf-itse/licencias-funcionamiento/archivos/{uuid}/descargar/` | Licencias |
| `GET` | `/lf-itse/licencias-funcionamiento/buscar/` | Licencias |
| `GET` | `/lf-itse/licencias-funcionamiento/consulta/` | Licencias |
| `POST` | `/lf-itse/licencias-funcionamiento/inactivar/` | Licencias |
| `GET` | `/lf-itse/licencias-funcionamiento/verificar-expediente/` | Licencias |
| `POST` | `/lf-itse/licencias-funcionamiento/verificar-expediente/` | Licencias |
| `PUT` | `/lf-itse/licencias-funcionamiento/{pk}/` | Licencias |
| `DELETE` | `/lf-itse/licencias-funcionamiento/{pk}/` | Licencias |
| `GET` | `/lf-itse/licencias-funcionamiento/{pk}/archivos/` | Licencias |
| `POST` | `/lf-itse/licencias-funcionamiento/{pk}/archivos/` | Licencias |
| `GET` | `/lf-itse/licencias-funcionamiento/{pk}/estados/` | Licencias |
| `GET` | `/lf-itse/licencias-funcionamiento/{pk}/giros/` | Licencias |
| `POST` | `/lf-itse/licencias-funcionamiento/{pk}/notificacion/` | Licencias |
| `PATCH` | `/lf-itse/licencias-funcionamiento/{pk}/notificacion/` | Licencias |

### Reportes

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/lf-itse/reportes/bajas/itse/` | ITSE o Reportes completos |
| `GET` | `/lf-itse/reportes/bajas/licencias/` | Licencias o Reportes completos |
| `GET` | `/lf-itse/reportes/improcedentes/itse/` | ITSE o Reportes completos |
| `GET` | `/lf-itse/reportes/improcedentes/licencias/` | Licencias o Reportes completos |
| `GET` | `/lf-itse/reportes/licencias/` | Licencias o Reportes completos |
| `GET` | `/lf-itse/reportes/licencias/pdf/` | Licencias o Reportes completos |

### Panel y configuración

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/lf-itse/configuracion/informes/` | Administrador |
| `PUT` | `/lf-itse/configuracion/informes/` | Administrador |
| `GET` | `/lf-itse/dashboard/resumen/` | Personal |
| `GET` | `/lf-itse/fecha-servidor/` | Cualquier sesión |

### Usuarios

| Método | Ruta | Acceso |
|---|---|---|
| `GET` | `/lf-itse/usuarios/` | Administrador |
| `POST` | `/lf-itse/usuarios/` | Administrador |
| `GET` | `/lf-itse/usuarios/menus/` | Administrador |
| `GET` | `/lf-itse/usuarios/{pk}/` | Administrador |
| `PUT` | `/lf-itse/usuarios/{pk}/` | Administrador |
| `DELETE` | `/lf-itse/usuarios/{pk}/` | Administrador |
| `PATCH` | `/lf-itse/usuarios/{pk}/cambiar-password/` | Administrador |
| `GET` | `/lf-itse/usuarios/{pk}/detalle/` | Administrador |

### Catálogos

Los ocho catálogos tienen la misma forma. Leerlos: **Personal** (los usan los formularios). Crear, editar o
borrar: **Administrador**.

| Catálogo | Ruta base | Extras |
|---|---|---|
| Estados | `/lf-itse/estados/` | `GET inactivos-itse/`, `GET inactivos-lf/` (motivos de baja) |
| Giros | `/lf-itse/giros/` | |
| Niveles de riesgo | `/lf-itse/niveles-riesgo/` | |
| Tipos de documento de identidad | `/lf-itse/tipos-documento-identidad/` | |
| Tipos de licencia | `/lf-itse/tipos-licencia/` | |
| Procedimientos TUPA | `/lf-itse/tipos-procedimiento-tupa/` | |
| Unidades orgánicas | `/lf-itse/unidades-organicas/` | |
| Zonificaciones | `/lf-itse/zonificaciones/` | |

Cada uno: `GET` (lista), `POST` (crear), `GET {id}/`, `PUT {id}/` y `DELETE {id}/`. Un registro en uso no se
borra (`409`).
