# Reporte de Auditoría Técnica — FASE 8A: PIDE (RENIEC + SUNAT)

## 1. Resumen Ejecutivo

Este documento formaliza la auditoría técnica de los servicios de interoperabilidad con la Plataforma de Interoperabilidad del Estado (**PIDE**) para las consultas a **RENIEC** y **SUNAT** en el backend Django (`backend/app_lf_itse`), con el objetivo de planificar su migración idéntica a Laravel 11 (`backend-php`).

---

## 2. Tabla de Endpoints Auditados

| Endpoint | Método | Query Params | Auth | Response | Fuente Upstream |
|---|---|---|---|---|---|
| `/api/lf-itse/reniec/consultar/` | `GET` | `dni` (8 dígitos numéricos) | JWT (`IsAuthenticated`) | JSON crudo (`consultarResponse`) | PIDE RENIEC (`ws2.pide.gob.pe`) |
| `/api/lf-itse/sunat/consultar/` | `GET` | `ruc` (11 dígitos numéricos) | JWT (`IsAuthenticated`) | JSON crudo (`getDatosPrincipalesResponse`) | PIDE SUNAT (`ws3.pide.gob.pe`) |

Ambos endpoints actúan como passthrough transparente de la respuesta JSON emitida por PIDE, aplicando validación de formato local y mapeando errores a códigos HTTP estándar (`400`, `502`, `500`).

---

## 3. Contrato de RENIEC

### Protocolo y Endpoint Upstream
- **Protocolo:** HTTP REST (`GET`) sobre HTTPS. **No es SOAP ni XML**.
- **URL Base:** `https://ws2.pide.gob.pe/Rest/RENIEC/Consultar`
- **Query String Upstream:**
  ```text
  ?nuDniConsulta={dni}&nuDniUsuario={dni_usuario}&nuRucUsuario={ruc_usuario}&password={password}&out=json
  ```

### Variables de Configuración y Credenciales
- `RENIEC_NUDNIUSUARIO`: DNI del usuario operador autorizado por la entidad.
- `RENIEC_NURUCUSUARIO`: RUC de la entidad solicitante (Municipalidad).
- `RENIEC_PASSWORD`: Clave asignada por PIDE/RENIEC.
- `RENIEC_TIMEOUT`: Tiempo límite de espera en segundos (por defecto `10`).

### Validaciones Locales
1. Parámetro `dni` presente y no vacío tras `trim()`. De lo contrario:
   - **HTTP 400 Bad Request:** `{"error": "El parámetro 'dni' es obligatorio."}`
2. Longitud y formato: `dni.isdigit() and len(dni) == 8`. De lo contrario:
   - **HTTP 400 Bad Request:** `{"error": "El DNI debe tener exactamente 8 dígitos numéricos."}`
3. Credenciales completas (`dni_usuario`, `ruc_usuario`, `password`). Si alguna falta:
   - **HTTP 502 Bad Gateway:** `{"error": "Las credenciales RENIEC no están configuradas (RENIEC_NUDNIUSUARIO, RENIEC_NURUCUSUARIO, RENIEC_PASSWORD)."}`

### Manejo de Errores Upstream
- **Timeout (`requests.exceptions.Timeout`):**
  - **HTTP 502 Bad Gateway:** `{"error": "El servicio RENIEC no respondió en el tiempo esperado."}`
- **Falla de conexión (`requests.exceptions.ConnectionError`):**
  - **HTTP 502 Bad Gateway:** `{"error": "No se pudo conectar con el servicio RENIEC."}`
- **Error HTTP (`requests.exceptions.HTTPError`):**
  - **HTTP 502 Bad Gateway:** `{"error": "Error HTTP del servicio RENIEC: {e}"}`
- **Excepción inesperada:**
  - **HTTP 500 Internal Server Error:** `{"error": "{e}"}`

---

## 4. Contrato de SUNAT

### Protocolo y Endpoint Upstream
- **Protocolo:** HTTP REST (`GET`) sobre HTTPS. **No es SOAP ni XML**.
- **URL Base:** `https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales`
- **Query String Upstream:**
  ```text
  ?numruc={ruc}&out=json
  ```

### Variables de Configuración y Credenciales
- SUNAT no requiere credenciales de usuario/password en este endpoint PIDE (`numruc` y `out=json`).
- Timeout: 10 segundos fijado por constante (`_TIMEOUT = 10`).

### Validaciones Locales
1. Parámetro `ruc` presente y no vacío tras `trim()`. De lo contrario:
   - **HTTP 400 Bad Request:** `{"error": "El parámetro 'ruc' es obligatorio."}`
