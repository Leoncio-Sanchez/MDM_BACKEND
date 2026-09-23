
## TD-004 — Resources con acceso a DB

- **Estado:** Activo
- **Impacto:** Medio (violación de Clean Architecture)
- **Descripción:** `LicenciaListResource` y `LicenciaResource` hacen
  consultas `DB::table()` directas para replicar los 34 campos del
  serializer Django.
- **Ubicación:** `app/Modules/Licencias/Presentation/Resources/*.php`
- **Plan de resolución:** Refactorizar para que el UseCase devuelva un
  DTO completo con las 34 keys, y el Resource solo mapee.
- **Responsable:** Equipo backend
- **Prioridad:** P1

## TD-005 — Microsegundos en fecha_digitacion

- **Estado:** Activo
- **Impacto:** Bajo (Vue no consume el campo)
- **Descripción:** Django preserva microsegundos (`067525`), Laravel/Eloquent los trunca (`000000`) al leer de PostgreSQL con cast default.
- **Ubicación:** `ZonificacionResource.php`, `LicenciaResource.php`
- **Plan de resolución:** Investigar `protected $dateFormat` de Eloquent o usar `DB::raw` para preservar precisión.
- **Prioridad:** P2

## TD-006 — Frontend no envía `tipo_persona` en TiposDocumentoIdentidad

- **Estado:** Activo
- **Impacto:** Alto (página `/admin/tipos-documento-identidad` está rota en frontend)
- **Descripción:** Vue llama a `/api/lf-itse/tipos-documento-identidad/` sin el query param `?tipo_persona=N|J`. Django (y Laravel que replica el contrato) devuelve 400 Bad Request.
- **Ubicación:** `frontend-reversed-pro_vue/src/infrastructure/repositories/TiposDocumentoIdentidadRepository.impl.ts`
- **Plan de resolución:** Añadir `tipo_persona` al método `getAll()` del repositorio de Vue. Actualizar los tests de la página.
- **Prioridad:** P1 (ticket separado post-migración backend)

## TD-007 — Tests de BuscarPersonasUseCase skipped en SQLite

- **Estado:** Activo
- **Impacto:** Bajo (SQL verificado con curl contra Django)
- **Descripción:** 2 tests unitarios del `BuscarPersonasUseCase` están
  skipped porque usan SQL crudo con `STRING_AGG` y casts `::text` que
  no son compatibles con SQLite in-memory. La verificación se hizo
  manualmente con `curl` contra Django + Laravel (diff ✅ IDÉNTICO).
- **Ubicación:** `app/Modules/Personas/Tests/Unit/Application/BuscarPersonasUseCaseTest.php`
- **Plan de resolución:** Convertir a test de integración con conexión
  PostgreSQL real (usar `RefreshDatabase` con `DB_CONNECTION=pgsql`
  apuntando a una DB de test separada).
- **Prioridad:** P3

## TD-008 — Archivos transversales (ITSE, Licencias, Expedientes, Personas)

- **Estado:** Activo
- **Impacto:** Alto (módulo Archivos no migrado)
- **Descripción:** Los endpoints de upload/download/detail de archivos
  existen en 4 módulos (itse_archivos, licencias_funcionamiento_archivos,
  expedientes_archivos, personas_documentos) con lógica duplicada.
- **Plan de resolución:** Crear módulo `app/Modules/Archivos/` en FASE 9
  con un `ArchivoService` centralizado. Migrar los 4 módulos a la vez.
- **Prioridad:** P1 (FASE 9)

## TD-009 — Django no soporta GET /itse/{pk}/

- **Estado:** Activo (resuelto en Laravel)
- **Impacto:** Alto en Django legacy (frontend roto con Django)
- **Descripción:** `ItseUpdateView` en Django solo implementa `PUT`.
  El frontend Vue llama `container.itse.getById(id)` → `GET /itse/{id}/`
  → Django devuelve 405. Laravel sí implementa GET y devuelve el ITSE.

  **Decisión:** Laravel mantiene GET (no replica el bug).
  Justificación: el frontend ya está roto con Django y funcionará
  con Laravel. Replicar el bug sería condenar el frontend a seguir
  roto en el nuevo backend.

- **Ubicación Django:** `app_lf_itse/views.py:1717` (ItseUpdateView)
- **Plan de resolución:** Al apagar Django, este TD queda inactivo
  automáticamente. Si se requiere arreglar Django antes del apagado,
  añadir método `def get(self, request, pk)` al view.
- **Prioridad:** P2 (no bloquea la migración)
- **Fecha:** 20/09/2026
- **Aprobado por:** Stakeholder (Leo)

## TD-010 — ItseInspectoresView siempre retorna []

- **Estado:** Activo (feature no implementada en Django)
- **Impacto:** Bajo (frontend no usa este endpoint actualmente)
- **Descripción:** `ItseInspectoresView` en Django siempre devuelve `[]`
  aunque la tabla `itse_inspectores` existe con schema completo
  (`FK a itse, inspector_id, usuario_id`). La feature nunca se implementó.

  **Decisión:** Laravel retorna `[]` (replica el comportamiento de Django).
  NO se implementa la query real para preservar el contrato.

- **Ubicación Django:** `app_lf_itse/views.py` (ItseInspectoresView)
- **Plan de resolución:** Al apagar Django, si la feature es requerida,
  implementar en Laravel (modelo ya tiene el schema). Ticket separado.
- **Prioridad:** P3
- **Fecha:** 21/09/2026
- **Aprobado por:** Stakeholder (Leo)

## TD-011 — Django no soporta GET /expedientes/

