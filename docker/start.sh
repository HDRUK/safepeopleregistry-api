#!/bin/bash

if [ -e /var/www/.env ]; then
    source /var/www/.env
fi

if [ $APP_ENV = 'local' ] || [ $APP_ENV = 'dev' ]; then
    echo 'running in dev mode - with watch'
    php artisan octane:start --host=0.0.0.0 --port=8100 --watch &
else
    php artisan octane:state --host=0.0.0.0 --port=8100 &
fi

php artisan queue:listen
