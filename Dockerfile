FROM bitnami/laravel:10

COPY . .
RUN composer install --no-dev --no-interaction --optimize-autoloader

RUN npm ci --production && npm run build

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000