FROM node:8

COPY ./docker/echo-server/docker-entrypoint.sh /docker-entrypoint.sh
COPY ./docker/echo-server/laravel-echo-server.json /laravel-echo-server.json

RUN apt update && \
    apt upgrade -y && \
    apt install -y openssl && \
    apt clean && \
    npm install -g laravel-echo-server && \
    mkdir -p /app/database && \
    chmod 755 /docker-entrypoint.sh

WORKDIR /app

VOLUME /app

EXPOSE 6001

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["laravel-echo-server", "start", "--force"]