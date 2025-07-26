FROM composer:2 AS composer

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader --ignore-platform-reqs

FROM php:8.4-fpm-alpine
WORKDIR /var/www/html

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    libzip-dev zip zlib-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    mysql-client \
    netcat-openbsd \
    dos2unix

RUN docker-php-ext-install pdo_mysql zip bcmath \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd \
 && pecl install redis mongodb \
 && docker-php-ext-enable redis mongodb

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY . .
COPY --from=composer /app/vendor/ ./vendor/

COPY ./docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN dos2unix /usr/local/bin/docker-entrypoint.sh \
 && chmod +x /usr/local/bin/docker-entrypoint.sh

COPY ./docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
EXPOSE 9000
CMD ["php-fpm"]