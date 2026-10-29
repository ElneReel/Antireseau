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

# Installation de Nginx et des extensions requises pour Laravel
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
    && mkdir -p /run/nginx /var/log/nginx /etc/nginx/http.d \
    && rm -rf /etc/nginx/http.d/* /etc/nginx/conf.d/* /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie du code
COPY . .

# Copie des assets compilés par Node.js
COPY --from=node-builder /app/public/build ./public/build

# Installation Composer en mode production
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Nginx vhost sous Alpine http.d
COPY nginx.conf /etc/nginx/http.d/default.conf

# Entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions initiales
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80 10000

CMD ["/usr/local/bin/entrypoint.sh"]