- **Estado:** Activo (resuelto en Laravel)
- **Impacto:** Alto en Django legacy (frontend roto con Django)
- **Descripción:** `ExpedienteCreateView` en Django solo implementa `POST`. El
  frontend Vue llama `container.expedientes.getAll()` → `GET /expedientes/`
  → Django devuelve 405 Method Not Allowed. Laravel implementa GET → devuelve lista.

  **Decisión:** Laravel mantiene GET. NO replicar el bug. Mismo criterio
  que TD-009 (ITSE).

- **Ubicación Django:** `app_lf_itse/views.py:149` (ExpedienteCreateView)
- **Plan de resolución:** Al apagar Django, este TD queda inactivo
  automáticamente. Si se requiere arreglar Django antes del apagado,
  añadir método `def get(self, request)` al view.
- **Prioridad:** P2
- **Fecha:** 21/09/2026
- **Aprobado por:** Leo (Stakeholder)

## TD-012 — Django no soporta GET /expedientes/{pk}/

- **Estado:** Activo (resuelto en Laravel)
- **Impacto:** Alto en Django legacy (frontend roto con Django)
- **Descripción:** `ExpedienteUpdateView` en Django solo implementa `PUT` y `DELETE`.
  El frontend Vue llama `container.expedientes.getById(id)` → `GET /expedientes/{id}/`
  → Django devuelve 405 Method Not Allowed. Laravel implementa GET y devuelve el detalle.

  **Decisión:** Laravel mantiene GET. NO replicar el bug.

- **Ubicación Django:** `app_lf_itse/views.py:186` (ExpedienteUpdateView)
- **Plan de resolución:** Al apagar Django, este TD queda inactivo
  automáticamente. Si se requiere arreglar Django antes del apagado,
  añadir método `def get(self, request, pk)` al view.
- **Prioridad:** P2
- **Fecha:** 21/09/2026
- **Aprobado por:** Leo (Stakeholder)

## TD-013 — Manejo de microsegundos y persistencia con timezone en fecha_recepcion y datetimes de Expedientes

- **Estado:** Resuelto en Laravel (alineado 1:1 con Django DRF)
- **Impacto:** Bajo (consistencia de contrato y serialización ISO-8601)
- **Descripción:** 
  1. En Django REST Framework, la serialización de `DateTimeField` (con `isoformat()`) omite la fracción de microsegundos si es `.000000` (e.g. `2025-02-03T10:00:00-05:00`), pero la incluye si `microseconds > 0`.
  2. En Laravel Resources (`ExpedienteResource` y `ExpedienteListResource`), se replica exactamente esta lógica:
     `((int) $this->fechaRecepcion->format('u')) > 0 ? 'Y-m-d\TH:i:s.uP' : 'Y-m-d\TH:i:sP'`.
  3. En persistencia PostgreSQL (`timestamp with time zone`), Eloquent por defecto formatea fechas usando `Y-m-d H:i:s` sin offset de zona horaria, lo que provocaría discrepancias horarias en conexiones UTC. Se definió `getDateFormat(): string { return 'Y-m-d H:i:sP'; }` en los modelos Eloquent de Expedientes para preservar el offset explícito (`-05:00` en `America/Lima`) al persistir en PostgreSQL, garantizando simetría total de datos entre Django y Laravel.
- **Ubicación:** 
  - `app/Modules/Expedientes/Presentation/Resources/ExpedienteResource.php`
  - `app/Modules/Expedientes/Presentation/Resources/ExpedienteListResource.php`
  - `app/Modules/Expedientes/Infrastructure/Persistence/Models/ExpedienteModel.php`
- **Plan de resolución:** El comportamiento está completamente armonizado. Al retirar Django, se mantendrá este estándar para interoperabilidad con el frontend Vue.
- **Prioridad:** P2
- **Fecha:** 23/09/2026
- **Aprobado por:** Leo (Stakeholder)

## TD-014 — Formato ISO-8601 UTC ('Z') en endpoints de lectura con SQL crudo (Expedientes 7B)

- **Estado:** Resuelto en Laravel (alineado 1:1 con Django DRF)
- **Impacto:** Alto (compatibilidad de parsing de fechas en frontend JS / `new Date()`)
- **Descripción:** 
  1. En Django, las vistas `ExpedientesPendientesView` y `ExpedientesBuscarView` retornan diccionarios obtenidos directamente de `cursor.fetchall()`. Los campos de fecha y hora (`fecha_recepcion`, `fecha_digitacion`, etc.) son instancias de Python `datetime` en zona horaria UTC (`<UTC>`), las cuales son serializadas por `DjangoJSONEncoder` como ISO-8601 UTC con sufijo `Z` (ej. `2026-09-20T15:30:00Z`).
  2. En Laravel, las consultas crudas con `DB::select()` devuelven cadenas crudas del driver PDO PostgreSQL con el formato nativo (ej. `2026-09-20 15:30:00+00`).
  3. Para lograr paridad byte a byte, `ExpedientePendienteResource` y `ExpedienteBusquedaResource` implementan `formatUtcIso8601()`, parseando el valor a `DateTimeImmutable` en zona horaria UTC y serializándolo con sufijo `Z` (y milisegundos si aplican).
- **Ubicación:**
  - `app/Modules/Expedientes/Presentation/Resources/ExpedientePendienteResource.php`
  - `app/Modules/Expedientes/Presentation/Resources/ExpedienteBusquedaResource.php`
- **Plan de resolución:** El comportamiento está completamente armonizado. Al retirar Django, se mantendrá este estándar para interoperabilidad con el frontend Vue.
- **Prioridad:** P2
- **Fecha:** 23/09/2026
- **Aprobado por:** Leo (Stakeholder)
