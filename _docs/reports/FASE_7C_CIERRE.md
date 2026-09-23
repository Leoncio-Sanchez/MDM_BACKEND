# FASE 7C — Cierre

**Fecha:** 2026-09-23
**Rama:** master
**Commits:** 7C.0 → 7C.5

## Resumen

Se completó la FASE 7C de Expedientes (Workflows: ampliación de plazo,
denegación de licencia/ITSE, consulta de autorización improcedente).

## Sub-gates cerrados

| Sub-gate | Contenido | Commit | Tests |
|----------|-----------|--------|-------|
| 7C.0 | Auditoría de workflows | c4637d7 | — |
| 7C.1+7C.2 | Domain + Application (4 UseCases + 4 DTOs + 3 Exceptions + conAmpliacion) | 0381dad | +19 |
| 7C.3 | Controller + 4 rutas + 31 tests Feature | a1c47ee | +21 |
| 7C.4 | Diff con datos reales vs Django | 50f3e1f | +2 |
| 7C.5 | Cierre + reporte | HEAD | — |

## Endpoints implementados

| Método | Endpoint | Status OK | Status Error |
|--------|----------|-----------|--------------|
| POST | /expedientes/{pk}/ampliacion-plazo/ | 200 | 400, 404 |
| POST | /expedientes/{pk}/denegar-licencia/ | 201 | 400, 404 |
| POST | /expedientes/{pk}/denegar-itse/ | 201 | 400, 404 |
| GET | /expedientes/{pk}/autorizacion-improcedente/?tipo=LF\|ITSE | 200 | 400 |

## Hallazgos críticos

1. **Cálculo de ampliación**: parte de `fecha_vencimiento_actual`, no de
   `fecha_suspension`. Validado con diff real: 5 días hábiles desde
   2026-09-25 → 2026-10-02.
2. **Idempotencia**: Django permite duplicados en denegaciones (2 INSERTs
   si se llama 2 veces). Laravel replicado.
3. **fecha_suspension**: Django NO valida contra `fecha_recepcion`. Se
   persiste tal cual como metadato.
4. **Respuesta vacía en "sin registro"**: Django devuelve HTTP 200 con
   body vacío (`Content-Length: 0`); Laravel replicado con `new JsonResponse('', 200, [], 0, true)`.
5. **Errores de emisión previa**: Mensajes exactos de negocio de Django replicados 1:1
   (`LicenciaYaEmitidaError` y `ItseYaEmitidaError`).

## Validación

- ✅ Diff byte-a-byte contra Django con datos reales en Neon: 4/4 workflows idénticos.
- ✅ Errores de validación replicados (LicenciaYaEmitidaError, ItseYaEmitidaError).
- ✅ 159 tests de Expedientes, 660 assertions.
- ✅ PHPStan Level 8: 0 errores en Expedientes.
- ✅ Pint: 0 errores de estilo.
- ✅ DB Neon 100% limpia post-test.

## Deuda técnica documentada

- TD-015: Replicar body vacío en respuestas "sin registro" de `autorizacion-improcedente` para paridad byte-a-byte con Django REST Framework.

## Próxima fase

FASE 8 — PIDE (RENIEC + SUNAT + Archivos).
