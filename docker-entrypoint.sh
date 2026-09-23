#!/bin/bash
set -e

# Adapt Apache listening port to Render's dynamic $PORT environment variable
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/*.conf

# Create storage directory structure
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

# Discover packages now that environment variables are available
php artisan package:discover --ansi || true

# Storage link and database migration
php artisan storage:link || true
php artisan migrate --force || true
php artisan db:seed --force || true
php artisan permission:cache-reset || true

# Cache configurations and routes
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Guarantee full ownership and read/write permissions for Apache (www-data)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Start Apache in foreground
exec apache2-foreground
