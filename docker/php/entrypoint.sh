#!/bin/sh
set -e

# Fix permissions on host-mounted volumes (Windows mounts ignore build-time chown)
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    sleep 2
done
echo "MySQL is ready."

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
