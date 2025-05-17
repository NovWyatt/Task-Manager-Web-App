# Build stage
FROM composer:latest AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --ignore-platform-reqs

FROM node:18-alpine AS node
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --production
COPY . .
COPY --from=composer /app/vendor ./vendor
RUN npm run build

# Production stage
FROM php:8.2-apache
WORKDIR /var/www/html

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd \
    && a2enmod rewrite headers

# Copy application code
COPY . /var/www/html
COPY --from=composer /app/vendor /var/www/html/vendor
COPY --from=node /app/public/build /var/www/html/public/build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port
EXPOSE 80

# Start Apache
CMD php artisan key:generate --force && apache2-foreground