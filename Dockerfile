FROM php:8.2-cli-alpine

RUN apk add --no-cache git unzip libxml2-dev oniguruma-dev \
    && docker-php-ext-install dom xml mbstring

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app
