# ==============================================================================
# STAGE 1 : BUILD DES ASSETS VITE (CSS & JS)
# ==============================================================================
FROM node:20-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ==============================================================================
# STAGE 2 : RUNTIME PHP 8.3 FPM + NGINX
# ==============================================================================
FROM php:8.3-fpm-alpine

# Installation de Nginx et des dépendances système
RUN apk update && apk add --no-cache \
    nginx \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    sqlite-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_sqlite pdo_mysql mbstring zip bcmath exif opcache \
    && rm -rf /var/cache/apk/*

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie du code source
COPY . .

# Copie des assets compilés par Node.js
COPY --from=node-builder /app/public/build ./public/build

# Installation des dépendances Composer sans dépendances de dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configuration Nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Script d'entrée
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
