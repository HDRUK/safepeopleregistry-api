#!/bin/bash

if [ -e /var/www/.env ]; then
    source /var/www/.env
fi

echo "===> Starting Laravel setup..."

# base_command="php artisan octane:frankenphp --max-requests=250 --host=0.0.0.0 --port=8100"
# base_command="php artisan octane:frankenphp --max-requests=250 --host=0.0.0.0 --port=8100"
# base_command="php artisan octane:frankenphp \
#   --host=0.0.0.0 \
#   --port=8100 \
#   --max-requests=500 \
#   --workers=auto \
#   --frankenphp-config=/etc/frankenphp.yaml"

if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "dev" ]; then
    echo "Running in development mode with watch enabled"
    # base_command="$base_command --watch"

    if [ "$REBUILD_DB" = 1 ]; then
        # Completely clear down the data in local/dev envs
        echo "Rebuilding DB..."
        php artisan migrate:fresh
        php artisan db:seed --class=BaseDemoSeeder
    else
        php artisan migrate
    fi
else
    echo "Running in production mode"

    # Only forward-facing migrations anywhere else
    php artisan migrate

    # call the email template seeder to updateOrCreate without truncating first
    DISABLE_TRUNCATE=true php artisan db:seed --class=EmailTemplatesSeeder
    php artisan validation:generate-logs
fi

# Laravel caching for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan horizon &

# Launch Octane + FrankenPHP
php artisan octane:frankenphp \
  --host=0.0.0.0 \
  --port=8100 \
  --max-requests=500 \
  --workers=auto \
  --max-requests=10000 \
  --garbage-collect-threshold=500