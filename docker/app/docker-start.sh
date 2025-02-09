#!/bin/bash

export APP_ENV=local

composer install

php artisan migrate:fresh --seed
php artisan optimize 
php artisan serve --host=0.0.0.0 --port=8000

tail -f /dev/null
