FROM php:8.2-apache

# Cài đặt extensions PHP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Cấu hình Apache
RUN a2enmod rewrite headers
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy mã nguồn ứng dụng
COPY . .

# Cài đặt Composer nếu cần
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Kiểm tra và cài đặt vendor nếu cần
RUN if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then \
    composer install --no-dev --optimize-autoloader; \
  fi

# Đảm bảo .htaccess tồn tại
RUN if [ ! -f "public/.htaccess" ]; then \
    echo '<IfModule mod_rewrite.c>\n\
    <IfModule mod_negotiation.c>\n\
        Options -MultiViews -Indexes\n\
    </IfModule>\n\
    RewriteEngine On\n\
    # Handle Authorization Header\n\
    RewriteCond %{HTTP:Authorization} .\n\
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n\
    # Redirect Trailing Slashes If Not A Folder...\n\
    RewriteCond %{REQUEST_FILENAME} !-d\n\
    RewriteCond %{REQUEST_URI} (.+)/$\n\
    RewriteRule ^ %1 [L,R=301]\n\
    # Send Requests To Front Controller...\n\
    RewriteCond %{REQUEST_FILENAME} !-d\n\
    RewriteCond %{REQUEST_FILENAME} !-f\n\
    RewriteRule ^ index.php [L]\n\
</IfModule>' > public/.htaccess; \
  fi

# Thiết lập quyền
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Kiểm tra các file quan trọng
RUN ls -la && ls -la public && ls -la vendor

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]