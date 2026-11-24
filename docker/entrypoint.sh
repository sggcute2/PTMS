#!/bin/bash
set -e

# Wait for MySQL to be ready
if [ -n "$DB_HOST" ]; then
  echo "Menunggu koneksi database di $DB_HOST:${DB_PORT:-3306}..."
  while ! nc -z "$DB_HOST" "${DB_PORT:-3306}"; do
    sleep 1
  done
  echo "Database siap terhubung!"
fi

# Ensure storage and bootstrap/cache permissions
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

# If vendor directory doesn't exist, run composer install
if [ ! -d "vendor" ]; then
  echo "Menjalankan composer install..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
  echo "Mengenerate application key..."
  php artisan key:generate --force
fi

# Run database migrations
echo "Menjalankan migrasi database..."
php artisan migrate --force

echo "Inisialisasi aplikasi selesai!"

exec "$@"
