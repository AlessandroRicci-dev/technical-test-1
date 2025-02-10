#!/bin/bash

export APP_ENV=local

php artisan migrate:fresh --seed
php artisan scout:import "App\Models\Order"
php artisan optimize 
php artisan serve --host=0.0.0.0 --port=8000

tail -f /dev/null
