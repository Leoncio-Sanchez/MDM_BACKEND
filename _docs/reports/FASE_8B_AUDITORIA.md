# Reporte de Auditoría Técnica — FASE 8B: Archivos (Expedientes, ITSE, Licencias)

## 1. Resumen Ejecutivo

Este documento formaliza la auditoría técnica de los 9 endpoints y los 3 servicios de archivos digitales en el backend Django (`app_lf_itse`), analizando su comportamiento, manejo de almacenamiento, atomicidad y estrategia de migración hacia Laravel 11.

Los 3 servicios (`expediente_archivo.py`, `itse_archivo.py`, `licencia_funcionamiento_archivo.py`) comparten una lógica **100% idéntica** en su flujo transaccional y de almacenamiento físico, diferenciándose exclusivamente en la carpeta relativa y el modelo de BD destino.

---

## 2. Tabla de los 9 Endpoints Auditados

| # | Módulo | Método | Endpoint Django / Laravel | Content-Type / Payload | Status OK | Status Errores |
|---|---|---|---|---|---|---|
| 1 | Expedientes | `GET` | `/api/lf-itse/expedientes/<pk>/archivos/` | `application/json` | 200 (Array) | 401, 404 |
| 2 | Expedientes | `POST` | `/api/lf-itse/expedientes/<pk>/archivos/` | `multipart/form-data` (`archivo`) | 201 (Object) | 400, 401, 404, 500 |
| 3 | Expedientes | `DELETE` | `/api/lf-itse/expedientes/archivos/<pk>/` | — | 204 (No Content) | 401, 404, 500 |
| 4 | Expedientes | `GET` | `/api/lf-itse/expedientes/archivos/<uuid>/descargar/` | Binary Stream (`inline`) | 200 (Stream) | 401, 404 |
| 5 | ITSE | `GET` | `/api/lf-itse/itse/<pk>/archivos/` | `application/json` | 200 (Array) | 401, 404 |
| 6 | ITSE | `POST` | `/api/lf-itse/itse/<pk>/archivos/` | `multipart/form-data` (`archivo`) | 201 (Object) | 400, 401, 404, 500 |
| 7 | ITSE | `DELETE` | `/api/lf-itse/itse/archivos/<pk>/` | — | 204 (No Content) | 401, 404, 500 |
| 8 | ITSE | `GET` | `/api/lf-itse/itse/archivos/<uuid>/descargar/` | Binary Stream (`inline`) | 200 (Stream) | 401, 404 |
| 9 | Licencias | `GET` | `/api/lf-itse/licencias-funcionamiento/<pk>/archivos/` | `application/json` | 200 (Array) | 401, 404 |
| 10 | Licencias | `POST` | `/api/lf-itse/licencias-funcionamiento/<pk>/archivos/` | `multipart/form-data` (`archivo`) | 201 (Object) | 400, 401, 404, 500 |
| 11 | Licencias | `DELETE` | `/api/lf-itse/licencias-funcionamiento/archivos/<pk>/` | — | 204 (No Content) | 401, 404, 500 |
| 12 | Licencias | `GET` | `/api/lf-itse/licencias-funcionamiento/archivos/<uuid>/descargar/` | Binary Stream (`inline`) | 200 (Stream) | 401, 404 |

*(Nota: Aunque se totalizan 9 rutas lógicas de archivo, las vistas de upload implementan tanto `GET` de listado como `POST` de subida).*

---

## 3. Comparativa de los 3 Servicios de Negocio

| Aspecto | `expediente_archivo.py` | `itse_archivo.py` | `licencia_funcionamiento_archivo.py` |
|---|---|---|---|
| **Carpeta storage** | `expedientes/{pk}/` | `itse/{pk}/` | `licencias_funcionamiento/{pk}/` |
| **Modelo BD** | `ExpedienteArchivo` | `ItseArchivo` | `LicenciaFuncionamientoArchivo` |
| **Tabla BD** | `expedientes_archivos` | `itse_archivos` | `licencias_funcionamiento_archivos` |
| **Nombre archivo** | `{uuid4}.{ext}` | `{uuid4}.{ext}` | `{uuid4}.{ext}` |
| **Estrategia Upload** | 1. Guarda en disco<br>2. Transacción BD crea registro<br>3. Si BD falla: borra archivo de disco | Idéntico | Idéntico |
| **Estrategia Delete** | 1. Transacción BD borra registro<br>2. Si BD ok: borra de disco<br>3. Si disco falla: warning en log | Idéntico | Idéntico |
| **Estrategia Download** | 1. Busca por `uuid`<br>2. Valida existencia en disco<br>3. `guess_type` + header `inline` | Idéntico | Idéntico |

---

## 4. Estructura de BD y Serialización de Metadatos

Las tres tablas comparten exactamente las mismas 10 columnas en PostgreSQL:
1. `id` (int PK)
2. `uuid` (uuid unique)
3. `{parent}_id` (`expediente_id`, `itse_id`, `licencia_funcionamiento_id`)
4. `nombre_original` (varchar 255)
5. `nombre_archivo` (varchar 255)
6. `ruta_archivo` (varchar 500)
7. `extension` (varchar 20)
8. `tamanio_bytes` (bigint)
9. `usuario_id` (int FK a `auth_user`)
10. `fecha_digitacion` (timestamptz)

