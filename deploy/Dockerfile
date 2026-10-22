# Image de production pour l'API SignalGabon.
#
# Différences avec backend/Dockerfile (qui sert aux hébergeurs PaaS) :
#   - php-fpm derrière nginx au lieu de `php artisan serve` (serveur de dev)
#   - pdo_mysql présent (la base de production est MariaDB)
#   - aucun seed au démarrage : les comptes agents sont créés à la main
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        git curl zip unzip oniguruma-dev libzip-dev libpng-dev libjpeg-turbo-dev \
        freetype-dev icu-dev mariadb-client gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql mbstring zip bcmath gd exif opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY backend/ ./
# Les répertoires de cache sont créés AVANT dump-autoload : `package:discover`
# écrit dans bootstrap/cache, que .dockerignore exclut du contexte.
RUN mkdir -p bootstrap/cache storage/app/private/preuves storage/framework/cache/data \
             storage/framework/sessions storage/framework/views storage/logs \
    && composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

# OPcache : l'API est en lecture seule, le cache peut être agressif.
RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.validate_timestamps=0'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.memory_consumption=192'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Taille des preuves : alignée sur la limite applicative (10 Mo par fichier).
RUN { \
      echo 'upload_max_filesize=10M'; \
      echo 'post_max_size=60M'; \
      echo 'memory_limit=256M'; \
      echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/signalgabon.ini

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
