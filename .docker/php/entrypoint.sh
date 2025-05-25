#!/bin/sh
set -e

# Set permissions for Laravel directories
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# permissions for PHPMyAdmin
mkdir -p /sessions

chmod 777 /sessions


# Check if Filament is already installed by looking for one of its classes
if ! grep -q "Filament\\Facades\\Filament" composer.lock 2>/dev/null; then
  echo "Filament and other dependencies not found. Installing..."
  composer require filament/filament spatie/laravel-medialibrary google/apiclient
else
  echo "Required packages already installed. Skipping composer require."
fi

php artisan filament:assets


exec "$@"