### Serializer de Salida
En Django, los tres serializers usan `fields = '__all__'`, retornando:
```json
{
  "id": 1,
  "uuid": "4565c818-99a2-4380-b1d8-b8fefa047adf",
  "nombre_original": "solicitud.pdf",
  "nombre_archivo": "4565c818-99a2-4380-b1d8-b8fefa047adf.pdf",
  "ruta_archivo": "expedientes/5/4565c818-99a2-4380-b1d8-b8fefa047adf.pdf",
  "extension": "pdf",
  "tamanio_bytes": 102400,
  "fecha_digitacion": "2026-09-23T15:30:00Z",
  "expediente": 5,
  "usuario": 1
}
```

---

## 5. Validaciones y Storage

1. **Storage Físico:**
   - Django define `MEDIA_ROOT = BASE_DIR / 'media'`.
   - No se usa AWS S3 ni Azure Blob. Los archivos residen localmente en el filesystem bajo subcarpetas organizadas por entidad y PK.
2. **Validación de Archivos:**
   - El serializer de entrada solo aplica `archivo = serializers.FileField()`.
   - No hay una lista blanca restrictiva de extensiones (se extrae la extensión original en minúsculas).
   - No hay límite de tamaño custom configurado en settings (aplica el default de Django/servidor web).
3. **Descarga:**
   - La cabecera `Content-Disposition` se fija como `inline; filename="{nombre_original}"`, permitiendo que visores de PDF o imágenes rendericen el archivo directamente en el navegador.

---

## 6. Decisión de Arquitectura para Laravel 11

### Alternativas Evaluadas:
- **Opción A (Trait):** Un único trait `ManejaArchivosTrait` compartido.
- **Opción B (Servicio Base Abstracto + UseCases dedicados):** Recomendada. Un `ArchivoStorageService` centralizado en la infraestructura para la manipulación del disco (`media`), y UseCases limpios (`SubirArchivoUseCase`, `EliminarArchivoUseCase`, etc.) en cada módulo o con un módulo compartido de Archivos.
- **Opción C (3 Implementaciones Independientes):** Violación abierta de DRY (más de 1,200 líneas de código duplicado).

### Decisión Adoptada:
**Arquitectura Híbrida Limpia (DRY + Domain Separation):**
1. **Configuración de Filesystem:** Configurar un disco `'media'` en `config/filesystems.php` apuntando a `/media` o `storage_path('media')` (o vinculando simbióticamente a `backend/media` para pruebas con datos reales).
2. **Infraestructura Compartida:** Crear un servicio de bajo nivel `LocalStorageArchivoManager` que encapsula la generación de nombres seguros (`UUID.ext`), almacenamiento físico y borrado seguro con atomicidad ante errores.
3. **Capa de Dominio y Aplicación:**
   - Definir interfaces de repositorio para cada entidad (`ExpedienteArchivoRepositoryInterface`, `ItseArchivoRepositoryInterface`, `LicenciaArchivoRepositoryInterface`).
   - Implementar UseCases parametrizados o específicos por módulo (`SubirArchivoExpedienteUseCase`, etc.).
4. **Presentación:** 3 Controllers (`ExpedienteArchivoController`, `ItseArchivoController`, `LicenciaFuncionamientoArchivoController`) que publican las rutas exactas requeridas por Django y el frontend.

---

## 7. Plan de Sub-Gates para FASE 8B

| Sub-gate | Alcance | Duración Est. |
|---|---|---|
| **8B.1** | **Domain & Contracts:** Entidades `ExpedienteArchivo`, `ItseArchivo`, `LicenciaArchivo`, ValueObjects (`ArchivoData`), Contratos de Repositorio y Excepciones. | ~2h |
| **8B.2** | **Application:** UseCases (SubirArchivo, ListarArchivos, EliminarArchivo, DescargarArchivo) + DTOs. | ~3h |
| **8B.3** | **Infrastructure:** Configuración del disco `media`, `LocalStorageArchivoManager`, y Repositorios Eloquent para las 3 tablas. | ~3h |
| **8B.4** | **Presentation:** 3 Controllers + 9 rutas bajo `api/lf-itse/` con middleware `auth:api`. | ~3h |
| **8B.5** | **Tests & Diff con Archivos Reales:** Upload/download real, validación binaria, status 200/201/204/400/404. | ~3h |
| **8B.6** | **Cierre Formal de FASE 8B:** Reporte final y balance global del proyecto. | ~30 min |

---

## 8. Riesgos Identificados y Mitigaciones

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Archivos huérfanos por fallo de BD | Acumulación de basura en disco | Replicar la cláusula de compensación: si falla `DB::transaction()`, ejecutar `Storage::delete()` de inmediato |
| Intentos de Path Traversal | Vulnerabilidad de seguridad | Usar únicamente `UUID v4` generado en el servidor como nombre de archivo en disco |
| Fallo al eliminar archivo físico | Registro borrado pero archivo queda en disco | Replicar lógica de Django: borrar registro en BD primero; si el borrado de disco falla, registrar `Log::warning()` sin revertir la BD |
| MIME Type incorrecto en descarga | Navegador descarga archivo en lugar de mostrarlo inline | Mapear MIME types usando `MimeTypes` de Symfony / Laravel y setear `inline; filename="..."` |
