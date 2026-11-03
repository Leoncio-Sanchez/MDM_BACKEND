# Despliegue en producción — Backend LF-ITSE

Para un servidor Ubuntu con Docker Compose donde un `nginx-proxy` con `acme-companion` ya atiende los puertos 80 y 443,
y las apps se conectan a él por la red externa de Docker `web`. La imagen se construye en el mismo servidor.

| Servicio | Contenedor | Red | Memoria máxima |
|---|---|---|---|
| Nginx + PHP-FPM 8.4 (sin root) | `mdm_app` | `interna` y `web` | 1 GB |
| PostgreSQL 16 | `mdm_pgsql` | `interna` (sin salida a internet) | 1 GB |
| Redis 7 con contraseña y AOF | `mdm_redis` | `interna` (sin salida a internet) | 256 MB |

Ningún servicio publica puertos. Todos los comandos de esta guía se corren desde la carpeta del repositorio.

```bash
alias dcp='docker compose -f compose.prod.yaml'
```

## 1. Instalación

### Configurar el `.env`

```bash
cp .env.production.example .env
chmod 600 .env
openssl rand -hex 32                        # DB_PASSWORD
openssl rand -hex 32                        # REDIS_PASSWORD
openssl rand -hex 64                        # JWT_SECRET
echo "base64:$(openssl rand -base64 32)"    # APP_KEY
docker network inspect web -f '{{(index .IPAM.Config 0).Subnet}}'   # TRUSTED_PROXIES
```

Use esos generadores: Docker Compose interpreta el signo `$` del `.env`, y una contraseña que lo lleve se altera.

**Variables obligatorias** (sin ellas, Compose o la aplicación no arrancan):

| Variable | Valor |
|---|---|
| `VIRTUAL_HOST`, `LETSENCRYPT_HOST` | Dominio de la API |
| `LETSENCRYPT_EMAIL` | Correo para el certificado |
| `APP_URL` | `https://` + dominio de la API |
| `APP_KEY`, `JWT_SECRET` | Generadas arriba |
| `TRUSTED_PROXIES` | Subred de la red `web` |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | El ejemplo trae `mdm_lf_itse` y `mdm_app`; la contraseña, generada arriba |
| `REDIS_PASSWORD` | Generada arriba |

**Según el caso:**

| Variable | Cuándo |
|---|---|
| `CORS_ALLOWED_ORIGINS` | Necesaria si el frontend está en otro dominio: sin ella, el navegador bloquea la API. `*` se ignora |
| `RENIEC_*` | Para consultar DNI. Vacías, la consulta falla y los datos se escriben a mano |
| `ITSE_ACTA_OBLIGATORIA_DESDE` | La define la municipalidad. Vacía, el certificado ITSE exige acta Aprobada a todos los expedientes |

`DB_PASSWORD` y `REDIS_PASSWORD` solo se aplican al crear los volúmenes. Cambiarlas después exige cambiarlas también
dentro de PostgreSQL.

### Permitir la subida de adjuntos en `nginx-proxy`

**Hágalo antes de levantar `mdm_app`.** Sin esto, `nginx-proxy` rechaza con 413 cualquier archivo desde 1 MB.

Cree, en la carpeta `vhost.d` de `nginx-proxy`, un archivo con el nombre exacto de `VIRTUAL_HOST`:

```nginx
client_max_body_size 110m;
```

El valor sale de un acta con 10 fotos de 10 MB cada una en un solo pedido. `nginx-proxy` lee el archivo cuando genera su
configuración, al arrancar o recrearse un contenedor. Si se crea después, basta con recrear `mdm_app`
(`dcp up -d --force-recreate --no-deps app`), sin reiniciar `nginx-proxy` ni afectar a las otras apps.

### Construir y levantar

```bash
dcp build
dcp up -d
dcp ps          # esperar a que los tres digan "healthy"
```

### Migrar y crear el administrador

Las migraciones no corren solas. **En este orden**, porque los catálogos referencian al usuario con id 1:

```bash
dcp exec app php artisan migrate --force
dcp exec -it app php artisan usuarios:administrador NOMBRE_DE_USUARIO --nombre="Nombres Apellidos"
dcp exec app php artisan db:seed --class=CatalogosSeeder --force
```

`usuarios:administrador` pide la contraseña sin mostrarla (mínimo 12 caracteres). Debe mostrar `id 1`.

`db:seed` sin `--class` está bloqueado en producción: `DatabaseSeeder` crea un administrador con contraseña conocida.

### Comprobar

