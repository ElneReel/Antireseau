#!/bin/sh
set -e

# Port dynamique (Render fournit $PORT, par défaut 80 ou 10000)
SERVER_PORT=${PORT:-80}
echo "Configuring Nginx on port ${SERVER_PORT}..."
sed -i "s/listen 80;/listen ${SERVER_PORT};/g" /etc/nginx/conf.d/default.conf
sed -i "s/listen \[::\]:80;/listen [::]:${SERVER_PORT};/g" /etc/nginx/conf.d/default.conf

# Base de données SQLite par défaut si non configurée en MySQL/Postgres
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
    chown -R www-data:www-data /var/www/html/database
fi

# Permissions sur storage et bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Migrations de base de données
echo "Running migrations..."
php artisan migrate --force || true

# Optimisations du cache Laravel pour la production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx on port ${SERVER_PORT}..."
nginx -g "daemon off;"
