FROM php:8.4-fpm-alpine

# Dependencias de sistema
RUN apk add --no-cache \
    bash \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    $PHPIZE_DEPS

# Extensoes PHP necessarias para Laravel + PostgreSQL + Redis
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip bcmath opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY ./src /var/www

RUN composer install --no-interaction --optimize-autoloade

EXPOSE 9000

CMD ["php-fpm"]
