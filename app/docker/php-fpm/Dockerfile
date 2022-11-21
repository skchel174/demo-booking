FROM php:8.1-fpm-alpine

RUN apk update && apk add mysql-client \
    && docker-php-ext-install pdo pdo_mysql

WORKDIR /app
