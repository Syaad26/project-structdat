FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libssl-dev pkg-config unzip git \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

COPY . /var/www/html/

RUN curl -sS https://getcomposer.org/installer | php \
    && php composer.phar install

EXPOSE 80