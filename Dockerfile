FROM php:8.5-alpine

COPY --from=composer:2.11 /usr/bin/composer /usr/bin/composer
