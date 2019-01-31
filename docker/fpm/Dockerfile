ARG TAG

FROM berlus/berlussimo-base:$TAG AS base

FROM php:7.3-fpm

COPY --from=base /var/www/berlussimo/ /var/www/berlussimo/

RUN chown -R www-data:www-data /var/www/berlussimo

RUN apt-get update \
    && apt-get install -y \
        libpng-dev \
        libcurl4-openssl-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j$(nproc) bcmath calendar curl gd mysqli pdo_mysql zip opcache \
    && apt-get clean

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./docker/fpm/docker-php-entrypoint /usr/local/bin/

RUN chmod 755 /usr/local/bin/docker-php-entrypoint

WORKDIR /var/www/berlussimo

USER www-data:www-data

VOLUME /var/www/berlussimo/config
VOLUME /var/www/berlussimo/storage
