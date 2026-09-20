
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
