# TOGAF ADM — Fase C: Arquitectura de Sistemas de Información (Data & Applications)

**Organización:** Municipalidad Distrital  
**Proyecto:** Plataforma Integral de Licencias y Gestión Territorial  
**Fase TOGAF:** Fase C  
**Fecha:** 2026-09-25  

---

## 1. Arquitectura de Datos (Data Architecture)

La arquitectura de datos estructura los activos informacionales de la municipalidad garantizando consistencia, integridad referencial y supresión de duplicidades.

### 1.1 Entidades y Modelo Conceptual de Datos

```mermaid
erDiagram
    PERSONA ||--o{ PERSONA_DOCUMENTO : "posee"
    PERSONA ||--o{ EXPEDIENTE : "inicia"
    EXPEDIENTE ||--|| LICENCIA_FUNCIONAMIENTO : "origina"
    EXPEDIENTE ||--o{ ITSE : "requiere"
    EXPEDIENTE ||--o{ EXPEDIENTE_ARCHIVO : "contiene"
    LICENCIA_FUNCIONAMIENTO ||--o{ LICENCIA_GIRO : "incluye"
    LICENCIA_FUNCIONAMIENTO ||--o{ WORKFLOW_LICENCIA_HISTORIAL : "registra"
    ITSE ||--o{ INSPECCION : "desarrolla"
    INSPECCION ||--o{ INSPECCION_EVIDENCIA : "respalda"
    INSPECCION ||--o{ INSPECCION_OBSERVACION : "detalla"
    USUARIO ||--o{ WORKFLOW_LICENCIA_HISTORIAL : "ejecuta"
    NOTIFICACION }o--|| USUARIO : "dirigida_a"

    PERSONA {
        int id PK
        string nombres
        string apellido_paterno
        string razon_social
    }
    EXPEDIENTE {
        int id PK
        string numero_expediente
        int anio
        int persona_id FK
        string estado
    }
    LICENCIA_FUNCIONAMIENTO {
        int id PK
        int expediente_id FK
        string numero_licencia
        string estado
        float area_total
    }
    INSPECCION {
        int id PK
        uuid uuid
        int licencia_id FK
        int inspector_id FK
        date fecha_programada
        string estado
        string resultado
    }
```

### 1.2 Fuentes de Datos y Eliminación de Duplicidades

| Entidad / Dominio | Fuente Primaria AS-IS | Problema de Duplicidad Detectado | Fuente Primaria TO-BE (Arquitectura) |
|-------------------|-----------------------|----------------------------------|---------------------------------------|
| **Personas Naturales** | Registro manual en ventanilla | DNI digitado con errores; copias físicas en cada trámite. | **PIDE RENIEC** (Consulta en tiempo real vía passthrough). |
| **Personas Jurídicas** | Copia de ficha RUC en papel | Información tributaria desactualizada o con estado inactivo. | **PIDE SUNAT** (Consulta directa del estado RUC y domicilio fiscal). |
| **Zonificación y Usos** | Catastro impreso y Excel | Conflictos entre el plano catastral y la memoria del inspector. | **Tabla `zonificaciones` centralizada** en PostgreSQL. |
| **Expediente e Historial**| Carpetas físicas de archivo | Pérdida de resoluciones y fechas contradictorias. | **Tabla `workflow_licencia_historial` append-only**. |
| **Evidencias de Inspección**| Archivos locales en cámaras/PCs | Fotos sin trazabilidad de fecha ni inspector responsable. | **Tabla `inspeccion_evidencias`** con hashes SHA-256 en almacenamiento seguro. |

### 1.3 Ciclo de Vida y Flujo de Datos

```mermaid
flowchart LR
    A["Entrada:\nSolicitud Ciudadana\n(JSON + Binarios)"] --> B["Validación PIDE:\nRENIEC / SUNAT\n(Normalización)"]
    B --> C["Persistencia Transaccional:\nPostgreSQL 16\n(ACID)"]
    C --> D["Operación & Workflow:\nHistorial inmutable\n(Audit Log)"]
    C --> E["Almacenamiento Binario:\nDisco Seguro\n(Hash SHA-256)"]
    C --> F["Analítica & Extracción:\nConsultas BI & KPIs"]
```

