# Reporte de Auditoría Final y Limpieza del Backend

**Fecha:** 25 de Septiembre de 2026  
**Rama:** `arq-exg`  
**Estado:** ✅ Limpieza y Verificación Completada

---

## 1. Archivos Conservados (Legítimos del Proyecto)

| Archivo / Carpeta | Tipo / Propósito | Justificación |
|---|---|---|
| `app/` | Código fuente MVC | Toda la lógica, modelos, controladores y servicios |
| `bootstrap/` | Arranque del Framework | `app.php` y `providers.php` |
| `config/` | Configuración | Parámetros de JWT, BD, PIDE, cors, etc. |
| `database/` | Migraciones y Seeders | Esquema relacional y datos de prueba |
| `public/` | Web Root | `index.php` y assets públicos |
| `resources/` | Vistas y Assets | Blade templates y estilos base |
| `routes/` | Enrutamiento | `api.php`, `web.php`, `console.php` |
| `storage/` | Almacenamiento | Logs, caché y archivos temporales |
| `tests/` | Suite de Pruebas | 240 tests pasando en Pest (Unit y Feature) |
| `_docs/` | Documentación | Reportes históricos, auditorías e incidentes |
| `.editorconfig`, `.gitattributes`, `.gitignore` | Configuración de Git | Estándares de desarrollo |
| `.env`, `.env.example`, `.env.testing` | Variables de Entorno | Configuración del entorno |
| `artisan` | CLI de Laravel | Herramienta de consola de Laravel |
| `composer.json`, `composer.lock` | Dependencias PHP | Gestión de paquetes PHP |
| `compose.yaml` | Docker Sail | Orquestación de contenedores (app, pgsql, redis) |
| `package.json`, `vite.config.js` | Herramientas Frontend | Compilación de assets con Vite y Tailwind |
| `.npmrc` | Seguridad Node/NPM | `ignore-scripts=true` y `audit=true` |
| `phpstan.neon`, `phpunit.xml`, `pint.json` | Calidad de Código | Configuraciones de linters y análisis estático |
| `README.md`, `ARCHITECTURE.md` | Documentación | Guías principales del repositorio |

---

## 2. Archivos Eliminados (Basura de Desarrollo y Módulos Obsoletos)

| Archivo / Directorio | Razón de la Eliminación |
|---|---|
| `fix_mig.php` | Script temporal de reemplazo de migraciones |
| `fix_phpstan.php` | Script temporal de ajustes para phpstan |
| `fix_zonificacion_auth.php` | Script temporal de prueba para autenticación |
| `fix.php` | Script temporal de prueba de formato de fechas |
| `generate_nivel_riesgo.php` | Script de generación de código hexagonal inicial |
| `generate_zonificacion.php` | Script de generación de código hexagonal inicial |
| `update_personas_migration.py` | Script Python residual |
| `update_user.php` | Script temporal para pruebas en User |
| `vite-module-loader.js` | Archivo no referenciado en la configuración de Vite |
| `stubs/` (`nwidart-stubs/`) | Plantillas residuales del paquete modular ya no utilizado |
| `routes/auth.php` | Archivo residual no utilizado por el framework |
| `app/Http/` | Namespace obsoleto reemplazado por la estructura plana en `app/` |

---

## 3. Archivos de IA Excluidos

| Archivo | Acción |
|---|---|
| `AGENTS.md` | Desindexado de Git (`git rm --cached`) y agregado a `.gitignore` |
| `CLAUDE.md` | Desindexado de Git (`git rm --cached`) y agregado a `.gitignore` |

---

## 4. Estado Final de la Estructura de `app/`

Tras la limpieza, el directorio `app/` quedó estructurado de forma 100% limpia y uniforme:

```text
app/
├── Auth/              (2 archivos: DjangoJwtGuard.php, DjangoJwtUserProvider.php)
├── Controllers/       (19 archivos: 17 controladores de módulo + 2 en Auth/)
├── Exceptions/        (7 archivos en Pide/ y Seguridad/)
├── Models/            (26 modelos Eloquent)
├── Providers/         (1 archivo: AppServiceProvider.php)
├── Repositories/      (4 repositorios para SQL crudo masivo)
├── Requests/          (52 FormRequests agrupados por módulo)
├── Resources/         (25 JsonResources con formato exacto Django)
├── Services/          (14 servicios y DTOs de negocio)
└── Shared/            (2 utilitarios compartidos)
```

**Total de archivos PHP en `app/`:** 152 archivos organizados lógicamente sin duplicidades ni capas innecesarias.
