FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

RUN docker-php-ext-enable mysqli pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY ./src .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader