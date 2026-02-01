#!/bin/sh
set -e

cd /var/www/html

echo "Laravel container started"

# 1. Create .env if missing
if [ ! -f ".env" ]; then
  echo ".env not found, creating from .env.example"
  cp .env.example .env
fi

# 2. Install composer dependencies FIRST
if [ ! -d "vendor" ]; then
  echo "Installing composer dependencies..."
  composer install --no-interaction --prefer-dist
fi

# 3. Inject DB environment variables into .env
set_env () {
  KEY=$1
  VALUE=$2

  if grep -q "^$KEY=" .env; then
    sed -i "s|^$KEY=.*|$KEY=$VALUE|" .env
  else
    echo "$KEY=$VALUE" >> .env
  fi
}

set_env DB_CONNECTION "${DB_CONNECTION}"
set_env DB_HOST "${DB_HOST}"
set_env DB_PORT "${DB_PORT}"
set_env DB_DATABASE "${DB_DATABASE}"
set_env DB_USERNAME "${DB_USERNAME}"
set_env DB_PASSWORD "${DB_PASSWORD}"

# 4. Generate APP_KEY if missing
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

# 5. Wait for MySQL
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

# 6. Permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 7. Run migrations
php artisan migrate --force
php artisan optimize:clear

echo "Laravel ready"

exec "$@"
