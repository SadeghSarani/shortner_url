#!/bin/sh
set -e

echo "Laravel container started"

cd /var/www/html

# Wait for MySQL
echo "Waiting for MySQL..."
until php -r "
try {
    new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
} catch (Exception \$e) {
    exit(1);
}
"; do
  sleep 2
done

echo "MySQL is ready"

# Install dependencies if missing
if [ ! -d "vendor" ]; then
  echo "Installing composer dependencies..."
  composer install --no-interaction --prefer-dist
fi

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Running artisan commands..."

php artisan migrate --force
php artisan optimize:clear
php artisan config:clear
php artisan key:generate

echo "Artisan commands finished"

exec "$@"
