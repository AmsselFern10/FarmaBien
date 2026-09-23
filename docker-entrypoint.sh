#!/bin/bash
set -e

# Adapt Apache listening port to Render's dynamic $PORT environment variable
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/*.conf

# Discover packages now that environment variables are available
php artisan package:discover --ansi || true

# Storage link and database migration
php artisan storage:link || true
php artisan migrate --force || true
php artisan db:seed --force || true

# Cache configurations and routes
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start Apache in foreground
exec apache2-foreground