```bash
curl -s -o /dev/null -w '%{http_code}\n' https://DOMINIO/up                  # 200
curl -s -o /dev/null -w '%{http_code}\n' https://DOMINIO/api/documentation   # 404
```

Luego, entrar desde el frontend, subir un adjunto y generar el informe de licencias en PDF.

## 2. Actualización

```bash
git pull
dcp build
# Respaldar antes de migrar (ver sección 3)
dcp up -d
dcp exec app php artisan migrate --force
```

Al arrancar, el contenedor vuelve a cachear configuración, rutas y vistas. Los adjuntos, la base y Redis están en
volúmenes y no se tocan. Un cambio en el `.env` se aplica con `dcp up -d`. El corte dura lo que tarda en recrearse
`mdm_app`, unos segundos.

## 3. Datos y respaldos

| Qué | Dónde |
|---|---|
| Adjuntos y fotos de actas | Volumen `mdm_media`, en `/var/www/media` dentro de `mdm_app` (`MEDIA_DISK_ROOT`, no cambiar) |
| Base de datos | Volumen `mdm_pgsql_data`. Base `mdm_lf_itse`, usuario `mdm_app` (valores del ejemplo) |
| Redis (caché y límites de intentos) | Volumen `mdm_redis_data`. Se puede perder sin daño |

**`dcp down -v` borra los adjuntos y la base.** Nunca lo use en producción. `dcp down` sin `-v` los conserva.

`scripts/backup_pg_morales.sh` respalda la base y los adjuntos, y rota las copias. Como root, en cron:

```cron
0 1 * * * DB_CONTAINER=mdm_pgsql DB_NAME=mdm_lf_itse DB_USER=mdm_app MEDIA_DIR=/var/lib/docker/volumes/mdm_media/_data /ruta/al/repo/scripts/backup_pg_morales.sh
```

Confirme la ruta con `docker volume inspect mdm_media -f '{{.Mountpoint}}'`. Las copias quedan en el mismo servidor: hay
que sincronizarlas a otro equipo.

## 4. Cómo funciona detrás de `nginx-proxy`

- **IP real y HTTPS.** Laravel acepta `X-Forwarded-For` y `X-Forwarded-Proto` solo si el pedido viene de `TRUSTED_PROXIES`.
  Sin esa variable, no detecta HTTPS y todos los usuarios comparten un solo límite de intentos. Si la red `web` se
  recrea con otra subred, hay que actualizarla.
- **`X-Forwarded-Host` no se acepta**, porque `nginx-proxy` deja pasar el que envíe el cliente. El `Host` ya es real.
- **Límites de intentos**, en Redis y por IP real:

  | Endpoint | Por minuto |
  |---|---|
  | `POST /api/auth/jwt/create/` | 5 por usuario e IP; 20 por IP |
  | `POST /api/publico/cuenta/*` | 5 por documento e IP; 20 por IP |
  | `GET /api/publico/consulta/` | 10 por IP |

  Reemplazan al límite de login de `gateway/nginx.conf`, que aquí no se usa (5 por segundo por IP) y son más estrictos.
  El límite general de 30 pedidos por segundo de ese archivo no tiene reemplazo.
- **Nombres de host.** `DB_HOST` y `REDIS_HOST` usan `mdm_pgsql` y `mdm_redis`, no `pgsql` y `redis`: `mdm_app` también
  está en la red compartida, y otra app con un servicio llamado `redis` en `web` podría responder en su lugar.
- **Colas y tareas programadas.** El código no despacha trabajos, no envía correos y `routes/console.php` no programa
  tareas, así que no hay servicios `worker` ni `scheduler`. Si se agregan, hay que crearlos.

## 5. Problemas frecuentes

| Síntoma | Causa |
|---|---|
| 413 al subir un archivo | Falta el archivo de `vhost.d` (ver sección 1) |
| 502 o 503 de `nginx-proxy` | `mdm_app` no está en la red `web`, o sigue arrancando: `dcp ps`, `dcp logs app` |
| El navegador informa un error de CORS | `CORS_ALLOWED_ORIGINS` no incluye el dominio exacto del frontend, con `https://` |
| Login responde 500 | Revisar Redis: sin él fallan los límites de intentos. `/up` responde 200 de todos modos, porque no revisa Redis ni la base |
| Todos reciben 429 a la vez | `TRUSTED_PROXIES` no coincide con la subred de `web` |

Logs: `dcp logs -f app`. Docker los rota: 5 archivos de 10 MB por servicio.
