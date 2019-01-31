FROM node:8 as builder

RUN mkdir dist

COPY . /dist

WORKDIR /dist

RUN npm install; \
    npm run prod

FROM php:7.2-fpm

RUN apt-get update \
    && apt-get install -y \
        libpng-dev \
        libcurl4-openssl-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j$(nproc) bcmath calendar curl gd mysqli pdo_mysql zip opcache \
    && apt-get clean \
    && mkdir /var/www/berlussimo

COPY . /var/www/berlussimo
COPY --from=builder /dist/public/mix-manifest.json /var/www/berlussimo/public/
COPY ./docker/fpm/docker-php-entrypoint /usr/local/bin/
RUN chmod 755 /usr/local/bin/docker-php-entrypoint
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /var/www/berlussimo
RUN composer install; \
    php artisan parser:generate
RUN chown -R www-data:www-data /var/www/berlussimo
USER www-data:www-data

VOLUME /var/www/berlussimo/config
VOLUME /var/www/berlussimo/storage

EXPOSE 9000