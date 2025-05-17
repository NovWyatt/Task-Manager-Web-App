FROM php:8.2-apache

# Thiết lập các biến môi trường
ENV TZ=UTC
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Cài đặt dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Cài đặt và bật extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Bật modules cần thiết của Apache
RUN a2enmod rewrite headers

# Cấu hình Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Thiết lập PHP ini
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit=256M" >> "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize=50M" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size=50M" >> "$PHP_INI_DIR/php.ini" \
    && echo "max_execution_time=600" >> "$PHP_INI_DIR/php.ini"

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ code ứng dụng (đã bao gồm vendor và các assets đã build)
COPY . .

# Kiểm tra các file quan trọng
RUN if [ ! -f "vendor/autoload.php" ]; then \
    echo "Error: vendor/autoload.php is missing. Please run 'composer install' locally before building."; \
    exit 1; \
  fi \
  && if [ ! -f "public/.htaccess" ]; then \
    echo "Error: public/.htaccess is missing. Please create it before building."; \
    exit 1; \
  fi

# Thiết lập quyền cho các thư mục
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html/storage -type d -exec chmod 775 {} \; \
    && find /var/www/html/storage -type f -exec chmod 664 {} \; \
    && find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \; \
    && find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;

# Tạo các thư mục logs nếu chưa tồn tại
RUN mkdir -p /var/www/html/storage/logs \
    && touch /var/www/html/storage/logs/laravel.log \
    && chown -R www-data:www-data /var/www/html/storage/logs \
    && chmod -R 775 /var/www/html/storage/logs

# Tạo script khởi động
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Starting Laravel application..."\n\
\n\
# Đợi database (nếu cần)\n\
#until nc -z -v -w30 $DB_HOST $DB_PORT; do\n\
#  echo "Waiting for database connection..."\n\
#  sleep 2\n\
#done\n\
\n\
# Cache cấu hình để tối ưu hiệu suất\n\
if [ "$APP_ENV" = "production" ]; then\n\
  echo "Optimizing for production..."\n\
  php artisan config:cache\n\
  php artisan route:cache\n\
  php artisan view:cache\n\
else\n\
  echo "Clearing cache for development..."\n\
  php artisan optimize:clear\n\
fi\n\
\n\
# Đảm bảo có App Key\n\
if [ -z "$APP_KEY" ]; then\n\
  echo "Generating application key..."\n\
  php artisan key:generate --force\n\
fi\n\
\n\
# Tạo symlink cho storage nếu cần\n\
if [ ! -L public/storage ]; then\n\
  echo "Creating storage symlink..."\n\
  php artisan storage:link\n\
fi\n\
\n\
# Thực hiện migrations (chỉ khi DB_MIGRATE=true trong env)\n\
if [ "${DB_MIGRATE:-false}" = "true" ]; then\n\
  echo "Running database migrations..."\n\
  php artisan migrate --force\n\
fi\n\
\n\
echo "Starting Apache..."\n\
apache2-foreground\n\
' > /usr/local/bin/start-laravel.sh \
    && chmod +x /usr/local/bin/start-laravel.sh

# Expose cổng
EXPOSE 80

# Command để chạy khi container được khởi động
CMD ["/usr/local/bin/start-laravel.sh"]