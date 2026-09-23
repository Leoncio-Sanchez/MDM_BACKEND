# Reporte de Auditoría — FASE 7C (Workflows de Expedientes)

**Fecha:** 2026-09-23  
**Sub-gate:** 7C.0  
**Estado:** ✅ AUDITORÍA COMPLETADA  

---

## 1. Resumen de la Auditoría

Se realizó una inspección exhaustiva de las vistas, serializers, servicios y modelos de Django en `app_lf_itse` para determinar la lógica exacta de los 4 endpoints de workflows asociados a Expedientes.

### Hallazgos Arquitectónicos Clave:
1. **`AutorizacionImprocedenteView` es de solo lectura (`GET`):** En Django NO existe método `POST` en `/expedientes/<pk>/autorizacion-improcedente/`. La creación de autorizaciones improcedentes ocurre exclusivamente a través de los endpoints especializados `/expedientes/<pk>/denegar-licencia/` (para LF) y `/expedientes/<pk>/denegar-itse/` (para ITSE).
2. **`AmpliacionPlazoSerializer` y Recálculo de Días Hábiles:** El workflow de ampliación recibe `fecha_suspension`, `dias_ampliacion` y `motivo_ampliacion`. Recalcula `fecha_vencimiento` sumando días hábiles a la fecha de vencimiento actual, y recalcula `fecha_alerta` restando `dias_alerta_vencimiento` del tipo de procedimiento. Devuelve el expediente completo (200 OK con 19 keys).
3. **Validaciones de Integridad Previa en Denegación:**
   - Para denegar Licencia: el expediente NO debe tener una `LicenciaFuncionamiento` emitida (si existe, lanza `LicenciaYaEmitidaError` -> 400).
   - Para denegar ITSE: el expediente NO debe tener una `Itse` emitida (si existe, lanza `ItseYaEmitidaError` -> 400).
4. **Respuesta de Denegación (201 Created):** Ambos endpoints de denegación crean un registro en `autorizaciones_improcedentes` con `tipo_autorizacion = 'LF'` o `'ITSE'`, y retornan el registro con código HTTP 201 y las 8 keys de `AutorizacionImprocedenteSerializer`.

---

## 2. Matriz de los 4 Workflows

| # | Workflow | Endpoint | Método | Request Body / Params | Response | Tablas Afectadas | Reglas de Negocio / Validaciones |
|---|---|---|---|---|---|---|---|
| 1 | **Ampliación de Plazo** | `/api/lf-itse/expedientes/<pk>/ampliacion-plazo/` | `POST` | Body: `fecha_suspension` (YYYY-MM-DD), `dias_ampliacion` (int >= 1), `motivo_ampliacion` (string <= 250) | `200 OK` (19 keys de `Expediente`) | `expedientes` (UPDATE) | • `dias_ampliacion >= 1`<br>• Recalcula `fecha_vencimiento` sumando días hábiles<br>• Recalcula `fecha_alerta`<br>• Registra `usuario_ampliacion` y `fecha_digitacion_ampliacion` |
| 2 | **Consultar Autorización Improcedente** | `/api/lf-itse/expedientes/<pk>/autorizacion-improcedente/` | `GET` | Query param: `tipo` (`'LF'` \| `'ITSE'`) | `200 OK` (8 keys de `AutorizacionImprocedente` o `null`) | Ninguna (SELECT) | • Parámetro `tipo` obligatorio<br>• `tipo` debe ser `'LF'` o `'ITSE'` (400 si inválido)<br>• Retorna `null` si no existe registro |
| 3 | **Denegar Licencia de Funcionamiento** | `/api/lf-itse/expedientes/<pk>/denegar-licencia/` | `POST` | Body: `fecha_rechazo` (YYYY-MM-DD), `documento` (string <= 100), `observaciones` (string <= 1000) | `201 Created` (8 keys de `AutorizacionImprocedente`) | `autorizaciones_improcedentes` (INSERT) | • 404 si expediente no existe<br>• 400 si ya existe una Licencia emitida en `licencias_funcionamiento`<br>• Fija `tipo_autorizacion = 'LF'` |
| 4 | **Denegar ITSE (Desfavorable)** | `/api/lf-itse/expedientes/<pk>/denegar-itse/` | `POST` | Body: `fecha_rechazo` (YYYY-MM-DD), `documento` (string <= 100), `observaciones` (string <= 1000) | `201 Created` (8 keys de `AutorizacionImprocedente`) | `autorizaciones_improcedentes` (INSERT) | • 404 si expediente no existe<br>• 400 si ya existe una ITSE emitida en `itse`<br>• Fija `tipo_autorizacion = 'ITSE'` |

---

## 3. Estado en Laravel: Qué Existe vs Qué Falta

