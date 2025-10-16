# Sử dụng PHP 8.3 với FPM
FROM php:8.3-fpm

# Cài đặt system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    netcat-openbsd \
    mariadb-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Tạo user www-data
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Thiết lập biến môi trường cho MySQL client
# ENV DB_CLIENT_BIN=/usr/bin

# Thiết lập working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Cài đặt PHP dependencies (skip scripts to avoid artisan errors)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy source code
COPY . .

# Chạy composer scripts sau khi có đầy đủ source code
RUN composer run-script post-autoload-dump

# Thiết lập quyền sở hữu
RUN chown -R www-data:www-data /var/www
RUN chmod -R 775 /var/www/storage
RUN chmod -R 775 /var/www/bootstrap/cache

# Copy PHP-FPM config
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy startup script
COPY docker/scripts/start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["/usr/local/bin/start.sh"]