---

## 2. Arquitectura de Aplicaciones (Application Architecture)

### 2.1 Comparación de Ecosistemas: AS-IS vs. TO-BE

```mermaid
flowchart TD
    subgraph AS_IS["Ecosistema de Aplicaciones AS-IS (Fragmentado)"]
        D1["Backend Django Legacy\n(Módulos aislados)"]
        D2["Mesa de Partes Manual\n(Libro de registros)"]
        D3["Inspecciones en Papel\n(Sin integración)"]
        D4["Notificación Telefónica\n(No sistematizada)"]
    end

    subgraph TO_BE["Ecosistema de Aplicaciones TO-BE (Orquestado)"]
        A1["Portal Ciudadano Responsive\n(Vue / Vite / Tailwind)"]
        A2["Backend MVC Centralizado\n(Laravel 11 / PHP 8.5)"]
        A3["Módulo Workflow & Máquina de Estados"]
        A4["Módulo de Inspecciones con Evidencias"]
        A5["Servicio de Correo Transaccional (SMTP)"]
        A6["Módulo de Auditoría & Dashboard BI"]
        A7["Adaptador PIDE (RENIEC / SUNAT)"]
    end

    A1 -->|"REST API / JWT"| A2
    A2 --> A3
    A2 --> A4
    A2 --> A5
    A2 --> A6
    A2 --> A7
```

### 2.2 Componentes de Aplicación en el Backend Laravel 11 MVC

1. **Capa de Controladores (`app/Controllers/`):** 19 controladores delgados que gestionan las peticiones HTTP, delegan la lógica al Service y devuelven Resources normalizados.
2. **Capa de Servicios de Negocio (`app/Services/`):** Contiene la lógica orquestadora:
   - `LicenciaService` y `WorkflowLicenciaService`: Máquina de estados determinista.
   - `InspeccionService`: Manejo de programación, ejecución y evidencias.
   - `NotificacionService`: Formulación y encolado de correos electrónicos.
   - `ReniecService` y `SunatService`: Integración con servicios externos vía Http facade.
3. **Capa de Repositorios Especializados (`app/Repositories/`):** 4 repositorios dedicados a consultas complejas SQL crudo (CTEs, agregaciones masivas y optimizaciones de búsqueda).
4. **Capa de Validación (`app/Requests/`):** 52 FormRequests que aseguran que ningún payload inconsistente ingrese a la capa de dominio.
5. **Capa de Transformación (`app/Resources/`):** 25 API Resources que aseguran contratos JSON compatibles con Django y desacoplados del esquema físico de BD.
6. **Mecanismo de Autenticación (`app/Auth/`):** Guards y Providers personalizados compatibles con JWT de Django (SimpleJWT) y hashes PBKDF2.

### 2.3 Matriz de Trazabilidad: Aplicación vs. Función de Negocio

| Componente de Aplicación | Solicitud de Licencia | Revisión Técnica | Programación ITSE | Ejecución ITSE | Notificaciones | Emisión Licencia |
|--------------------------|:---------------------:|:----------------:|:-----------------:|:--------------:|:--------------:|:----------------:|
| **Portal Ciudadano** | **X** | | | | | **X** (Descarga) |
| **LicenciaController / Service** | **X** | **X** | | | | **X** |
| **WorkflowLicenciaService** | **X** | **X** | **X** | **X** | | **X** |
| **InspeccionController / Service**| | | **X** | **X** | | |
| **NotificacionService / Jobs** | **X** | **X** | **X** | **X** | **X** | **X** |
| **PIDE Adapter (Reniec/Sunat)** | **X** | | | | | |
| **Dashboard BI Controller** | | **X** | **X** | | | **X** |
