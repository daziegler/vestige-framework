# syntax=docker/dockerfile:1
FROM php:8.5-cli AS base

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install -j"$(nproc)" intl zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/zz-vestige.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

RUN groupadd -g "${GID}" vestige \
    && useradd -m -u "${UID}" -g "${GID}" -s /bin/bash vestige

USER vestige
WORKDIR /app