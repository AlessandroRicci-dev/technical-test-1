#!/bin/bash

export APP_ENV=testing

php artisan config:clear
php artisan db:wipe
php artisan migrate --seed &
php artisan serve --host=0.0.0.0 --port=8000 &
php artisan test 

tail -f /dev/null
