
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
