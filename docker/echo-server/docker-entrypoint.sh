#!/bin/bash
set -e

cp /laravel-echo-server.json /app/laravel-echo-server.json
    # Replace with environment variables
sed -i "s|ECHO_SERVER_DB|${ECHO_SERVER_DB:-redis}|i" /app/laravel-echo-server.json
sed -i "s|REDIS_HOST|${REDIS_HOST:-redis}|i" /app/laravel-echo-server.json
sed -i "s|REDIS_PORT|${REDIS_PORT:-6379}|i" /app/laravel-echo-server.json
sed -i "s|REDIS_DB_BACKEND|${REDIS_DB_BACKEND:-0}|i" /app/laravel-echo-server.json

exec "$@"