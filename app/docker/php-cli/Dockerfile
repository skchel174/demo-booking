FROM php:8.1-cli-alpine

RUN apk update && apk add --no-cache bash mysql-client \
    && docker-php-ext-install pdo pdo_mysql

# Composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

WORKDIR /app
