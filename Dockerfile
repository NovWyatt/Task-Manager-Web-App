# Build Stage
FROM composer:2.6 as composer_build

# Cài đặt các extension PHP cần thiết
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git

# Cài đặt extensions PHP
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip gd

# Copy composer files và cài đặt dependencies
WORKDIR /app
COPY composer.json composer.lock ./

# Tăng giới hạn bộ nhớ cho composer
ENV COMPOSER_MEMORY_LIMIT=-1

# Cài đặt dependencies cho production và bỏ qua scripts để tiết kiệm bộ nhớ
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

# Application Stage
FROM php:8.2-apache

# Cài đặt các gói cần thiết
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Cài đặt extensions PHP
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Cấu hình Apache
RUN a2enmod rewrite headers
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy code ứng dụng trước
COPY . /var/www/html

# Copy vendor từ stage composer_build
COPY --from=composer_build /app/vendor /var/www/html/vendor

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cài đặt Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Tối ưu hóa Composer autoload và cache
RUN composer dump-autoload --optimize --no-dev
RUN php artisan config:cache && php artisan route:cache && php artisan view:cache

# Cài đặt dependencies frontend và build
RUN npm ci --omit=dev && npm run build

# Thiết lập quyền
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html/storage -type d -exec chmod 775 {} \; \
    && find /var/www/html/storage -type f -exec chmod 664 {} \; \
    && find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \; \
    && find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;

# Tạo thư mục logs và thiết lập quyền
RUN mkdir -p /var/www/html/storage/logs \
    && touch /var/www/html/storage/logs/laravel.log \
    && chown -R www-data:www-data /var/www/html/storage/logs \
    && chmod -R 775 /var/www/html/storage/logs

# Expose port
EXPOSE 80

# Tạo start script
RUN echo '#!/bin/bash\n\
set -e\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
php artisan storage:link\n\
apache2-foreground' > /usr/local/bin/start-laravel.sh \
    && chmod +x /usr/local/bin/start-laravel.sh

# Start Apache với script tùy chỉnh
CMD ["/usr/local/bin/start-laravel.sh"]