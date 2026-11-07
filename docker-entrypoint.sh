#!/bin/bash
set -e

# Run database migrations if configured
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Optimization caches for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

exec "$@"

