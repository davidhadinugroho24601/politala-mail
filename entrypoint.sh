#!/bin/sh
set -e

# Set up PHPMyAdmin session storage
mkdir -p /sessions
chmod 777 /sessions

# Only install Filament if it's not found in composer.lock
if ! grep -q "Filament\\Facades\\Filament" composer.lock 2>/dev/null; then
  echo "Filament and other dependencies not found. Installing..."
  composer require filament/filament spatie/laravel-medialibrary google/apiclient
else
  echo "Required packages already installed. Skipping composer require."
fi

# Build Filament assets
php artisan filament:assets

# Start the container's main process
exec "$@"
