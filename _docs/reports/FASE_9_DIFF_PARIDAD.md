# FASE 9 — Diff de Paridad con Django (Seguridad)

**Fecha:** 2026-09-24  
**Sub-gate:** 9.5  
**Ambiente:** Django 5.x (`:8000`) vs Laravel 11 (`:8001`)  

---

## 1. Escenarios Validados

| # | Escenario | Django | Laravel | Diff |
|---|-----------|:------:|:-------:|:----:|
| 1 | Login OK (claims JWT) | 200 | 200 | ✅ Idénticos (excepto iat/exp/jti) |
| 2 | Login password incorrecta | 401 | 401 | ✅ Idéntico byte-a-byte |
| 3 | Login usuario inexistente | 401 | 401 | ✅ Idéntico byte-a-byte |
| 4 | Refresh token (rotación) | 200 | 200 | ✅ Idéntico (emite nuevo par access + refresh) |
| 5 | `GET /api/auth/users/me/` | 200 | 200 | ✅ Idéntico (3 keys exactas: email, id, username) |
| 6 | `POST /api/auth/jwt/verify/` | 200 `{}` | 200 `{}` | ✅ Idéntico (objeto JSON vacío) |

---

## 2. Claims del JWT (`access`)

| Claim | Django SimpleJWT | Laravel JWT | Comparación |
|---|---|---|:---:|
| `token_type` | `"access"` | `"access"` | ✅ Idéntico |
| `user_id` | `"1"` (string) | `"1"` (string) | ✅ Idéntico |
| `username` | `"admin"` | `"admin"` | ✅ Idéntico |
| `role` | `"ADMIN_MUNICIPALIDAD"` | `"ADMIN_MUNICIPALIDAD"` | ✅ Idéntico |
| `ruc` | `null` | `null` | ✅ Idéntico |
| `iat` | Unix timestamp | Unix timestamp | Volátil (tiempo real) |
| `exp` | Unix timestamp | Unix timestamp | Volátil (tiempo real) |
| `jti` | 32-char hex string | 32-char hex string | Volátil (aleatorio) |

---

## 3. Manejo de Errores (401)

Tanto con password incorrecta como con usuario inexistente, ambos sistemas responden exactamente igual:
```json
{"detail": "La combinación de credenciales no tiene una cuenta activa"}
```
Con código HTTP `401 Unauthorized`. Esto previene ataques de enumeración de usuarios.

---

## 4. Conclusión

✅ **Autenticación 100% compatible con Django.**
* Los usuarios existentes de la tabla `auth_user` pueden autenticarse inmediatamente sin resetear contraseña (hashing `pbkdf2_sha256` 100% interoperable).
* El frontend Vue (`AuthRepository.impl.ts` y Axios interceptor) opera de forma idéntica sin requerir ninguna modificación.
