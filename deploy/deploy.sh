#!/bin/bash
set -e
cd /var/www/ospay

php artisan down --retry=60

git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

# worker harus dimuat ulang agar memakai kode terbaru
php artisan queue:restart

php artisan up
echo "Deploy selesai: $(date)"
