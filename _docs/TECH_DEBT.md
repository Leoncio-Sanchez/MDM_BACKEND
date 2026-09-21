
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
