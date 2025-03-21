#!/bin/bash

if [ -e /var/www/.env ]; then
    source /var/www/.env
fi

base_command="php artisan octane:frankenphp --host=0.0.0.0 --port=8100"

if [ $APP_ENV = 'local' ] || [ $APP_ENV = 'dev' ]; then
    echo 'running in dev mode - with watch'
    # base_command="$base_command --watch"

    if [ $REBUILD_DB = 1 ]; then
        # Completely clear down the data in local/dev envs
        php artisan migrate:fresh
        php artisan db:seed --class=BaseDemoSeeder
    fi
else
    # Only forward-facing migrations anywhere else
    php artisan migrate
    # TODO - Remove this. This is a do-once on initial deployment
    php artisan db:seed --class=BaseProdSeeder
    echo "running in prod mode"
fi

# Add workers option if OCTANE_WORKERS is set
if [ -n "$OCTANE_WORKERS" ]; then
    base_command="$base_command --workers=${OCTANE_WORKERS}"
fi

$base_command &

php artisan horizon