### Ya Existente en Laravel (Reutilizable):
- **Entidad de Dominio:** `AutorizacionImprocedente` y `Expediente`.
- **Value Objects:** `TipoAutorizacion` (`enum: LF, ITSE`).
- **Servicios de Dominio:** `CalculadoraDiasHabilesService` (`calcularFechaVencimiento()`, `calcularFechaAlerta()`, `esFeriado()`).
- **Repositorios y Contratos:**
  - `AutorizacionImprocedenteRepositoryInterface` y `EloquentAutorizacionImprocedenteRepository` (`buscarPorExpedienteYTipo()`, `crear()`, `existe()`).
  - `ExpedienteRepositoryInterface` (`buscarPorId()`, `actualizar()`, `tieneLicencia()`, `tieneItse()`).
- **Resources de Presentación:**
  - `ExpedienteResource` (19 keys con fechas en America/Lima `-05:00`).
  - `AutorizacionImprocedenteResource` (8 keys: `id`, `tipo_autorizacion`, `fecha_rechazo`, `documento`, `observaciones`, `fecha_digitacion`, `expediente`, `usuario`).

### Por Implementar para FASE 7C:
- **Excepciones de Dominio:**
  - `LicenciaYaEmitidaError`
  - `ItseYaEmitidaError`
  - `TipoAutorizacionInvalidoError`
- **DTOs:**
  - `AmpliacionPlazoDTO` (`fechaSuspension`, `diasAmpliacion`, `motivoAmpliacion`, `usuarioId`)
  - `DenegarAutorizacionDTO` (`expedienteId`, `tipoAutorizacion`, `fechaRechazo`, `documento`, `observaciones`, `usuarioId`)
  - `ConsultarAutorizacionImprocedenteDTO` (`expedienteId`, `tipoAutorizacion`)
- **UseCases (Application Layer):**
  - `AmpliarPlazoExpedienteUseCase`
  - `ConsultarAutorizacionImprocedenteUseCase`
  - `DenegarLicenciaUseCase`
  - `DenegarItseUseCase`
- **Form Requests (Presentation Layer):**
  - `AmpliacionPlazoRequest`
  - `DenegarAutorizacionRequest`
  - `ConsultarAutorizacionImprocedenteRequest`
- **Controller & Rutas:**
  - Métodos en `ExpedienteController`: `ampliacionPlazo()`, `autorizacionImprocedente()`, `denegarLicencia()`, `denegarItse()`.
  - Rutas registradas bajo prefijo `api/lf-itse/expedientes/{pk}/...` protegidas con middleware `auth:api`.

---

## 4. Análisis de Riesgos

| Riesgo | Probabilidad | Impacto | Estrategia de Mitigación |
|---|---|---|---|
| **Discrepancia en recálculo de fechas de ampliación** | Media | Alto | `CalculadoraDiasHabilesService` ya fue validada con 8 tests específicos de feriados y días hábiles. Se cubrirá con tests unitarios comparando contra fechas calculadas en Django. |
| **Idempotencia / Duplicidad en denegaciones** | Baja | Medio | La validación verifica que no exista licencia/ITSE previa emitida. Se añadirá test para asegurar el comportamiento si se intenta denegar dos veces. |
| **Manejo de Transacciones (DB::transaction)** | Baja | Alto | Las operaciones de escritura (`crear` autorización improcedente y `actualizar` expediente) deben ejecutarse dentro de transacciones atómicas. |
| **Formato de Fecha y Timestamp en Autorizaciones** | Baja | Medio | `AutorizacionImprocedenteResource` ya formatea `fecha_rechazo` como `Y-m-d` y `fecha_digitacion` con microsegundos y timezone `-05:00`. Se validará con diff real en 7C.5. |

---

## 5. Plan de Sub-gates Propuesto para FASE 7C

| Sub-gate | Alcance | Duración Estimada |
|---|---|---|
| **7C.1** | **Domain:** Excepciones (`LicenciaYaEmitidaError`, `ItseYaEmitidaError`, `TipoAutorizacionInvalidoError`) + métodos en entidad `Expediente` | ~1.5 h |
| **7C.2** | **Application:** 3 DTOs + 4 UseCases (`AmpliarPlazo`, `ConsultarImprocedente`, `DenegarLicencia`, `DenegarItse`) con tests unitarios puros (Mockery) | ~2.5 h |
| **7C.3** | **Infrastructure:** Métodos de repositorio transaccionales (si aplican) y verificación contra PostgreSQL | ~1.5 h |
| **7C.4** | **Presentation:** 3 FormRequests + 4 métodos en `ExpedienteController` + 4 rutas API | ~2 h |
| **7C.5** | **Tests Feature + Diff con datos reales vs Django:** Pruebas contra PostgreSQL Neon para los 4 endpoints | ~2.5 h |
| **7C.6** | **Cierre Formal + Reporte:** Suite completa (tests, PHPStan Level 8, Pint) y reporte de cierre | ~1 h |
