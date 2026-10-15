# Étape 1 : build des assets front (Tailwind/Vite)
FROM node:22-slim AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Étape 2 : application PHP
FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    pkg-config libsqlite3-dev libzip-dev libonig-dev libpq-dev unzip git \
    && docker-php-ext-install pdo pdo_sqlite pdo_pgsql mbstring zip \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN printf "upload_max_filesize=12M\npost_max_size=14M\n" > /usr/local/etc/php/conf.d/uploads.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache-port.sh /usr/local/bin/apache-port.sh
RUN chmod +x /usr/local/bin/apache-port.sh

EXPOSE 80
CMD ["/usr/local/bin/apache-port.sh"]