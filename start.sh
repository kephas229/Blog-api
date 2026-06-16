#!/bin/sh
set -e

echo "==> Mise en cache de la configuration..."
php artisan config:cache

echo "==> Exécution des migrations..."
php artisan migrate --force

echo "==> Création du lien symbolique storage..."
php artisan storage:link || true

echo "==> Démarrage du serveur..."
php artisan serve --host=0.0.0.0 --port=8080
