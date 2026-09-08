FROM php:8.1-fpm-alpine

RUN apk update

ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.52/install-php-extensions /usr/local/bin/

# Install Postgres PDO
RUN apk add libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql
