# Perfiles y accesos — Sistema LF-ITSE

Municipalidad Distrital de Morales · Gerencia de Desarrollo Económico

Este documento explica qué puede hacer cada persona en el sistema y qué casillas marcar al crear su cuenta en
**Administración → Usuarios y Permisos**.

## Cómo funcionan los accesos

Cada cuenta tiene un **perfil** con casillas de acceso. Se pueden combinar: la persona ve la suma de lo que tiene
marcado. Los cambios aplican de inmediato, sin que la persona tenga que volver a iniciar sesión.

| Casilla | Qué habilita |
|---|---|
| **Expedientes** | Mesa de partes: registrar, editar, ampliar plazo y anular expedientes TUPA; adjuntar archivos |
| **Licencias** | Emitir, inactivar y **denegar** licencias de funcionamiento |
| **ITSE** | Emitir (con acta Aprobada), inactivar, renovar y **denegar** certificados ITSE; ver actas e inspectores |
| **Jefe de ITSE** | Asignar inspecciones, elegir acompañantes, reprogramar, reasignar y anular actas. Requiere también **ITSE** |
| **Inspector** | Bandeja de inspecciones en el celular y registro del acta con fotos |
| **Administrador** | Usuarios y permisos, catálogos (giros, zonificaciones, TUPA, niveles de riesgo, etc.) y configuración |
| **Reportes completos** | Ver y descargar todos los reportes, sin modificar nada (Subgerente, Auditor, OCI) |

Además:

- **Personal / no personal**: el personal de oficina entra al panel administrativo. Un inspector de campo que *no* es
  personal solo ve su bandeja de inspecciones.
- **Superusuario**: tiene todos los accesos aunque no estén marcados (salvo Inspector, que es una función y no un
  privilegio). Conviene que haya uno o dos, no más.
- Una cuenta sin ninguna casilla solo ve el dashboard vacío. El comando `php artisan usuarios:revisar-accesos`
  lista estas cuentas.

## Perfiles sugeridos

| Puesto | Casillas | Pantallas que verá |
|---|---|---|
| **Mesa de partes** | Expedientes | Expedientes TUPA · Personas · Reportes |
| **Oficina de licencias** | Licencias | Licencias de funcionamiento · Expedientes (consulta) · Personas · Reportes |
| **Oficina de ITSE** | ITSE | Inspecciones ITSE · ITSE por renovar · Inspectores · Expedientes (consulta) · Personas · Reportes |
| **Jefatura de ITSE** | ITSE + Jefe de ITSE | Lo de la oficina de ITSE + Asignar inspecciones |
| **Inspector de campo** | Inspector (sin marcar "personal") | Mis inspecciones · Registrar acta |
| **Personal que también inspecciona** | ITSE + Inspector | Lo de la oficina de ITSE + Mis inspecciones |
| **Administrador del sistema (TI)** | Administrador | Usuarios y permisos · Catálogos · Configuración · Personas |
| **Subgerente / Auditor / OCI** | Reportes completos | Reportes (solo lectura) |

"Expedientes (consulta)" significa que ven la lista y los archivos del expediente, y registran la denegatoria de lo
que les toca, pero no crean, editan ni anulan expedientes, ni suben o dan de baja archivos.

## Pantallas por acceso

Rutas del sistema (después de `https://<dominio>`):

### Sin sesión
| Ruta | Pantalla |
|---|---|
| `/login` | Inicio de sesión |
| `/consulta` | Consulta pública del trámite por número de expediente (también con sesión) |

### Personal de oficina
Todos entran a `/admin` (dashboard). Cada tarjeta del dashboard aparece solo si tiene el acceso correspondiente.

