FROM php:8.3-fpm-alpine

# Installation des extensions nécessaires pour PostgreSQL et Laravel
RUN apk add --no-cache unzip nodejs npm postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

# Installation des dépendances et nettoyage
RUN composer install --no-dev --optimize-autoloader

# Droits d'accès pour le stockage des images
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080
CMD php artisan serve --host=0.0.0.0 --port=8080
