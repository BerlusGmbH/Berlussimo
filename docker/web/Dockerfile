FROM node:8 as builder

RUN mkdir dist

COPY . /dist

WORKDIR /dist

RUN npm install; \
    npm run prod


FROM nginx:1.15

RUN mkdir -p /var/www/berlussimo

COPY --from=builder /dist/public /var/www/berlussimo/public

COPY ./docker/web/berlussimo.template /etc/nginx/conf.d/berlussimo.template

RUN chown -R www-data:www-data /var/www/berlussimo

WORKDIR /var/www/berlussimo

EXPOSE 9000