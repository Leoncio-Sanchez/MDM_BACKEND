# Backend PHP (Laravel 11) — Municipalidad LF ITSE

Backend REST API en Laravel 11 con arquitectura **MVC Plano**, diseñado para paridad estricta y reemplazo transparente del backend original en Django/DRF.

---

## 🚀 Requisitos

- **Docker & Docker Compose** (gestión completa vía **Laravel Sail**).
- Entorno de desarrollo: **PHP 8.5**, **PostgreSQL 18**, **Redis Alpine**.
- *(Opcional)* Node.js / npm para assets estáticos.

---

## ⚙️ Instalación y Puesta en Marcha

```bash
# 1. Iniciar los contenedores Sail en segundo plano
./vendor/bin/sail up -d

# 2. Instalar dependencias de PHP (si es necesario)
./vendor/bin/sail composer install

# 3. Copiar archivo de entorno y generar clave (si es primera vez)
cp .env.example .env
./vendor/bin/sail artisan key:generate

# 4. Ejecutar migraciones
./vendor/bin/sail artisan migrate
```

---

## 🧪 Comandos de Calidad y Tests

```bash
# Ejecutar suite de pruebas completa (240 tests passing)
./vendor/bin/sail pest

# Análisis estático en Nivel 8 (0 errores)
./vendor/bin/sail bin phpstan analyse --level=8 app/

# Formateo y validación de estándares de código
./vendor/bin/sail bin pint --test
./vendor/bin/sail bin pint
```

---

## 🏛️ Estructura del Código (MVC Plano)

El proyecto utiliza una arquitectura **MVC minimalista y directa** en `app/`:

```text
app/
├── Auth/              # Guards y UserProviders custom compatibles con SimpleJWT
├── Controllers/       # Controladores delgados por módulo (+ Auth/)
├── Exceptions/        # Manejo centralizado de excepciones por dominio
├── Models/            # Modelos Eloquent unificados
├── Providers/         # ServiceProviders globales
├── Repositories/      # Consultas SQL complejas (>30 líneas / CTEs)
├── Requests/          # FormRequests con validación exacta HTTP 400
├── Resources/         # JsonResources con formato y orden exacto de Django
└── Services/          # Lógica de negocio, workflows y clientes externos
    └── External/      # Integraciones PIDE (RENIEC y SUNAT)
```

---

## 📡 Endpoints Principales

- **Autenticación (JWT Djoser):**
  - `POST /api/auth/jwt/create/` (Login y emisión de par `access`/`refresh`)
  - `POST /api/auth/jwt/refresh/` (Rotación de tokens)
  - `POST /api/auth/jwt/verify/` (Verificación de validez)
  - `GET /api/auth/users/me/` (Datos del usuario autenticado)
- **Módulos Municipales (`/api/lf-itse/`):**
  - `/licencias-funcionamiento/` (CRUD, búsqueda, reportes, histórico de estados)
  - `/itse/` (CRUD, inspecciones, asignación de inspectores)
  - `/expedientes/` (Gestión de expedientes, ampliación de plazos, cálculo de días hábiles)
  - `/personas/` (Búsqueda, documentos de identidad, registro)
  - `/archivos/` (Descarga y gestión documental unificada)
  - `/reniec/consultar/` & `/sunat/consultar/` (Passthrough PIDE)
  - Catálogos: `estados/`, `giros/`, `tipos-licencia/`, `niveles-riesgo/`, `zonificaciones/`, etc.

---

## 📚 Documentación Adicional

- [Arquitectura Detallada (MVC 2.0)](ARCHITECTURE.md)
- [Auditoría y Justificación del Refactor MVC](_docs/ARCHITECTURE_MVC.md)
- [Reporte de Cierre Final del Refactor (18/18 Módulos)](_docs/reports/REFACTOR_MVC_CIERRE_FINAL.md)
- [Auditoría Final de Limpieza de Archivos](_docs/reports/AUDITORIA_FINAL_LIMPIEZA.md)
