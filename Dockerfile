FROM php:7-apache

RUN apt-get update && \
    apt-get install -y libxml2-dev && \
    apt-get install -y libzip-dev && \
    docker-php-ext-configure zip --with-libzip=/usr/include/  &&\
    docker-php-ext-install soap zip mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y mysql-client && rm -rf /var/lib/apt

COPY upload.ini /usr/local/etc/php/conf.d

