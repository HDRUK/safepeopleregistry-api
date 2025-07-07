#!/bin/bash
while true; do
    php /var/www/artisan schedule:run --verbose --no-interaction >> /dev/stderr 2>&1
    sleep 60
done