FROM composer:2.8.12 AS vendor

WORKDIR /app

COPY backend/composer.json backend/composer.lock ./

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

COPY backend/ ./

RUN composer dump-autoload --optimize --no-interaction \
    && php artisan package:discover --ansi

FROM php:8.4.14-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-notes.conf
COPY docker/php/entrypoint.sh /entrypoint.sh
COPY docker/php/healthcheck.sh /healthcheck.sh
RUN chmod +x /entrypoint.sh /healthcheck.sh

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html

RUN mkdir -p storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
