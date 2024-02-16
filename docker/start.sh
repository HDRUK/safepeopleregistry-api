#!/bin/bash

php artisan serve --host=0.0.0.0 --port=8100 &
php artisan queue:work