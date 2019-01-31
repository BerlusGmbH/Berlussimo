ARG TAG

FROM berlus/berlussimo-base:$TAG AS base

FROM nginx:1.15

COPY --from=base /var/www/berlussimo/ /var/www/berlussimo/

RUN chown -R www-data:www-data /var/www/berlussimo

COPY ./docker/web/berlussimo.template /etc/nginx/conf.d/berlussimo.template

WORKDIR /var/www/berlussimo

EXPOSE 9000
