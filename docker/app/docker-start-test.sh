#!/bin/bash

export APP_ENV=testing

php artisan config:clear
php artisan migrate:fresh --seed --env=testing
php artisan serve --host=0.0.0.0 --port=8000 &
php artisan test 

tail -f /dev/null
