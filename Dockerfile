FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    curl \
    git \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        bcmath \
        exif \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
