# FASE 7B — Cierre

**Fecha:** 2026-09-23
**Rama:** master
**Commits:** 7B.2 → 7B.8

## Resumen

Se completó la FASE 7B de Expedientes (SQL crudo + ORM + Resources + Controller).

## Sub-gates cerrados

| Sub-gate | Contenido | Commit | Tests |
|----------|-----------|--------|-------|
| 7B.2 | CalculadoraDiasHabilesService::diferenciaDiasHabiles() | e9ab4fc | +8 |
| 7B.3 | 3 UseCases + 2 DTOs + contrato ampliado | ce0e339 | +19 |
| 7B.4 | Repositorio con 2 SQL crudos + ORM | 6d3486c | +2 |
| 7B.5 | 2 Resources (11 + 27 keys) | 59f3657 | +5 |
| 7B.6 | Controller + 3 rutas | 9773688 | +10 |
| 7B.7 | Diff con datos reales vs Django | a01b7ff | +3 |
| 7B.8 | Cierre + reporte | HEAD | — |

## Endpoints implementados

| Endpoint | Keys | Fuente SQL |
|----------|------|------------|
| GET /expedientes/pendientes/ | 11 | SQL crudo (55 líneas) |
| GET /expedientes/buscar/ | 27 | SQL crudo (85 líneas) + cálculo en UseCase |
| GET /expedientes/consulta/ | 19 | ORM (Eloquent) |

## Hallazgos críticos

1. **27 keys en buscar, no 26**: Django anexa `dias_habiles_restantes` en el
   servicio (no en el SQL). Requirió exponerlo en `ExpedienteBusquedaResource`.
2. **Formato ISO-8601 UTC con `Z`**: Django serializa datetime con
   `DjangoJSONEncoder` (`2026-09-20T15:30:00Z`); Laravel devolvía string PDO.
   Resuelto con `formatUtcIso8601()` en ambos Resources.
3. **`consultaRapida()` eliminado**: era un artefacto del docstring de Django,
   no un endpoint real. Eliminado del contrato.

## Validación

- ✅ Diff byte-a-byte contra Django con datos reales: 3/3 endpoints idénticos.
- ✅ 5 filtros adicionales verificados con datos reales (`ID`, `FECHA_RECEPCION`, `FECHA_VENCIMIENTO`, `NOMBRE_SOLICITANTE`, `RUC_SOLICITANTE`).
- ✅ 117 tests de Expedientes, 513 assertions.
- ✅ PHPStan Level 8: 0 errores en Expedientes.
- ✅ Pint: 0 errores de estilo.

## Deuda técnica documentada

- TD-014: Serialización de datetimes UTC con sufijo `Z` en consultas SQL crudas mediante `formatUtcIso8601()` en Resources.

## Próxima fase

FASE 7C — Workflows (ampliación de plazo, denegar licencia, denegar ITSE, autorizaciones improcedentes).
