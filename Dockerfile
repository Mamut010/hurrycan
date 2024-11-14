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
    && mkdir /var/www/html/public /var/www/html/resources /var/www/db \
    && a2enmod rewrite \
    && docker-php-ext-install \
        mysqli \
        bcmath \
    && docker-php-ext-enable mysqli
# Copy custom ini files into conf.d
COPY ./.docker/php/*.ini /usr/local/etc/php/conf.d/
# Copy source code
COPY ./src /var/www/html
# Copy public directory
COPY ./public /var/www/html/public
# Copy resources directory
COPY ./resources /var/www/html/resources
    # Give permission to write into assets directory
RUN chown www-data:www-data /var/www/html/public/assets && chmod 775 /var/www/html/public/assets \
    # Give permission to write into resources directory
    && chown www-data:www-data /var/www/html/resources && chmod 775 /var/www/html/resources \
    # Copy the favicon.ico
    && favicon=$(find ./public/ -maxdepth 1 -name 'favicon.ico' | head -n 1) && \
    if [ -n "$favicon" ]; then cp "$favicon" /var/www/html/; else echo "No favicon file found."; fi

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
