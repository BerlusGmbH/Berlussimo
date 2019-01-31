FROM php:7.3-fpm as builder

ARG CACHE_DRIVER=file
ARG SESSION_DRIVER=file
ARG LIGHTHOUSE_CACHE_ENABLE=false
ARG APP_KEY=base64:/7xjVvfLJJyFRX6Bjse2sdpxae7+5804g+9m1RRKTiY=

COPY . /var/www/berlussimo

RUN apt-get update \
    && apt-get install -y git \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j$(nproc) zip \
        && apt-get clean

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/berlussimo
RUN composer install; \
    php artisan graphql:generate-schema; \
    php artisan storage:link

FROM node:10

COPY --from=builder /var/www/berlussimo/ /var/www/berlussimo/

WORKDIR /var/www/berlussimo

RUN npm install; \
    npm run prod

RUN chown -R www-data:www-data /var/www/berlussimo
