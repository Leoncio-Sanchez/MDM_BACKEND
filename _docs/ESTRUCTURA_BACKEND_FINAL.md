# Estructura Final del Backend PHP (MVC Plano)

**Fecha:** 2026-09-25  
**Rama:** arq-exg  
**Arquitectura:** MVC Plano Laravel 11  
**Módulos:** 18/18 migrados  
**Estado:** ✅ Backend limpio y funcional

---

## 1. Resumen Ejecutivo

| Métrica | Valor |
|---------|-------|
| **Arquitectura** | MVC Plano minimalista |
| **Módulos** | 18 |
| **Archivos PHP en `app/`** | 152 |
| **Líneas netas eliminadas** | -11,477 (vs Hexagonal) |
| **Tests** | 240 pasando (995 assertions) |
| **PHPStan Level 8** | 0 errores |
| **Pint** | 0 errores |
| **Rutas API** | 119 |
| **Paridad Django** | 100% byte-a-byte |

---

## 2. Estructura Completa del Proyecto

```text
backend-php/
│
├── _docs/                                  ← Documentación del proyecto
│   ├── reports/
│   │   ├── FASE_7B_CIERRE.md
│   │   ├── FASE_7C_CIERRE.md
│   │   ├── FASE_8A_CIERRE.md
│   │   ├── FASE_8B_CIERRE.md
│   │   ├── FASE_9_CIERRE.md
│   │   ├── BACKEND_100_PORCIENTO.md
│   │   ├── AUDITORIA_MIGRACION_GLOBAL.md
│   │   ├── REFACTOR_STAGE_2_CIERRE.md
│   │   ├── REFACTOR_STAGE_3_CIERRE.md
│   │   ├── REFACTOR_MVC_CIERRE_FINAL.md
│   │   └── AUDITORIA_FINAL_LIMPIEZA.md
│   ├── ARCHITECTURE_MVC.md
│   ├── ESTRUCTURA_BACKEND_FINAL.md         ← Este documento
│   └── TECH_DEBT.md
│
├── app/                                    ← 152 archivos PHP
│   │
│   ├── Auth/                               ← 2 archivos (intactos)
│   │   ├── DjangoJwtGuard.php
│   │   └── DjangoJwtUserProvider.php
│   │
│   ├── Controllers/                        ← 19 controladores
│   │   ├── ArchivoController.php
│   │   ├── Controller.php (base)
│   │   ├── EstadoController.php
│   │   ├── ExpedienteController.php
│   │   ├── GiroController.php
│   │   ├── InspectorController.php
│   │   ├── ItseController.php
│   │   ├── LicenciaController.php
│   │   ├── NivelRiesgoController.php
│   │   ├── PersonaController.php
│   │   ├── PideController.php
│   │   ├── TipoDocumentoIdentidadController.php
│   │   ├── TipoLicenciaController.php
│   │   ├── TipoProcedimientoTupaController.php
│   │   ├── UnidadOrganicaController.php
│   │   ├── UsuarioController.php
│   │   ├── ZonificacionController.php
│   │   └── Auth/
│   │       ├── JwtAuthController.php
│   │       └── UserController.php
│   │
│   ├── Exceptions/                         ← 7 archivos
│   │   ├── Pide/
│   │   │   ├── PideError.php
│   │   │   ├── ReniecError.php
│   │   │   └── SunatError.php
│   │   └── Seguridad/
│   │       ├── CredencialesInvalidas.php
│   │       ├── SeguridadError.php
│   │       ├── TokenInvalido.php
│   │       └── UsuarioNoEncontrado.php
│   │
│   ├── Models/                             ← 26 modelos Eloquent
│   │   ├── AutorizacionImprocedente.php
│   │   ├── Estado.php
│   │   ├── Expediente.php
│   │   ├── ExpedienteArchivo.php
│   │   ├── FeriadoAnual.php
│   │   ├── FeriadoRecurrente.php
│   │   ├── Giro.php
│   │   ├── Itse.php
│   │   ├── ItseArchivo.php
│   │   ├── ItseEstado.php
│   │   ├── ItseGiro.php
│   │   ├── LicenciaArchivo.php
│   │   ├── LicenciaEstado.php
│   │   ├── LicenciaFuncionamiento.php
│   │   ├── LicenciaGiro.php
│   │   ├── NivelRiesgo.php
│   │   ├── Persona.php
│   │   ├── PersonaDocumento.php
│   │   ├── TipoDocumentoIdentidad.php
│   │   ├── TipoLicencia.php
│   │   ├── TipoProcedimientoTupa.php
│   │   ├── UnidadOrganica.php
│   │   ├── User.php
│   │   ├── Usuario.php
│   │   ├── UsuarioPerfil.php
│   │   └── Zonificacion.php
│   │
│   ├── Providers/                          ← 1 proveedor
│   │   └── AppServiceProvider.php
│   │
│   ├── Repositories/                       ← 4 repositorios (SQL crudo)
│   │   ├── ExpedienteRepository.php
│   │   ├── ItseRepository.php
│   │   ├── LicenciaRepository.php
│   │   └── PersonaRepository.php
│   │
│   ├── Requests/                           ← 52 FormRequests
│   │   ├── Archivos/
│   │   │   └── SubirArchivoRequest.php
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php
│   │   │   ├── RefreshTokenRequest.php
│   │   │   └── VerifyTokenRequest.php
│   │   ├── Estados/
│   │   │   ├── StoreEstadoRequest.php
│   │   │   └── UpdateEstadoRequest.php
│   │   ├── Expedientes/
│   │   │   ├── AmpliarPlazoRequest.php
│   │   │   ├── BuscarExpedienteRequest.php
│   │   │   ├── DenegarItseRequest.php
│   │   │   ├── DenegarLicenciaRequest.php
│   │   │   ├── StoreExpedienteRequest.php
│   │   │   └── UpdateExpedienteRequest.php
│   │   ├── Giros/
│   │   │   ├── StoreGiroRequest.php
│   │   │   └── UpdateGiroRequest.php
│   │   ├── Inspectores/
│   │   │   ├── BuscarInspectorRequest.php
│   │   │   ├── StoreInspectorRequest.php
│   │   │   └── UpdateInspectorRequest.php
│   │   ├── Itse/
│   │   │   ├── ActualizarItseRequest.php
│   │   │   ├── BuscarItseRequest.php
│   │   │   ├── ConsultaItseRequest.php
│   │   │   ├── CrearItseRequest.php
│   │   │   ├── InactivarItseRequest.php
│   │   │   ├── NotificarItseRequest.php
│   │   │   ├── PorRenovarItseRequest.php
│   │   │   └── VerificarExpedienteRequest.php
│   │   ├── Licencias/
│   │   │   ├── BuscarLicenciaRequest.php
│   │   │   ├── ConsultaLicenciaRequest.php
│   │   │   ├── InactivarLicenciaRequest.php
│   │   │   ├── NotificarLicenciaRequest.php
│   │   │   ├── ReporteLicenciaRequest.php
│   │   │   ├── StoreLicenciaRequest.php
│   │   │   ├── UpdateLicenciaRequest.php
│   │   │   └── VerificarExpedienteRequest.php
│   │   ├── NivelRiesgo/
│   │   │   ├── StoreNivelRiesgoRequest.php
│   │   │   └── UpdateNivelRiesgoRequest.php
│   │   ├── Personas/
│   │   │   ├── BuscarPersonaRequest.php
│   │   │   ├── StorePersonaRequest.php
│   │   │   └── UpdatePersonaRequest.php
│   │   ├── Pide/
│   │   │   ├── ConsultarReniecRequest.php
│   │   │   └── ConsultarSunatRequest.php
│   │   ├── TipoDocumentoIdentidad/
│   │   │   ├── ListarTipoDocumentoIdentidadRequest.php
│   │   │   ├── StoreTipoDocumentoIdentidadRequest.php
│   │   │   └── UpdateTipoDocumentoIdentidadRequest.php
│   │   ├── TipoLicencia/
│   │   │   ├── StoreTipoLicenciaRequest.php
│   │   │   └── UpdateTipoLicenciaRequest.php
│   │   ├── TipoProcedimientoTupa/
│   │   │   ├── StoreTipoProcedimientoTupaRequest.php
│   │   │   └── UpdateTipoProcedimientoTupaRequest.php
│   │   ├── Usuarios/
│   │   │   ├── CambiarPasswordRequest.php
│   │   │   ├── StoreUsuarioRequest.php
│   │   │   └── UpdateUsuarioRequest.php
│   │   └── Zonificacion/
│   │       ├── StoreZonificacionRequest.php
│   │       └── UpdateZonificacionRequest.php
│   │
│   ├── Resources/                          ← 25 API Resources
│   │   ├── ArchivoResource.php
│   │   ├── AutorizacionImprocedenteResource.php
│   │   ├── EstadoResource.php
│   │   ├── ExpedienteBusquedaResource.php
│   │   ├── ExpedienteListResource.php
│   │   ├── ExpedientePendienteResource.php
│   │   ├── ExpedienteResource.php
│   │   ├── GiroResource.php
│   │   ├── InspectorResource.php
│   │   ├── ItseListResource.php
│   │   ├── ItseResource.php
│   │   ├── LicenciaListResource.php
│   │   ├── LicenciaReporteResource.php
│   │   ├── LicenciaResource.php
│   │   ├── NivelRiesgoResource.php
│   │   ├── PersonaDocumentoDetalleResource.php
│   │   ├── PersonaDocumentoResource.php
│   │   ├── PersonaResource.php
│   │   ├── TipoDocumentoIdentidadResource.php
│   │   ├── TipoLicenciaResource.php
│   │   ├── TipoProcedimientoTupaResource.php
│   │   ├── UnidadOrganicaResource.php
│   │   ├── UserAuthResource.php
│   │   ├── UsuarioResource.php
│   │   └── ZonificacionResource.php
│   │
│   ├── Services/                           ← 14 servicios
│   │   ├── ArchivoService.php
│   │   ├── AuthenticatedUser.php
│   │   ├── CalculadoraDiasHabiles.php
│   │   ├── DjangoJwtTokenService.php
│   │   ├── DjangoPbkdf2Hasher.php
│   │   ├── ExpedienteService.php
│   │   ├── InspectorService.php
│   │   ├── ItseService.php
│   │   ├── LicenciaService.php
│   │   ├── PersonaService.php
│   │   ├── TokenPair.php
│   │   ├── UsuarioService.php
│   │   └── External/
│   │       ├── ReniecService.php
│   │       └── SunatService.php
│   │
│   └── Shared/                             ← Utilitarios compartidos (2)
│       ├── Infrastructure/Concerns/
│       │   └── ReadOnlyTrait.php
│       └── Presentation/Resources/
│           └── DjangoPaginatedResource.php
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/                                 ← Configuración centralizada
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── cors.php
│   ├── database.php
│   ├── filesystems.php
│   ├── jwt.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   ├── session.php
│   └── ...
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── public/
│   ├── index.php
│   ├── robots.txt
│   └── .htaccess
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/                                 ← Rutas centralizadas
│   ├── api.php                             ← 119 rutas de negocio
│   ├── auth.php
│   ├── console.php
│   └── web.php
│
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
│
├── tests/                                  ← 240 tests (100% pasando)
│   ├── Feature/
│   │   ├── ArchivoControllerTest.php
│   │   ├── AuthControllerTest.php
│   │   ├── CompatibilidadRutasTest.php
│   │   ├── EstadoControllerTest.php
│   │   ├── ExpedienteControllerTest.php
│   │   ├── GiroControllerTest.php
│   │   ├── Inspectores/InspectorApiTest.php
│   │   ├── Itse/ItseControllerTest.php
│   │   ├── LicenciaResourceNullSafetyTest.php
│   │   ├── Licencias/LicenciaControllerTest.php
│   │   ├── NivelRiesgoControllerTest.php
│   │   ├── Personas/PersonaControllerTest.php
│   │   ├── Pide/PideControllerTest.php
│   │   ├── TipoDocumentoIdentidadControllerTest.php
│   │   ├── TipoLicenciaControllerTest.php
│   │   ├── TipoProcedimientoTupaControllerTest.php
│   │   ├── UnidadOrganicaControllerTest.php
│   │   ├── UsuarioControllerTest.php
│   │   └── ZonificacionControllerTest.php
│   ├── Unit/
│   │   ├── Models/
│   │   │   ├── EstadoTest.php
│   │   │   ├── GiroTest.php
│   │   │   ├── NivelRiesgoTest.php
│   │   │   ├── TipoDocumentoIdentidadTest.php
│   │   │   ├── TipoLicenciaTest.php
│   │   │   ├── TipoProcedimientoTupaTest.php
│   │   │   ├── UnidadOrganicaTest.php
│   │   │   └── ZonificacionTest.php
│   │   └── Services/
│   │       ├── CalculadoraDiasHabilesTest.php
│   │       ├── DjangoJwtTokenServiceTest.php
│   │       └── DjangoPbkdf2HasherTest.php
│   └── Pest.php
│
├── vendor/
│
├── .editorconfig
├── .env
├── .env.example
├── .env.testing
├── .gitattributes
├── .gitignore
├── .npmrc
├── ARCHITECTURE.md
├── artisan
├── compose.yaml
├── composer.json
├── composer.lock
├── package.json
├── phpstan.neon
├── phpunit.xml
├── pint.json
├── README.md
└── vite.config.js
```

