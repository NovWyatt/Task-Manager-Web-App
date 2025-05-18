FROM php:8.2-apache

# Cài đặt extensions và dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd \
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

# Thiết lập quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Tạo script khởi động
RUN echo '#!/bin/bash\n\
echo "Starting Laravel application..."\n\
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
# Thực hiện migrations nếu được thiết lập\n\
if [ "${DB_MIGRATE:-false}" = "true" ]; then\n\
  echo "Running database migrations..."\n\
  php artisan migrate --force\n\
fi\n\
\n\
# Khởi động Apache\n\
apache2-foreground' > /usr/local/bin/start-laravel.sh \
    && chmod +x /usr/local/bin/start-laravel.sh

# Expose port
EXPOSE 80

# Start Apache
CMD ["/usr/local/bin/start-laravel.sh"]