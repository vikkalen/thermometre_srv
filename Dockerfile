FROM arm32v6/php:8.0-fpm-alpine

ENV docker 1
RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /bin/composer.phar

ENV PHPIZE_DEPS \
    git \
    file \
    re2c \
    autoconf \
    make \
    zlib \
    zlib-dev \
    g++

RUN apk add --update --no-cache --virtual .build-deps ${PHPIZE_DEPS} rrdtool-dev \
    && pecl install rrd-2.0.3 \
    && apk del .build-deps \
    && apk add --update --no-cache ttf-dejavu rrdtool \
    && apk add --update --no-cache sqlite \
    && docker-php-ext-enable rrd

WORKDIR /var/www/html/thermometre

COPY . .