---

## 3. Patrón Arquitectónico por Módulo

### Patrón 1 — CRUD Simple (8 módulos)
**Módulos:** Estados, Giros, Tipos de Licencia, Niveles de Riesgo, Zonificaciones, Tipos Doc. Identidad, Tipos Proc. TUPA, Unidades Orgánicas

**Estructura:**
- 1 Model + 1 Controller + 2 FormRequests + 1 Resource = **5 archivos**

### Patrón 2 — CRUD + Service (2 módulos)
**Módulos:** Usuarios, Inspectores

**Estructura:**
- 1-2 Models + 1 Service + 1 Controller + 3 FormRequests + 1 Resource = **8-10 archivos**

### Patrón 3 — Service + Repository (4 módulos)
**Módulos:** Licencias, ITSE, Expedientes, Personas

**Estructura:**
- 2-4 Models + 1 Service + 1 Repository + 1 Controller + 3-7 FormRequests + 1-3 Resources = **15-20 archivos**

### Patrón 4 — Service DRY (1 módulo)
**Módulos:** Archivos

**Estructura:**
- 3 Models + 1 Service compartido + 1 Controller + 1 FormRequest + 1 Resource = **7 archivos**

### Patrón 5 — Servicios Externos (1 módulo)
**Módulos:** Pide (RENIEC + SUNAT)

