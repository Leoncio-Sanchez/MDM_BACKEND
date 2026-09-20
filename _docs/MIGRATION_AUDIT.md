# Auditoría de Migración Django → Laravel

**Fecha:** 2026-09-20
**Estado:** En curso
**Progreso:** 0/14 módulos migrados

## 1. Inventario General

| Carpeta | Tamaño | Archivos Python | Destino |
|---|---|---|---|
| `app_lf_itse/` | 1.5M | 32 | `app/Modules/` |
| `app_main/` | 156K | 14 | `app/Modules/` |
| `backend_api/` | 88K | 6 | `config/` |
| `media/` | 13M | — | Django read-only |
| `node_modules/` | 6.2M | — | ❌ ELIMINAR |
| `staticfiles/` | 3.9M | — | ❌ REGENERAR |

**Total líneas Python:** 10,935

## 2. Módulos Identificados (bounded contexts)

| Módulo | Archivos Django | Responsabilidad | Prioridad |
|---|---|---|---|
| Seguridad | `backend_api/serializers.py`, `app_main/models.py`, `services/usuario.py` | Auth y roles | 🔴 Crítica |
| Estados | `app_lf_itse/models.py`, `services/estado.py` | Maestros de estado | ✅ Migrado |
| TipoLicencia | `services/tipo_licencia.py` | Maestros | 🟢 Baja |
| Giros | `services/giro.py` | Maestros | 🟢 Baja |
| NivelRiesgo | `services/nivel_riesgo.py` | Maestros | 🟢 Baja |
| Zonificacion | `services/zonificacion.py` | Maestros | 🟢 Baja |
| TipoDocumentoIdentidad | `services/tipo_documento_identidad.py` | Maestros | 🟢 Baja |
| TipoProcedimientoTupa | `services/tipo_procedimiento_tupa.py` | Maestros | 🟢 Baja |
| Personas | `services/persona.py` | Gestión de ciudadanos | 🟡 Media |
| Interoperabilidad | `services/reniec.py`, `services/sunat.py` | Consultas PIDE | 🟠 Alta |
| Licencias | `services/licencia_funcionamiento.py`, `autorizacion_improcedente.py` | Core negocio | 🔴 Alta |
| ITSE | `services/itse.py` | Core negocio | 🔴 Alta |
| Expedientes | `services/expediente.py` | Flujo trámites | 🔴 Alta |
| Archivos (Documental) | `*_archivo.py` (expediente, itse, licencia) | Transversal | 🟡 Media |

## 3. Modelos a Migrar

| Modelo Django | Tabla Neon | FKs | Módulo |
|---|---|---|---|
| LicenciaFuncionamiento | `licencias_funcionamiento` | 7 | Licencias |
| Expediente | `expedientes` | 4+ | Expedientes |
| Persona | `personas` | 1 | Personas |
| TipoLicencia | `tipos_licencia` | 0 | TipoLicencia |
| Feriados (Anual/Recurrente) | *(No listadas)* | 0 | Utils/Transversal |

## 4. Endpoints a Migrar

**Total:** Decenas de rutas custom en `urls.py`

| Ruta Django | Método | Módulo destino | Prioridad |
|---|---|---|---|
| `/api/lf-itse/licencias-funcionamiento/` | POST/GET/PUT/DELETE | Licencias | 🟠 Alta |
| `/api/lf-itse/expedientes/` | POST/GET/PUT/DELETE | Expedientes | 🟠 Alta |
| `/api/lf-itse/itse/` | POST/GET/PUT/DELETE | ITSE | 🟠 Alta |

## 5. Dependencias Externas

| Servicio | Config | Módulo Laravel |
|---|---|---|
| RENIEC (DNI) | `RENIEC_NUDNIUSUARIO`, `RENIEC_TIMEOUT` | Interoperabilidad |
| SUNAT (RUC) | *(Asumido igual que Reniec)* | Interoperabilidad |
| PostgreSQL Neon | `DATABASE_URL` | `config/database.php` |

## 6. Configuración a Migrar (`backend_api/` → `config/`)

| Aspecto | Django | Laravel equivalente |
|---|---|---|
| DB | PostgreSQL Neon | `config/database.php` |
| Auth | JWT (Djoser) | `config/auth.php` + `tymon/jwt-auth` |
| CORS | `django-cors-headers` | `config/cors.php` |
| Media | `MEDIA_ROOT`, `MEDIA_URL` | `storage/app/public/` |
| Static | `STATIC_ROOT` | Vite output (`public/`) |
| PIDE | Settings custom | `config/services.php` |

## 7. Bugs Pendientes (NO OLVIDAR)

1. **Prefijo de ruta inconsistente** — Laravel usa `api`, Django usa `api/lf-itse`.
2. **JWT auth inconsistente** — `/tipos-licencia/` funciona, `/buscar/` da 500.
3. **`auth.php` modificado** — rompe Licencias, hay que aislar el cambio.

## 8. Decisiones Estratégicas (Aprobadas)

1. **`media/` (13M, data real):** Opción B. Se deja en Django en modo read-only durante la coexistencia para evitar ruptura de enlaces. Se migrará en la fase final de cierre (Deuda Técnica documentada).
2. **`app_main/`:** Opción C. Ignorado por contener boilerplate irrelevante (CRUD de Products genéricos). No se migrará salvo dependencias encontradas a futuro.
3. **`node_modules/`, `staticfiles/` y basura:** Movidos a `_archive/django_legacy_junk/` por seguridad. No se borrarán de momento pero salen del alcance de la migración.
4. **Orden de Migración:** Prioridad basada en dependencias (Maestros → Personas → Licencias → ITSE → Expedientes → Interoperabilidad → Archivos → Seguridad → Cierre final).

## 9. Roadmap Sugerido

| Fase | Módulos | Duración estimada |
|---|---|---|
| 3.3 | Licencias | 3-5 días |
| 4 | Expedientes | 4-6 días |
| 5 | ITSE | 3-5 días |
| 6 | Personas | 2-3 días |
| 7 | Interoperabilidad (PIDE) | 3-4 días |
| 8 | Seguridad (roles/permisos) | 2-3 días |
| 9 | Auditoría / Utils | 2-3 días |
| 10 | Documental / Archivos | 2-3 días |

**Total estimado: 6-8 semanas.**