2. Longitud y formato: `ruc.isdigit() and len(ruc) == 11`. De lo contrario:
   - **HTTP 400 Bad Request:** `{"error": "El RUC debe tener exactamente 11 dígitos numéricos."}`

### Manejo de Errores Upstream
- **Timeout (`requests.exceptions.Timeout`):**
  - **HTTP 502 Bad Gateway:** `{"error": "El servicio SUNAT no respondió en el tiempo esperado."}`
- **Falla de conexión (`requests.exceptions.ConnectionError`):**
  - **HTTP 502 Bad Gateway:** `{"error": "No se pudo conectar con el servicio SUNAT."}`
- **Error HTTP (`requests.exceptions.HTTPError`):**
  - **HTTP 502 Bad Gateway:** `{"error": "Error HTTP del servicio SUNAT: {e}"}`
- **Excepción inesperada:**
  - **HTTP 500 Internal Server Error:** `{"error": "{e}"}`

---

## 5. Decisiones de Arquitectura para Laravel 11

1. **Cliente HTTP:**
   - Usar el cliente HTTP nativo de Laravel `Illuminate\Support\Facades\Http` (respaldado por `GuzzleHttp\Client` v8.2 ya presente en `composer.json`).
2. **Módulo Dedicado:**
   - Crear el módulo `app/Modules/Pide/` con arquitectura limpia por capas:
     - `Domain/Contracts/`: `ReniecClientInterface`, `SunatClientInterface`.
     - `Domain/Exceptions/`: `ReniecException`, `SunatException`, `CredencialesReniecNoConfiguradasException`, `ValidacionPideException`.
     - `Infrastructure/External/`: `HttpReniecClient`, `HttpSunatClient`.
     - `Infrastructure/Providers/`: `PideServiceProvider`.
     - `Application/UseCases/`: `ConsultarReniecUseCase`, `ConsultarSunatUseCase`.
     - `Presentation/Controllers/`: `ReniecController`, `SunatController` (o unificado `PideController`).
3. **Estrategia de Cache y Reintentos:**
   - **Cache:** NO se implementa. Django no usa cache en estos endpoints; la consulta refleja el estado real del padrón en PIDE.
   - **Retry:** NO se implementa. Django no reintenta; ante fallas de red se falla rápido con 502 para no degradar la experiencia de usuario en formularios interactivos.
4. **Configuración en `config/services.php`:**
   - Completar las entradas para `reniec` y añadir `sunat`:
     ```php
     'reniec' => [
         'url' => env('RENIEC_URL', 'https://ws2.pide.gob.pe/Rest/RENIEC/Consultar'),
         'dni_usuario' => env('RENIEC_NUDNIUSUARIO', ''),
         'ruc_usuario' => env('RENIEC_NURUCUSUARIO', ''),
         'password' => env('RENIEC_PASSWORD', ''),
         'timeout' => (int) env('RENIEC_TIMEOUT', 10),
     ],
     'sunat' => [
         'url' => env('SUNAT_URL', 'https://ws3.pide.gob.pe/Rest/Sunat/DatosPrincipales'),
         'timeout' => (int) env('SUNAT_TIMEOUT', 10),
     ],
     ```

---

## 6. Riesgos Identificados y Mitigaciones

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Caída del servicio PIDE del Estado | Timeout o error HTTP 502 | Excepciones capturadas y mapeadas a 502 con los mensajes exactos de Django |
| Falta de credenciales en producción | Falla de consulta RENIEC | Validación temprana arrojando 502 explícito indicando las variables faltantes |
| Invocaciones reales en suite de pruebas | Dependencia de red externa y lentitud | Uso exclusivo de `Http::fake()` en Feature tests |
| Discrepancia en parsing de JSON | Errores en frontend | Passthrough del array asociativo devuelto por `$response->json()` |

---

## 7. Plan de Sub-Gates de FASE 8A

- **SUB-GATE 8A.0:** Auditoría de PIDE (RENIEC + SUNAT) — *(Completado)*
- **SUB-GATE 8A.1:** Infrastructure (Adapters HTTP + Configuración en `config/services.php` + ServiceProvider)
- **SUB-GATE 8A.2:** Application (2 UseCases + DTOs / Reglas de validación)
- **SUB-GATE 8A.3:** Presentation (Controller + 2 rutas con prefijo `api/lf-itse/`)
- **SUB-GATE 8A.4:** Tests completos con `Http::fake()` (casos exitosos, 400, 502, 500)
- **SUB-GATE 8A.5:** Cierre formal de FASE 8A + Reporte