**Estructura:**
- 2 Services + 1 Controller + 2 FormRequests + 3 Exceptions = **8 archivos**

### Patrón 6 — Auth / JWT (1 módulo)
**Módulos:** Seguridad

**Estructura:**
- 2 Guards (intactos) + 2 Services + 2 Controllers + 3 FormRequests + 1 Resource + 4 Exceptions = **14 archivos**

---

## 4. Endpoints Principales (119 rutas)

### Auth (4 rutas)
- `POST /api/auth/jwt/create/`
- `POST /api/auth/jwt/refresh/`
- `POST /api/auth/jwt/verify/`
- `GET /api/auth/users/me/`

### Módulos de Negocio (115 rutas bajo `/api/lf-itse/`)
- `/estados/`, `/giros/`, `/tipos-licencia/`, `/niveles-riesgo/`
- `/zonificaciones/`, `/tipos-documento-identidad/`, `/tipos-procedimiento-tupa/`
- `/unidades-organicas/`, `/usuarios/`, `/inspectores/`
- `/licencias-funcionamiento/`, `/itse/`, `/expedientes/`, `/personas/`
- `/reniec/consultar/`, `/sunat/consultar/`
- Archivos: `/expedientes/*/archivos/`, `/itse/*/archivos/`, `/licencias-funcionamiento/*/archivos/`

---

## 5. Verificación de Calidad

| Aspecto | Estado |
|---------|--------|
| **Tests** | 240 pasando (995 assertions) |
| **PHPStan Level 8** | 0 errores |
| **Pint** | 0 errores en `app/` |
| **Login admin** | ✅ Funciona sin reset (`admin`/`admin`) |
| **JWT SimpleJWT** | ✅ Claims idénticos |
| **Hasher pbkdf2** | ✅ Compatible Django |
| **Paridad API** | ✅ 100% byte-a-byte |

---

## 6. Documentación Relacionada

- [ARCHITECTURE.md](../../ARCHITECTURE.md) — Arquitectura MVC 2.0
- [README.md](../../README.md) — Guía de instalación y uso
- [_docs/ARCHITECTURE_MVC.md](../ARCHITECTURE_MVC.md) — Racional de la migración
- [_docs/reports/REFACTOR_MVC_CIERRE_FINAL.md](REFACTOR_MVC_CIERRE_FINAL.md) — Cierre del refactor
- [_docs/reports/AUDITORIA_FINAL_LIMPIEZA.md](AUDITORIA_FINAL_LIMPIEZA.md) — Limpieza ejecutada
