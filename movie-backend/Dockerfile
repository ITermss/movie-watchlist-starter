FROM dunglas/frankenphp:php8.4-alpine

WORKDIR /app

# Install PHP extensions for PostgreSQL, MySQL, SQLite, and sodium
RUN install-php-extensions \
    pdo_pgsql \
    pdo_mysql \
    pdo_sqlite \
    sodium \
    opcache \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["frankenphp", "run", "--config", "/app/Caddyfile"]

