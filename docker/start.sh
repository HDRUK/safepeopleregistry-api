#!/bin/bash

if [ -e /var/www/.env ]; then
    source /var/www/.env
fi

php artisan migrate

base_command="php artisan octane:start --host=0.0.0.0 --port=8100"

if [ $APP_ENV = 'local' ] || [ $APP_ENV = 'dev' ]; then
    echo 'running in dev mode - with watch'
    base_command="$base_command --watch"

    php artisan db:seed --class=BaseDemoSeeder
else
    php artisan db:seed --class=BaseProdSeeder
    echo "running in prod mode"
fi

# Add workers option if OCTANE_WORKERS is set
if [ -n "$OCTANE_WORKERS" ]; then
    base_command="$base_command --workers=${OCTANE_WORKERS}"
fi

$base_command &

php artisan horizon
