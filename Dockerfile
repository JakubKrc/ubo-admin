# Local-dev image: PHP CLI + composer + node, runs `php artisan serve`.
# Production deploy uses a separate Dockerfile with php-fpm + nginx.
FROM php:8.4-cli-alpine

RUN apk add --no-cache \
        postgresql-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        nodejs \
        npm \
        git \
        unzip \
        bash \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        opcache \
        intl \
        mbstring \
        zip \
        bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
