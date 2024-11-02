# syntax=docker/dockerfile:1

FROM composer:lts AS dev-deps
WORKDIR /app
RUN --mount=type=bind,source=./composer.json,target=composer.json \
    --mount=type=bind,source=./composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-interaction

FROM composer:lts AS prod-deps
WORKDIR /app
RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction

FROM php:8.2-apache AS base
RUN echo "ServerName 127.0.0.1" >> /etc/apache2/apache2.conf \
    && mkdir /var/www/html/public && chmod 755 /var/www/html/public \
    && mkdir /var/www/html/resources && chmod 777 /var/www/html/resources \
    && a2enmod rewrite \
    && docker-php-ext-install \
        mysqli \
    && docker-php-ext-enable mysqli
COPY ./src /var/www/html
COPY ./.docker/apache/.htaccess /var/www/html
COPY ./public /var/www/html/public
COPY ./resources /var/www/html/resources

FROM base AS development
COPY ./tests /var/www/html/tests
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug
COPY --from=dev-deps app/vendor/ /var/www/html/vendor

FROM development AS test
WORKDIR /var/www/html
RUN ./vendor/bin/phpunit tests/HelloWorldTest.php

FROM base AS final
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --from=prod-deps app/vendor/ /var/www/html/vendor
USER www-data
