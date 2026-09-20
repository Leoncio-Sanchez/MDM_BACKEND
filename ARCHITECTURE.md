# 🏛️ Arquitectura del Backend: PHP/Laravel 11 + Clean Architecture

Este documento establece los lineamientos arquitectónicos inmutables para el backend del proyecto. Cualquier nuevo módulo DEBE apegarse a estas reglas.

## 1. Capas de Clean Architecture

```mermaid
graph TB
    subgraph EXT["🌐 SISTEMA EXTERNO"]
        API[REST API / JSON]
        DB[(PostgreSQL)]
        PIDE[PIDE · RENIEC · SUNAT]
    end

    subgraph PRES["1️⃣ PRESENTATION · MVC"]
        C[Controllers<br/>≤10 líneas/método]
        RQ[Form Requests]
        RS[API Resources]
        RT[Routes]
    end

    subgraph APP["2️⃣ APPLICATION"]
        UC[Use Cases<br/>1 caso = 1 acción]
        DTO[DTOs inmutables]
        MP[Mappers]
    end

    subgraph DOM["3️⃣ DOMAIN · Núcleo Puro"]
        EN[Entities readonly]
        VO[Value Objects]
        CO[Contracts]
        EX[Exceptions]
    end

    subgraph INFRA["4️⃣ INFRASTRUCTURE"]
        MO[Eloquent Models]
        RP[Repositories]
        HT[HTTP Clients]
        SP[Service Providers]
    end

    EXT --> PRES
    PRES --> APP
    APP --> DOM
    INFRA -.implementa.-> DOM
    INFRA --> DB
    INFRA --> PIDE

    style DOM fill:#1e293b,stroke:#38bdf8,stroke-width:3px,color:#e2e8f0
    style APP fill:#1e3a5f,stroke:#60a5fa,color:#e2e8f0
    style INFRA fill:#1e3a5f,stroke:#60a5fa,color:#e2e8f0
    style PRES fill:#1e3a5f,stroke:#60a5fa,color:#e2e8f0
```

## 2. Flujo de una petición HTTP

```mermaid
sequenceDiagram
    participant C as Cliente Vue
    participant Ctrl as Controller
    participant DTO as DTO
    participant UC as UseCase
    participant Repo as Repository
    participant DB as PostgreSQL

    C->>Ctrl: POST /api/lf-itse/estados/
    Ctrl->>DTO: fromRequest(request)
    DTO-->>Ctrl: DTO inmutable
    Ctrl->>UC: execute(DTO)
    UC->>Repo: create(Entity)
    Repo->>DB: Eloquent::create()
    DB-->>Repo: Model
    Repo-->>UC: Entity
    UC-->>Ctrl: Entity
    Ctrl->>C: API Resource (JSON)
```

## 3. Estructura Modular por Bounded Contexts

```mermaid
graph TD
    subgraph APP["app/Modules"]
        SH[Shared]
        ES[Estados]
        EX[Expedientes]
        LF[Licencias LF]
        IT[ITSE]
        AU[Auditoría]
    end

    ES --> SH
    EX --> SH
    LF --> SH
    LF -.-> ES
    LF -.-> EX
    IT -.-> EX
```

## 4. Estructura Interna de un Módulo (Ej. Estados)

```mermaid
graph LR
    subgraph MOD["app/Modules/Estados/"]
        direction TB
        DOM["Domain/"]
        APP["Application/"]
        INF["Infrastructure/"]
        PRE["Presentation/"]
    end
```

## 5. Regla de Dependencias

```mermaid
graph TD
    subgraph OUTER["Outer Layers"]
        PRE[Presentation]
        INF[Infrastructure]
    end

    subgraph INNER["Inner Layers"]
        APP[Application]
        DOM[Domain]
    end

    PRE --> APP
    INF --> DOM
    APP --> DOM

    style OUTER fill:#f1f5f9,color:#000
    style INNER fill:#e0f2fe,color:#000
```

## 6. Roadmap de Migración

```mermaid
flowchart LR
    M1[Estados] --> M2[Expedientes]
    M2 --> M3[Licencias LF]
    M3 --> M4[ITSE]
    M4 --> M5[Seguridad]
    M5 --> M6[Auditoría]
```

## 7. Comparativa Django vs Laravel (Mapping)

```mermaid
graph LR
    subgraph DJ["Django (Antes)"]
        V[Views / ViewSets]
        S[Serializers]
        M[Models]
        U[urls.py]
    end

    subgraph LV["Laravel (Ahora)"]
        C[Controllers]
        R[Requests / Resources]
        E[Eloquent Models]
        RT[api.php]
    end

    V --> C
    S --> R
    M --> E
    U --> RT
```

## 8. Clean Architecture Clásica vs Aplicada (PHP)

```mermaid
graph TD
    subgraph CA["Clásica (Martin)"]
        E[Entities]
        UC[Use Cases]
        C[Controllers/Presenters]
        DB[DB/Web]
    end

    subgraph LV["Nuestra (Laravel)"]
        E2[Entities + Contracts]
        UC2[Use Cases + DTOs]
        C2[Controllers + Resources]
        DB2[Eloquent Models + Repositories]
    end

    E -.-> E2
    UC -.-> UC2
    C -.-> C2
    DB -.-> DB2
```
