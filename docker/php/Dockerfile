FROM php:8.2-fpm-alpine

# Cài đặt các gói cần thiết cho hệ thống
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    libxml2-dev

# Cài đặt các extension PHP cần thiết cho Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy file cấu hình PHP tùy chỉnh
# COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Đặt thư mục làm việc
WORKDIR /var/www

# Cấp quyền
RUN chown -R www-data:www-data /var/www

# Chuyển sang user www-data
USER www-data

# Mở cổng 9000 cho PHP-FPM
EXPOSE 9000