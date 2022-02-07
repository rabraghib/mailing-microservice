ARG PHP_VERSION=8.1.2
ARG COMPOSER_VERSION=2.1.12

FROM composer:${COMPOSER_VERSION} as composer
FROM php:${PHP_VERSION}-fpm-alpine as app

ARG APP_ENV=prod
ARG NGINX_VERSION=1.21.3-r1

ENV OPCACHE_VALIDATE_TIMESTAMPS=${APP_DEBUG:-0}
ENV PHP_DATE_TIMEZONE=UTC

# Install PHP extensions
RUN docker-php-source extract \
        && apk add --update --virtual .build-deps autoconf g++ make pcre-dev icu-dev openssl-dev libxml2-dev libmcrypt-dev git libpng-dev \
	# Installing pecl modules (xdebug not installed in prod)
		&& pecl install apcu $([[ "$APP_ENV" != "prod" ]] && echo "xdebug") \
	# Enable pecl modules
        && docker-php-ext-enable apcu opcache \
	# Installing database ext \
        && apk add postgresql-dev \
        && docker-php-ext-install pgsql pdo_pgsql \
        && apk del postgresql-libs libsasl db \
	# Post run
		&& runDeps="$( \
			scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
				| tr ',' '\n' \
				| sort -u \
				| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
		)" \
		&& apk add --no-cache --virtual .app-phpexts-rundeps $runDeps \
		&& pecl clear-cache \
		&& docker-php-source delete \
		&& apk del --purge .build-deps \
		&& rm -rf /tmp/pear \
		&& rm -rf /var/cache/apk/*

RUN mkdir -p /var/www/var/logs \
  && ln -sf /dev/stdout /var/www/var/logs/php-fpm-access.log \
  && ln -sf /dev/stderr /var/www/var/logs/php-fpm-error.log

COPY docker/php.ini $PHP_INI_DIR/conf.d/php.ini
COPY docker/php-fpm.ini $PHP_INI_DIR/../php-fpm.d/php-fpm.conf
COPY docker/xdebug.ini $PHP_INI_DIR/conf.d/xdebug.ini

WORKDIR /var/www

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN mkdir -p var/cache var/logs
RUN if [ "$APP_ENV" == "prod" ] ; then \
	rm -f $PHP_INI_DIR/conf.d/xdebug.ini && \
    composer install --no-interaction --no-dev \
; else \
    composer install --no-interaction \
; fi
RUN composer clear-cache
COPY . ./
RUN chown -R www-data:www-data /var/www

COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN dos2unix /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80
ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

FROM app as worker
ENTRYPOINT ["php", "-f", "bin/worker.php", "--"]
CMD ["--serve"]