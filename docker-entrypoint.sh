#!/bin/sh
set -e

PORT="${PORT:-8080}"

sed "s/RAILWAY_PORT/${PORT}/g" /etc/nginx/http.d/default.conf.template \
    > /etc/nginx/http.d/default.conf

php-fpm -D

exec nginx -g 'daemon off;'
