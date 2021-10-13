# 컴포저 빌더패던...
FROM composer:2.0 AS composer

# PHP-Apache
FROM webdevops/php-nginx:7.4-alpine

# 기본 패키지 설치
RUN apk add --update --no-cache libzip-dev zip git
RUN docker-php-ext-install zip mysqli sockets bcmath

# 최초 컴포저 설치
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY src/code/composer.json /app
COPY src/code/composer.lock /app
WORKDIR /app
RUN composer install

# Codebase
COPY src/code/ /app
COPY src/config/etc /opt/docker/etc
COPY src/config/periodic/ /etc/periodic
RUN chmod -R +x /etc/periodic/