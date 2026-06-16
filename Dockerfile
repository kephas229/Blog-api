FROM php:8.4-fpm-alpine

# Extensions nécessaires pour PostgreSQL et Laravel
RUN apk add --no-cache unzip nodejs npm postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Droits sur le stockage
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

# Variable par défaut : PostgreSQL (peut être surchargée par les env vars Render)
ENV DB_CONNECTION=pgsql

# Migrations + seed (une seule fois si la base est vide) + démarrage
CMD php artisan config:clear && php artisan migrate --force && php artisan db:seed --force && php artisan storage:link --force && php artisan serve --host=0.0.0.0 --port=8080
