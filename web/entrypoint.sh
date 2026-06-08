#!/usr/bin/env bash

# Set nginx port
sed -i "s/listen PORT/listen ${PORT:-80}/g" /etc/nginx/nginx.conf

cd /app

# Pre-create the log file owned by www-data
mkdir -p /app/storage/logs
install -o www-data -g www-data -m 664 /dev/null /app/storage/logs/laravel.log 2>/dev/null || true

# Run migrations (skip if already installed)
if [ "$FORCE_MIGRATE" = "true" ] || [ ! -f /app/storage/installed ]; then
  echo "Running database migrations..."
  su-exec www-data php artisan migrate --force --no-interaction || true
  echo "APP_INSTALLED" > /app/storage/installed
fi

# Run seeders
if [ "$FORCE_SEED" = "true" ] || [ ! -f /app/storage/seeded ]; then
  echo "Running database seeders..."
  su-exec www-data php artisan db:seed --force --no-interaction 2>/dev/null || true
  echo "APP_SEEDED" > /app/storage/seeded
fi

echo "Starting nginx server..."
nginx

echo "Starting PHP-FPM..."
php-fpm -g "daemon off;"
