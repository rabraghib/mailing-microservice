FROM composer:2.1.12 as composer

FROM php:8.1.2-alpine

RUN apk add libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN docker-php-source delete

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

CMD php -S 0.0.0.0:8080 -t public