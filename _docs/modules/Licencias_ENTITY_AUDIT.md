# Auditoría de Entidad Licencia vs Modelo Django

## Campos del modelo Django

| Campo | Tipo | Null | Default | Presente en Entity | Acción |
|---|---|---|---|---|---|
| id | int | no | auto | ✅ | — |
| uuid | uuid | no | auto | ✅ | — |
| numero_licencia | int | no | — | ✅ | — |
| fecha_emision | date | no | — | ✅ | — |
| nombre_comercial | str(250) | no | — | ✅ | — |
| titular_id | FK | no | — | ✅ | — |
| conductor_id | FK | no | — | ❌ | Añadir |
| licencia_principal_id | FK self | sí | — | ❌ | Añadir |
| expediente_id | FK | no | — | ✅ | — |
| tipo_licencia_id | FK | no | — | ✅ | — |
| nivel_riesgo_id | FK | no | — | ✅ | — |
| zonificacion_id | FK | no | — | ✅ | — |
| es_vigencia_indeterminada | bool | no | true | ❌ | Añadir |
| fecha_inicio_vigencia | date | sí | — | ❌ | Añadir |
| fecha_fin_vigencia | date | sí | — | ❌ | Añadir |
| actividad | str | no | — | ❌ | Añadir |
| direccion | str | no | — | ❌ | Añadir |
| hora_desde | int | no | — | ❌ | Añadir |
| hora_hasta | int | no | — | ❌ | Añadir |
| resolucion_numero | str | no | — | ❌ | Añadir |
| area | decimal | no | — | ❌ | Añadir |
| numero_recibo_pago | str | no | — | ❌ | Añadir |
| observaciones | text | sí | — | ❌ | Añadir |
| se_puede_publicar | bool | no | false | ❌ | Añadir |
| fecha_notificacion | datetime | sí | — | ❌ | Añadir |
| estado | FK/str | no | — | ✅ | — |
