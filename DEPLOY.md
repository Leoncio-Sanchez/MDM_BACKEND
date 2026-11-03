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
curl -s -o /dev/null -w '%{http_code}\n' https://DOMINIO/                    # 404
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

## 4. Importar los datos del sistema anterior (Django)

**Este procedimiento no está probado con datos reales**, porque no se dispone de una copia de producción. Se ensayó con
`BD/2026.sql`, que solo trae catálogos, 4 personas y ningún expediente, más un expediente y un adjunto de prueba. Vea
[qué falta probar](#qué-falta-probar) antes de usarlo, y ensáyelo primero en otro equipo con una copia real.

Reemplaza a "Migrar y crear el administrador" de la sección 1: la base debe estar **vacía**. Si ya se migró o se usó para
una demo, no restaure encima. Los dumps tienen datos de ciudadanos: guárdelos fuera del repositorio, con `chmod 600`.

### 1. Sacar la copia del sistema anterior

Detenga antes las escrituras en Django, para que la base y los adjuntos queden iguales:

```bash
pg_dump -Fc --no-owner --no-acl -d BASE_DE_DJANGO -f lf_itse.dump
tar -C RUTA_DE_MEDIA_ROOT -czf lf_itse_media.tgz .
```

### 2. Restaurar la base

```bash
dcp up -d pgsql redis
dcp exec -T pgsql sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -c "\dt"'     # "Did not find any relations."
docker run --rm -i postgres:17-alpine pg_restore -f - --no-owner --no-acl < lf_itse.dump \
  | grep -av '^SET transaction_timeout' \
  | dcp exec -T pgsql sh -c 'psql -q -v ON_ERROR_STOP=1 --single-transaction -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
```

El contenedor `postgres:17-alpine` debe tener una versión igual o mayor que el `pg_dump` que creó la copia: `mdm_pgsql` es
PostgreSQL 16 y no lee directamente una copia hecha con la 17 (`unsupported version in file header`). Por eso se convierte
a SQL y se quita `transaction_timeout`, que la 16 no conoce. Si `psql` falla, nada queda aplicado.

### 3. Revisar los datos antes de migrar

```bash
dcp exec -T pgsql sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"' <<'SQL'
SELECT count(*) AS personas_sin_sexo FROM personas WHERE sexo IS NULL OR trim(sexo) = '';
SELECT count(*) AS numeros_repetidos_en_un_anio FROM (SELECT 1 FROM expedientes
  GROUP BY numero_expediente, EXTRACT(YEAR FROM fecha_recepcion AT TIME ZONE 'America/Lima') HAVING count(*) > 1) r;
SELECT count(*) AS personal_sin_perfil FROM auth_user u
  WHERE is_staff AND NOT EXISTS (SELECT 1 FROM usuarios_perfiles p WHERE p.user_id = u.id);
SELECT table_name, column_name FROM information_schema.columns
  WHERE table_schema = 'public' AND table_name = 'tipos_procedimiento_tupa' AND column_name = 'monto'
    AND is_nullable = 'NO' AND column_default IS NULL;
SQL
```

Todo debe dar 0 filas o 0. Si no:

| Resultado | Efecto | Qué hacer |
|---|---|---|
| `personas_sin_sexo` | La lista de personas responde **500** (comprobado) | Decidir con la municipalidad con qué valor completarlo |
| `numeros_repetidos_en_un_anio` | El número deja de identificar al expediente de ese año | Revisar cada caso con Mesa de Partes |
| `personal_sin_perfil` | Esa cuenta entra sin accesos | Asignarlos en Usuarios y Permisos |
| Fila con `monto` | La copia tiene esa columna, que el código no conoce, como obligatoria y sin valor por defecto: crear un procedimiento TUPA fallaría | Decidir antes de abrir el sistema |

### 4. Migrar y levantar

```bash
dcp up -d
dcp exec app php artisan migrate --force
```

Las migraciones detectan la base existente: no recrean las tablas heredadas, solo agregan las nuevas. **No corra
`CatalogosSeeder`**: los catálogos ya vienen en la copia. Las cuentas conservan su contraseña, porque Laravel lee el formato
de Django. Con un `JWT_SECRET` nuevo, todos deben volver a iniciar sesión. `usuarios:administrador` solo hace falta si nadie conoce una cuenta de
administrador heredada; no tendrá el id 1, y no es un problema porque ese id ya existe.

### 5. Copiar los adjuntos

Con el `.tgz` descomprimido en una carpeta `media_django`:

```bash
dcp exec app id     # debe mostrar uid=82; si no, cambie 82 en el comando siguiente
docker run --rm -v mdm_media:/destino -v "$PWD/media_django":/origen:ro alpine:3.20 \
  sh -c 'cp -a /origen/. /destino/ && chown -R 82:82 /destino'
```

`ruta_archivo` se resuelve desde `/var/www/media`. Este comando lista los que no se encuentran y no debe mostrar nada:

```bash
dcp exec -T pgsql sh -c 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB" -tA -c "SELECT ruta_archivo FROM expedientes_archivos UNION ALL SELECT ruta_archivo FROM itse_archivos UNION ALL SELECT ruta_archivo FROM licencias_funcionamiento_archivos"' \
  | dcp exec -T app sh -c 'while IFS= read -r r; do test -f "/var/www/media/$r" || echo "FALTA: $r"; done'
```

### 6. Comprobar

Haga las comprobaciones de la sección 1 y además, con una cuenta heredada: compare con Django los totales de expedientes,
licencias e ITSE, abra un trámite antiguo de cada tipo, descargue un adjunto de cada tipo y genere el informe en PDF de un
mes con datos. Mantenga Django detenido, pero disponible, hasta terminar: es la forma de volver atrás.

### Qué falta probar

| Falta | Por qué importa |
|---|---|
| Una copia real de producción | `BD/2026.sql` no tiene expedientes, licencias ni ITSE. No se sabe si los datos reales cumplen las reglas nuevas |
| Adjuntos reales | No hay ninguno disponible. El ensayo usó una ruta `expedientes/ID/UUID.pdf`: si Django guardó otro formato en `ruta_archivo`, la comprobación del paso 5 lo mostrará |
| Iniciar sesión con una cuenta real | Se probó un hash con el formato de Django, creado para el ensayo |
| Datos en `inspectores`, `itse_inspectores` y `auditlog_logentry` | Están en `BD/2026.sql` y este sistema no las usa: se conservan, pero no aparecen en pantalla ni pasan a las actas nuevas. No están en `BD/data-production` |
| El esquema real | `BD/2026.sql` y `BD/data-production` no coinciden entre sí. El código coincide con `data-production`, y `2026.sql` tiene además columnas y tablas de más |
| Tiempo, disco y memoria | Con el volumen real, para saber cuánto dura el corte |
| Restaurar un respaldo posterior | Sección 3, ya con los datos importados |

## 5. Cómo funciona detrás de `nginx-proxy`

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

## 6. Problemas frecuentes

| Síntoma | Causa |
|---|---|
| 413 al subir un archivo | Falta el archivo de `vhost.d` (ver sección 1) |
| 502 o 503 de `nginx-proxy` | `mdm_app` no está en la red `web`, o sigue arrancando: `dcp ps`, `dcp logs app` |
| El navegador informa un error de CORS | `CORS_ALLOWED_ORIGINS` no incluye el dominio exacto del frontend, con `https://` |
| La lista de personas responde 500 tras importar | Hay personas sin sexo (sección 4, paso 3) |
| Login responde 500 | Revisar Redis: sin él fallan los límites de intentos. `/up` responde 200 de todos modos, porque no revisa Redis ni la base |
| Todos reciben 429 a la vez | `TRUSTED_PROXIES` no coincide con la subred de `web` |

Logs: `dcp logs -f app`. Docker los rota: 5 archivos de 10 MB por servicio.
