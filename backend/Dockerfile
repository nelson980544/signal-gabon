FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libzip-dev libpng-dev libonig-dev libxml2-dev \
    libimage-exiftool-perl qpdf \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql mbstring zip bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:clear || true

EXPOSE 8000

# Pas de `db:seed` ici : le seeder crée des comptes de démonstration dont
# les mots de passe sont publiés dans le README. Créer le premier compte avec
#   php artisan cnlcei:creer-superviseur
CMD php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=8000
