FROM php:8.2-apache

# Cài đặt extensions và dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \  # Thêm libzip-dev
    libpng-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd \  # Thêm zip
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

# Thêm vào Dockerfile
COPY config/backup.php /var/www/html/config/backup.php

# Thiết lập quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Tạo script khởi động
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port
EXPOSE 80

# Start Apache
CMD ["/usr/local/bin/docker-entrypoint.sh"]