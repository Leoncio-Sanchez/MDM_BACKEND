#!/bin/sh
# Arranque del backend: deja en caché config, rutas y vistas. Se hace aquí y no al construir la
# imagen porque necesita las variables del servidor. Las migraciones NO corren solas (DEPLOY.md).
set -e

if [ "${APP_DEBUG:-false}" = "true" ]; then
    echo "AVISO: APP_DEBUG=true muestra detalles internos a cualquiera; en producción debe ser false." >&2
fi

if [ ! -w "${MEDIA_DISK_ROOT:-/var/www/media}" ]; then
    echo "ERROR: la carpeta de adjuntos ${MEDIA_DISK_ROOT:-/var/www/media} no se puede escribir." >&2
    exit 1
fi

php artisan config:cache --no-ansi
php artisan route:cache --no-ansi
php artisan view:cache --no-ansi

exec "$@"
