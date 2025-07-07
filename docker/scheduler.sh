#!/bin/bash
while true; do
    php /var/www/artisan schedule:run --verbose --no-interaction >> /dev/stdout 2>> /dev/stderr
    sleep 60
done