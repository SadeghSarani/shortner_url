set -e

echo "Laravel container started"

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Running artisan commands..."

php artisan migrate --force
php artisan optimize:clear
php artisan config:clear

echo "Artisan commands finished"

exec "$@"
