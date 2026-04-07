ARG PHP_VERSION=8.3
ARG NODE_VERSION=20

FROM node:${NODE_VERSION}-alpine AS node
# https://serversideup.net/open-source/docker-php/docs/getting-started/installation
FROM serversideup/php:${PHP_VERSION}-fpm-nginx-alpine AS base

ENV PHP_OPCACHE_ENABLE=1
ENV HEALTHCHECK_PATH=/up

ARG COMPOSER_AUTH
ENV COMPOSER_AUTH="${COMPOSER_AUTH}"

##############################################################################

COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

##############################################################################

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .


# Fix permissions
# TODO: find alternative way to fix permissions
RUN chown -R www-data:www-data storage && \
    chmod -R 775 storage

##############################################################################

USER root

# Laravel medialibrary requirements - https://spatie.be/docs/laravel-medialibrary/v11/installation-setup#content-setting-up-optimization-tools
RUN docker-php-serversideup-dep-install-alpine "ffmpeg jpegoptim optipng pngquant gifsicle libavif"

RUN install-php-extensions \
    # Laravel requirements
    bcmath intl \
    # Laravel medialibrary
    exif gd imagick/imagick@master

# TODO: Restore imagick version when imagick supports PHP 8.3 officially

RUN npm install -g --no-audit svgo

USER www-data

RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

##############################################################################

RUN npm install --include=dev --no-audit && \
    # Laravel medialibrary requirements
    npm cache clean --force

RUN npm run build

##############################################################################

# Pre-deploy scripts
RUN composer run-script predeploy
