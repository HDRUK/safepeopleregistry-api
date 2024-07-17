#!/bin/bash

# php artisan swoole:http start &
php artisan serve --host=0.0.0.0 --port=8100 &
# if [ "${APP_ENV}" = "dev" ]; then
    php artisan queue:listen
# else
    # php artisan queue:work
# fi