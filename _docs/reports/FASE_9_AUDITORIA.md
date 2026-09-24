# FASE 9 — Auditoría de Seguridad & Autenticación (SUB-GATE 9.0)

**Fecha:** 2026-09-24  
**Objetivo:** Mapear la arquitectura de autenticación y autorización entre Django, Frontend Vue y Laravel para planificar FASE 9.  
**Estado:** SUB-GATE 9.0 Completado  

---

## 1. Mapeo de Endpoints de `/api/auth/`

| Endpoint | Método | Request Body | Response OK (200/201) | Response Error (401/400) | ¿Frontend Vue lo consume? |
|---|:---:|---|---|---|:---:|
| `/api/auth/jwt/create/` | `POST` | `{"username": "...", "password": "..."}` | `{"access": "...", "refresh": "..."}` | 401 `{"detail": "La combinación de credenciales no tiene una cuenta activa"}` | ✅ **SÍ** (`AuthRepository.impl.ts`) |
| `/api/auth/jwt/refresh/` | `POST` | `{"refresh": "..."}` | `{"access": "...", "refresh": "..."}` | 401 `{"detail": "El token no es válido o ha expirado"}` | ⚠️ Opcional (interceptor redirige directo a `/login`) |
| `/api/auth/jwt/verify/` | `POST` | `{"token": "..."}` | `{}` | 401 `{"detail": "El token no es válido o ha expirado"}` | ⚠️ Contrato DRF/Djoser |
| `/api/auth/users/me/` | `GET` | *(Ninguno, usa Bearer token)* | `{"id": 1, "username": "admin", "email": "admin@correo.com"}` | 401 `{"detail": "Las credenciales de autenticación no se proveyeron."}` | ⚠️ Contrato DRF/Djoser (`getCurrentUser` retorna null en frontend) |
| `/api/auth/users/` | `POST` | `{"username": "...", "password": "..."}` | Datos de usuario | 400 Validación | ❌ No (usuarios se gestionan en `/api/lf-itse/usuarios/`) |

> **Hallazgo Clave de Frontend:**  
> En `frontend-reversed-pro_vue/src/infrastructure/repositories/AuthRepository.impl.ts`, el frontend realiza `POST /auth/jwt/create/`, decodifica el token JWT en el cliente vía base64 para extraer `id`, `username`, `role` y `ruc`, y guarda `{ user, token }` en `localStorage('auth-storage')`. En caso de 401 en cualquier llamada HTTP, el interceptor de Axios purga el storage y redirige de inmediato a `/login`.

---

## 2. Payload JWT Exacto (Django SimpleJWT + Custom Claims)

Inspección realizada en vivo contra Django REST Framework con el usuario `admin`:

### Access Token Payload
```json
{
  "token_type": "access",
  "exp": 1790271668,
  "iat": 1790268069,
  "jti": "bc0bf273cb5245acb057fa0cc251b5a0",
  "user_id": "1",
  "username": "admin",
  "role": "ADMIN_MUNICIPALIDAD",
  "ruc": null
}
```

### Refresh Token Payload
```json
{
  "token_type": "refresh",
  "exp": 1790872868,
  "iat": 1790268068,
  "jti": "3ee3b0cfc9c14e2aad6724eb6bc0adc1",
  "user_id": "1",
  "username": "admin",
  "role": "ADMIN_MUNICIPALIDAD",
  "ruc": null
}
```

### Reglas de Negocio de Claims
1. **`user_id`**: Es un `string` con el ID numérico del usuario (`"1"`, `"2"`), NO un entero ni el claim `sub` estándar de Tymon.
2. **`role`**:
   - Si `is_superuser == true` o `is_staff == true` ➔ `'ADMIN_MUNICIPALIDAD'`.
   - En otro caso ➔ `'CIUDADANO'`.
3. **`ruc`**: `null` (o valor asociado si existiera perfil extendido).
4. **Firma**: Algoritmo `HS256` utilizando la clave secreta `JWT_SECRET` / `SECRET_KEY` (`django-insecure-dev-key-change-in-production`).

---

## 3. Formato del Hash de Password (`pbkdf2_sha256`)

### Estructura de Django
```text
pbkdf2_sha256$<iteraciones>$<salt>$<base64_hash>
```