| Ruta | Pantalla | Accesos que la habilitan |
|---|---|---|
| `/admin/expedientes` | Expedientes TUPA | Expedientes (completo) · Licencias o ITSE (consulta) |
| `/admin/personas` | Personas / administrados | Expedientes, Licencias, ITSE o Administrador |
| `/admin/licencias-funcionamiento` | Licencias de funcionamiento | Licencias |
| `/admin/itse` | Inspecciones ITSE | ITSE |
| `/admin/itse/por-renovar` | ITSE por renovar | ITSE |
| `/admin/inspectores` | Inspectores técnicos | ITSE |
| `/admin/itse/asignaciones` | Asignar inspecciones | Jefe de ITSE |
| `/admin/reportes` | Reportes | Expedientes, Licencias, ITSE o Reportes completos |
| `/admin/usuarios` | Usuarios y permisos | Administrador |
| `/admin/configuracion` | Configuración | Administrador |
| `/admin/giros`, `/admin/zonificaciones`, `/admin/niveles-riesgo`, `/admin/tipos-procedimiento-tupa`, `/admin/tipos-licencia`, `/admin/tipos-documento-identidad`, `/admin/unidades-organicas`, `/admin/estados` | Catálogos maestros | Administrador |

### Inspectores
| Ruta | Pantalla | Quién |
|---|---|---|
| `/inspector` | Mis inspecciones: las que tiene a cargo y las que acompaña | Inspector |
| `/inspector/acta/<asignación>` | Registrar acta con fotos (solo el inspector responsable) | Inspector |

### Ciudadanos (administrados)
| Ruta | Pantalla |
|---|---|
| `/ciudadano/ingresar` | Ingresar con DNI/RUC y contraseña, crear la cuenta u «Olvidé mi contraseña» |
| `/ciudadano` | **Mis trámites**: sus expedientes, licencias y certificados ITSE, con el avance de la inspección |

- **Crear la cuenta**: el ciudadano lo hace solo, con su DNI/RUC y el número de uno de sus expedientes (está en su
  cargo de recepción) como prueba de que el documento es suyo. No necesita correo ni que la municipalidad haga nada.
- **Olvidó su contraseña**: la restablece igual, con su DNI/RUC y el número de uno de sus expedientes.
- **Qué ve**: todo lo que está a nombre de su documento (como solicitante, representante, titular o conductor). Nunca
  datos de otras personas, ni el nombre del inspector, ni el detalle o las fotos del acta.
- **Avance de la inspección**: pendiente de programar, programada para tal fecha, observada (con el plazo para
  subsanar), aprobada o desaprobada. También aparece en la consulta pública sin cuenta.
- Los intentos están limitados (5 por minuto por documento) para que nadie pruebe expedientes o contraseñas en masa.
- Su usuario interno es `ciu_<documento>`; no es personal ni tiene accesos del panel.

Sin cuenta, cualquiera puede consultar un trámite en `/consulta` con el número y el DNI/RUC del titular.

El servidor aplica las mismas reglas en cada pedido: aunque alguien escriba una ruta a mano, sin el acceso recibe
"No tiene permisos".

## Reglas importantes del flujo ITSE

- **Denegatorias**: la de ITSE la registra quien tiene ITSE; la de licencia, quien tiene Licencias. No se repiten ni
  se deshacen.
- **Certificado ITSE**: solo se emite con un acta de inspección **Aprobada** vigente (desde la fecha configurada en
  `ITSE_ACTA_OBLIGATORIA_DESDE`).
- **Equipo de inspección**: un responsable (registra y firma el acta) y hasta 3 acompañantes. Quitar a alguien del
  equipo no lo borra: queda la constancia.

## Comandos para el administrador de TI

| Comando | Para qué |
|---|---|
| `php artisan usuarios:administrador <usuario> --nombre="Nombres Apellidos"` | Crea un superusuario; pide la contraseña sin mostrarla (mínimo 12 caracteres). Úselo para el primer administrador de una instalación nueva |
| `php artisan usuarios:revisar-accesos` | Lista al personal activo que no tiene ningún acceso |
| `php artisan usuarios:prueba [--archivo=ruta]` | **Solo pruebas y capacitación** (no corre en producción): crea un usuario `prueba_*` por cada perfil sugerido, con una contraseña nueva |
| `php artisan usuarios:prueba --eliminar` | Borra los usuarios `prueba_*`; si alguno registró datos, lo desactiva (nada se borra) |
