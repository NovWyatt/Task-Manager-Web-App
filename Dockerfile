# Build Stage
FROM composer:2.6 AS composer_stage

WORKDIR /app
COPY composer.json composer.lock ./

# Cài đặt dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs

# Production Stage
FROM php:8.2-apache

# Cài đặt extension PHP cần thiết
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd \
    && a2enmod rewrite headers

# Cấu hình Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy mã nguồn ứng dụng
COPY . .

# Copy vendor từ build stage
COPY --from=composer_stage /app/vendor ./vendor

# Tạo .htaccess nếu không tồn tại
RUN echo '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>' > public/.htaccess

# Thiết lập quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Tạo thư mục logs
RUN mkdir -p /var/www/html/storage/logs \
    && touch /var/www/html/storage/logs/laravel.log \
    && chown -R www-data:www-data /var/www/html/storage/logs \
    && chmod -R 775 /var/www/html/storage/logs

# Tạo script khởi động
RUN echo '#!/bin/bash
echo "Starting Laravel application..."

# Đảm bảo có App Key
php artisan key:generate --force

# Tạo symlink cho storage nếu cần
if [ ! -L public/storage ]; then
  echo "Creating storage symlink..."
  php artisan storage:link
fi

# Thực hiện migrations nếu được thiết lập
if [ "${DB_MIGRATE:-false}" = "true" ]; then
  echo "Running database migrations..."
  php artisan migrate --force
fi

# Khởi động Apache
apache2-foreground
' > /usr/local/bin/start-laravel.sh \
    && chmod +x /usr/local/bin/start-laravel.sh

# Expose port
EXPOSE 80

# Command để chạy khi container được khởi động
CMD ["/usr/local/bin/start-laravel.sh"]