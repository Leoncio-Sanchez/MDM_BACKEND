# Lógica real de los 5 UseCases (extraída de Django)

## 1. ModificarLicenciaUseCase

**Reglas de negocio:**
- Verificar licencia existe (lanza LicenciaNoEncontrada).
- Verificar expediente no tenga autorización improcedente de LF (lanza LicenciaDenegadaError).
- Validar `numero_licencia` único excluyendo actual (lanza LicenciaDuplicadaError).
- Validar `numero_recibo_pago` único en licencias excluyendo actual (lanza ReciboPagoDuplicadoError).
- Forzar `fecha_inicio_vigencia` y `fecha_fin_vigencia` a `null` si `es_vigencia_indeterminada` es true.
- Actualizar campos de la licencia en transacción.
- **Reemplazar completamente los giros**: eliminar todos los giros asociados y crear los nuevos (bulk insert).

## 2. EliminarLicenciaUseCase

**Reglas de negocio:**
- Verificar licencia existe.
- **Verificar que NO tenga licencias dependientes** (lanza LicenciaTieneDependientesError).
- Buscar y almacenar rutas de archivos (`LicenciaFuncionamientoArchivo`).
- Eliminar en transacción.
- En Django (post-transacción), iterar sobre las rutas para borrarlos con `default_storage.delete(ruta)`.

## 3. ListarEstadosLicenciaUseCase

**Reglas de negocio:**
- No verifica existencia previa (o si lo hace, no levanta excepción extra, solo consulta SQL).
- Consulta directa con SQL crudo a `licencias_funcionamiento_estados` unido a usuarios y estados para sacar nombres.
- Devuelve un array (list[dict]) con id, estado_nombre, fecha_estado, observaciones, documento, fecha_digitacion, usuario.

## 4. RegistrarInactivacionUseCase

**Reglas de negocio:**
- Verificar licencia existe.
- **Verificar que el estado no esté duplicado** (mismo licencia_id y estado_id en historial). Si está duplicado lanza EstadoInactivacionDuplicadoError.
- Insertar registro en `licencias_funcionamiento_estados` con fecha de estado, documento, observaciones, usuario y `fecha_digitacion=now()`.

## 5. RegistrarNotificacionUseCase

**Reglas de negocio:**
- Verificar licencia existe.
- **Validar que `fecha_notificacion >= fecha_emision`** (lanza NotificacionFechaInvalidaError).
- Actualizar y persistir solo el campo `fecha_notificacion`.

## Dependencias adicionales

- `LicenciaTieneDependientesError`
- `EstadoInactivacionDuplicadoError`
- `NotificacionFechaInvalidaError`
- `LicenciaDenegadaError`
- `LicenciaDuplicadaError`
- `ReciboPagoDuplicadoError`
- Tablas/Modelos: `licencias_funcionamiento_estados`, `licencias_funcionamiento_archivos`, `licencias_funcionamiento_giros`.
