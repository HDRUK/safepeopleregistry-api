#!/bin/bash

php artisan swoole:http start &

# if [ "${APP_ENV}" = "dev" ]; then
    php artisan queue:listen
# else
    # php artisan queue:work
# fi