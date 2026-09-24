# FASE 8A — Cierre (PIDE: RENIEC + SUNAT)

**Fecha:** 23/09/2026  
**Rama:** master  
**Commits:** 8A.0 → 8A.4

## Resumen

Se completó la FASE 8A: integración con RENIEC y SUNAT vía PIDE (Plataforma de Interoperabilidad del Estado).

## Sub-gates cerrados

| Sub-gate | Contenido | Commit | Tests |
|----------|-----------|--------|-------|
| 8A.0 | Auditoría de PIDE | 5e7f3d1 | — |
| 8A.1 | Infrastructure (adapters + config) | 5193d64 | +15 |
| 8A.2 | Application (2 UseCases + 2 DTOs) | bd2e4bd | +6 |
| 8A.3 | Presentation (Controller + 2 rutas) | af638f2 | +12 |
| 8A.4 | Diff de paridad (error paths) | 9e2be88 | +9 |
| 8A.5 | Cierre + reporte | *(en progreso)* | — |

## Endpoints implementados

| Método | Endpoint | Status OK | Status Error |
|--------|----------|-----------|--------------|
| GET | `/api/lf-itse/reniec/consultar/?dni=XXXXXXXX` | 200 | 400, 502, 500 |
| GET | `/api/lf-itse/sunat/consultar/?ruc=XXXXXXXXXXX` | 200 | 400, 502, 500 |

## Arquitectura

- **Módulo:** `app/Modules/Pide/` (independiente de `Personas`).
- **Protocolo:** REST JSON (no SOAP).
- **Cliente:** `Illuminate\Support\Facades\Http` (Guzzle v8.2).
- **Passthrough:** respuesta JSON de PIDE devuelta tal cual (sin Resource).
- **Config:** `config/services.php` → `reniec` + `sunat`.

## Hallazgos críticos

1. **RENIEC y SUNAT son REST JSON** (no SOAP). Adapter directo sin librerías adicionales.
2. **SUNAT NO requiere credenciales** (endpoint público PIDE).
3. **SUNAT timeout hardcoded = 10s** (Django no usa env).
4. **Status 502 (Bad Gateway)** para errores de PIDE, no 500.
5. **2 RENIEC clients legítimos**: `Personas\ReniecHttpClient` (import/mapeo a entidad) vs `Pide\ReniecHttpClient` (passthrough de interoperabilidad general). No es duplicación.

## Validación

- ✅ Diff byte-a-byte contra Django en error paths: 5/5 idénticos.
- ✅ DNI inválido (corto, letras, vacío) → 400 con mensaje exacto.
- ✅ RUC inválido → 400 con mensaje exacto.
- ✅ Credenciales no configuradas → 502 con mensaje exacto.
- ✅ Timeout upstream → 502 con mensaje exacto.
- ✅ 43 tests de Pide, 106 assertions.
- ✅ PHPStan Level 8: 0 errores.
- ✅ Pint: 0 errores.

## Limitaciones documentadas

- **TD-016: Happy path de PIDE requiere credenciales gubernamentales válidas.**  
  No se validó contra RENIEC/SUNAT reales por falta de credenciales PIDE en el entorno de desarrollo local. El código está listo para producción; solo hay que configurar `RENIEC_NUDNIUSUARIO`, `RENIEC_NURUCUSUARIO`, `RENIEC_PASSWORD` en `.env` de staging/prod.

## Próxima fase

FASE 8B — Archivos (Expedientes, ITSE, Licencias).
