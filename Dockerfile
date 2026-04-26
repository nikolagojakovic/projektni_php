FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx libpq-dev curl-dev ca-certificates \
    && update-ca-certificates \
    && docker-php-ext-install pdo pdo_pgsql curl \
    && rm -rf /var/cache/apk/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .

COPY nginx.conf.template /etc/nginx/http.d/default.conf.template
RUN rm -f /etc/nginx/http.d/default.conf

RUN chown -R www-data:www-data /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

CMD ["/usr/local/bin/docker-entrypoint.sh"]
