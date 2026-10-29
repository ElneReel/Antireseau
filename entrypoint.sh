#!/bin/sh

echo "=== Démarrage antireseau sur Render ==="

# 1. Dossiers requis par Alpine Linux & Nginx
mkdir -p /run/nginx /var/log/nginx /var/www/html/storage/logs /var/www/html/storage/framework/views /var/www/html/storage/framework/sessions /var/www/html/storage/framework/cache

# 2. Fichier d'environnement .env
if [ ! -f /var/www/html/.env ]; then
    echo "Création du fichier .env depuis .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Forcer APP_URL et ASSET_URL en HTTPS
sed -i "s|^APP_URL=.*|APP_URL=https://antireseau.com|g" /var/www/html/.env
sed -i "s|^APP_ENV=.*|APP_ENV=production|g" /var/www/html/.env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|g" /var/www/html/.env
if ! grep -q "^ASSET_URL=" /var/www/html/.env; then
    echo "ASSET_URL=https://antireseau.com" >> /var/www/html/.env
else
    sed -i "s|^ASSET_URL=.*|ASSET_URL=https://antireseau.com|g" /var/www/html/.env
fi

# 3. Port dynamique Render ($PORT)
SERVER_PORT=${PORT:-10000}
echo "Configuration de Nginx pour écouter sur le port ${SERVER_PORT}..."
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

# 8. Nettoyage du cache pour régénérer avec HTTPS
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 9. Démarrage de PHP-FPM en arrière-plan
echo "Lancement de PHP-FPM..."
php-fpm -D

# 10. Test et démarrage de Nginx au premier plan
echo "Vérification de la configuration Nginx..."
nginx -t

echo "Lancement de Nginx sur le port ${SERVER_PORT}..."
exec nginx -g "daemon off;"
