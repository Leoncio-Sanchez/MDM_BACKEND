# ==============================================================================
# Backend de producción — Sistema LF-ITSE
# Nginx + PHP-FPM en un solo contenedor, sin root, escuchando en el puerto 80 interno.
# Lo publica nginx-proxy por la red "web" (ver compose.prod.yaml y DEPLOY.md).
# ==============================================================================

# 1) PHP con las extensiones: también valida los requisitos de composer.lock
FROM php:8.4-fpm-alpine AS base
RUN set -eux; \
    apk add --no-cache --virtual .compilar $PHPIZE_DEPS linux-headers postgresql-dev \
        freetype-dev libjpeg-turbo-dev libpng-dev libwebp-dev icu-dev libzip-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" pdo_pgsql gd intl zip bcmath opcache; \
    pecl install redis-6.2.0; \
    docker-php-ext-enable redis; \
    rm -rf /tmp/pear; \
    librerias="$(scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
        | tr ',' '\n' | sort -u | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }')"; \
    apk add --no-cache $librerias icu-data-full; \
    apk del --no-network .compilar

# 2) Dependencias de Composer, sin las de desarrollo
FROM base AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-interaction --no-scripts

# 3) Imagen final
FROM base AS produccion
RUN apk add --no-cache nginx supervisor

COPY docker/produccion/php.ini /usr/local/etc/php/conf.d/zz-produccion.ini
# El nombre debe ordenarse después de zz-docker.conf, que escucha en todas las interfaces
COPY docker/produccion/fpm.conf /usr/local/etc/php-fpm.d/zz-produccion.conf
COPY docker/produccion/nginx.conf /etc/nginx/nginx.conf
COPY docker/produccion/supervisord.conf /etc/supervisord.conf
COPY --chmod=755 docker/produccion/entrypoint.sh /usr/local/bin/mdm-entrypoint

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app ./

# MEDIA_DISK_ROOT: se crea aquí para que el volumen nuevo herede el dueño www-data
RUN rm -rf docker \
    && mkdir -p storage/app/private storage/app/public storage/fonts storage/logs \
        storage/framework/cache/data storage/framework/sessions storage/framework/views \
        bootstrap/cache /var/www/media \
    && chown -R www-data:www-data storage bootstrap/cache /var/www/media

USER www-data
# Lo que haría "composer install" con sus scripts. APP_ENV distinto de production: aún no hay JWT_SECRET
RUN APP_ENV=build php artisan package:discover --ansi

EXPOSE 80
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD wget -q --spider http://127.0.0.1/up || exit 1
ENTRYPOINT ["mdm-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
