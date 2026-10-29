#!/bin/sh

echo "=== Démarrage antireseau sur Render ==="

# 1. Dossiers requis par Alpine Linux & Nginx
mkdir -p /run/nginx /var/log/nginx /var/www/html/storage/logs /var/www/html/storage/framework/views /var/www/html/storage/framework/sessions /var/www/html/storage/framework/cache

# 2. Fichier d'environnement .env
if [ ! -f /var/www/html/.env ]; then
    echo "Création du fichier .env depuis .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# 3. Port dynamique Render ($PORT)
SERVER_PORT=${PORT:-10000}
echo "Configuration de Nginx pour écouter sur le port ${SERVER_PORT}..."
# Cible Alpine http.d
if [ -f /etc/nginx/http.d/default.conf ]; then
    sed -i "s/listen 80;/listen ${SERVER_PORT};/g" /etc/nginx/http.d/default.conf
    sed -i "s/listen \[::\]:80;/listen [::]:${SERVER_PORT};/g" /etc/nginx/http.d/default.conf
fi

# 4. Base SQLite
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite
chmod -R 777 /var/www/html/database

# 5. Permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Clé Laravel obligatoire
cd /var/www/html
php artisan key:generate --force

# 7. Migrations de la base de données
echo "Exécution des migrations Laravel..."
php artisan migrate --force

# 8. Démarrage de PHP-FPM en arrière-plan
echo "Lancement de PHP-FPM..."
php-fpm -D

# 9. Test et démarrage de Nginx au premier plan
echo "Vérification de la configuration Nginx..."
nginx -t

echo "Lancement de Nginx sur le port ${SERVER_PORT}..."
exec nginx -g "daemon off;"