Ejemplo extraído de la tabla `auth_user` para `admin`:
- **Algoritmo:** `pbkdf2_sha256`
- **Iteraciones:** `1200000` (1.2M iteraciones)
- **Salt:** `bCyHs02f8l03epJzsd0N0D` (22 caracteres alfanuméricos planos)
- **Hash Base64:** `s+PXHa+tN/qJkDe7bnCgcCFghprjFNI2xLALtbqwW3U=` (44 caracteres)
- **Longitud total:** 89 caracteres

### Validación en PHP Nativo
Probado y validado en Tinker:
```php
$computedBinary = hash_pbkdf2('sha256', $password, $salt, $iterations, 0, true);
$computedB64 = base64_encode($computedBinary);
hash_equals($expectedHash, $computedB64); // true (100% MATCH)
```
Se implementará un `DjangoPbkdf2Hasher` que implemente `Illuminate\Contracts\Hashing\Hasher` o un servicio de verificación equivalente.

---

## 4. Estado Actual en Laravel

| Componente | Estado | Detalle |
|---|:---:|---|
| Guard `django-jwt` | ⚠️ Básico | Implementado en `App\Auth\DjangoJwtGuard`, valida firma HMAC con `Lcobucci\JWT` y busca por `user_id` o `sub`. Falta validar expiración (`exp`) y manejo de errores. |
| Hasher Django | ❌ Pendiente | Laravel usa Bcrypt por defecto. No existe hasher para `pbkdf2_sha256`. |
| `LoginController` | ❌ Stub | `App\Http\Controllers\Auth\LoginController::store` retorna admin hardcodeado sin validar password. |
| Endpoints `/api/auth/` | ⚠️ Parcial | Solo existe `POST /api/auth/jwt/create/` apuntando al stub. Faltan `/refresh`, `/verify`, `/users/me`. |
| Modelo `User` | ⚠️ Incompleto | `App\Models\User` tiene campos en fillable (`name`, `role`, `ruc`) que no existen en la tabla física `auth_user`. Debe limpiarse. |

---

## 5. Sub-gates Propuestos para FASE 9

| Sub-gate | Módulo / Tarea | Duración Est. | Entregables |
|---|---|:---:|---|
| **9.0** | Auditoría & Mapeo de Seguridad | Completado | `FASE_9_AUDITORIA.md`, análisis de contratos y tokens. |
| **9.1** | Domain & Contracts (Seguridad / Auth) | ~2h | `PasswordHasherInterface`, `TokenServiceInterface`, `UserRepositoryInterface`, DTOs de login y tokens. |
| **9.2** | Application (UseCases de Auth) | ~2.5h | `AutenticarUsuarioUseCase`, `RefrescarTokenUseCase`, `VerificarTokenUseCase`, `ObtenerUsuarioAutenticadoUseCase`. |
| **9.3** | Infrastructure (Hasher + JWT + Repos) | ~3h | `DjangoPbkdf2Hasher`, `SimpleJwtTokenService`, `EloquentUserRepository`, optimización de `DjangoJwtGuard`. |
| **9.4** | Presentation (Controllers + Rutas `/api/auth/`) | ~2h | `AuthController` con 4 métodos (`create`, `refresh`, `verify`, `me`), middlewares y tests feature. |
| **9.5** | Diff de Paridad con Django | ~2h | Comparación byte-a-byte de tokens, códigos de error (401), headers y respuestas contra Django real. |
| **9.6** | Cierre Formal de FASE 9 | ~1h | Suite completa (100% pasando), PHPStan Level 8, Pint, `FASE_9_CIERRE.md`. |

---

## 6. Riesgos Críticos y Mitigaciones

1. **Incompatibilidad de Hashing:**
   - *Riesgo:* Que un usuario no pueda autenticarse por diferencias en el salt o codificación base64.
   - *Mitigación:* Comprobado con éxito en el Tinker de Laravel con el usuario `admin` existente (`s+PXHa...`).
2. **Claims de JWT esperados por el Frontend:**
   - *Riesgo:* Frontend Vue espera `user_id`, `role`, `ruc` en el payload JWT. Si faltan, el frontend no sabrá qué permisos tiene o fallará la redirección.
   - *Mitigación:* Generación de tokens en `SimpleJwtTokenService` con la estructura idéntica de SimpleJWT.
3. **Manejo de Respuestas de Error (401):**
   - *Riesgo:* DRF retorna `{"detail": "..."}` en error de login y token inválido. Si Laravel retorna `{"message": "..."}`, el frontend podría mostrar mensajes vacíos.
   - *Mitigación:* Presentation Controller devolverá `{"detail": "..."}` idéntico a SimpleJWT.
