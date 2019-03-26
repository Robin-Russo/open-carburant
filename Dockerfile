FROM php:7-apache

RUN apt-get update && \
    apt-get install -y libxml2-dev && \
    docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y mysql-client && rm -rf /var/lib/apt

RUN groupadd --gid 1000 devweb && \
    useradd --gid devweb --uid 1000 devweb && \
    usermod --append --groups devweb www-data
