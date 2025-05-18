#!/bin/bash
set -e

echo "Starting Laravel application..."

# Đảm bảo có App Key
if [ -z "$APP_KEY" ]; then
  echo "Generating application key..."
  php artisan key:generate --force
fi

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