#!/bin/sh
set -e

echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    sleep 2
done
echo "MySQL is ready."

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
