FROM composer:2.8 AS composer

FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl zip unzip libpq-dev libzip-dev libpng-dev libjpeg-dev libonig-dev libxml2-dev \
    libimage-exiftool-perl ffmpeg \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath gd \
    && a2enmod rewrite headers \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Apache : racine sur /public, port non privilégié (8000 par défaut, $PORT sinon), pas de version exposée
ENV PORT=8000 \
    APACHE_DOCUMENT_ROOT=/app/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri 's!Listen 80!Listen ${PORT}!; s!<VirtualHost \*:80>!<VirtualHost *:${PORT}>!' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf \
    && printf 'ServerTokens Prod\nServerSignature Off\nTraceEnable Off\n' > /etc/apache2/conf-available/security-hardening.conf \
    && a2enconf security-hardening

WORKDIR /app
COPY --chown=www-data:www-data . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data /app/storage /app/bootstrap/cache /var/run/apache2 /var/lock/apache2 /var/log/apache2

# Plus de root à l'exécution
USER www-data

EXPOSE 8000

CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    exec apache2-foreground
