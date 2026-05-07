web: php artisan migrate --force --seed --class=SuperAdminSeeder && php artisan serve --host 0.0.0.0 --port $PORT
worker: php artisan queue:work --verbose --tries=3 --timeout=90
