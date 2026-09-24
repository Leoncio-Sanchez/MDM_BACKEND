# FASE 8B — Cierre (Archivos)

**Fecha:** 2026-09-24  
**Rama:** master  
**Commits:** 8B.0 → 8B.5  

## Resumen

Se completó la FASE 8B: gestión de archivos digitales para Expedientes,
ITSE y Licencias de Funcionamiento. Arquitectura DRY con dominio compartido.

## Sub-gates cerrados

| Sub-gate | Contenido | Commit | Tests |
|----------|-----------|--------|-------|
| 8B.0 | Auditoría | `c1efd4f` | — |
| 8B.1 | Domain + Contracts | `ddcf72d` | +8 |
| 8B.2 | Application (4 UseCases DRY) | `9476399` | +12 |
| 8B.3 | Infrastructure (Storage + 3 repos) | `95eef1b` | +22 |
| 8B.4 | Presentation (3 Controllers + 12 rutas) | `b61842c` | +23 |
| 8B.6 | Cierre + reporte | `3861a92` | — |

## Endpoints implementados (12 totales)

| Módulo | Listar | Subir | Eliminar | Descargar |
|--------|:------:|:-----:|:--------:|:---------:|
| Expedientes | ✅ | ✅ | ✅ | ✅ |
| ITSE | ✅ | ✅ | ✅ | ✅ |
| Licencias | ✅ | ✅ | ✅ | ✅ |

Total: 12 endpoints HTTP.

## Arquitectura DRY

- **Domain compartido**: `ArchivoMetadata` + `ArchivoManagerInterface` + `ArchivoRepositoryInterface`.
- **Application DRY**: 4 UseCases compartidos (subir/listar/eliminar/descargar).
- **Infrastructure**: `AbstractEloquentArchivoRepository` + `LocalStorageArchivoManager`.
- **Presentation**: `AbstractArchivoController` + 3 controllers específicos.

## Validación

- ✅ **SHA-256 del contenido IDÉNTICO** byte a byte entre Django y Laravel (`98a4bdb6ec2013dfa4c4912ddb74641caf7252cab5195fdc0c9bf0e7ff1b5398`).
- ✅ Metadata JSON plana (10 keys) idéntica.
- ✅ `Content-Disposition: inline; filename="..."` replicado.
- ✅ Status 400 (no 422) en validación.
- ✅ StreamedResponse para descarga binaria.
- ✅ 70 tests de Archivos, 252 assertions.
- ✅ PHPStan Level 8: 0 errores.
- ✅ Pint: 0 errores.

## Hallazgos críticos

1. **Los 3 servicios de Django son idénticos** → DRY válido.
2. **`Content-Type` difiere levemente**: Django `text/plain`, Laravel `text/plain; charset=utf-8`.
   Aceptado como diferencia benigna (el navegador parsea ambos igual).
3. **UUID generado al azar**: distinto entre frameworks pero contenido idéntico.
4. **`listarPorParent` con `ORDER BY fecha_digitacion ASC`**: replicado.

## Deuda técnica documentada

- TD-017: `Content-Type` con `; charset=utf-8` en descargas Laravel. Diferencia
  benigna vs Django (`text/plain` sin charset).

## Próxima fase

FASE 9 — Seguridad (roles + permisos + auditoría).
