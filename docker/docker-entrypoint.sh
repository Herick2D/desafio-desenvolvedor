#!/usr/bin/env sh
set -e

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775      /var/www/html/storage /var/www/html/bootstrap/cache

if [ ! -f /var/www/html/vendor/autoload.php ]; then
  composer install --no-progress --no-interaction
fi

cd /var/www/html

if [ ! -f ".env" ]; then
  cp .env.example .env
fi
if [ -z "$(grep -E '^APP_KEY=' .env | cut -d '=' -f2-)" ]; then
  php artisan key:generate
fi

php artisan config:clear

echo "Esperando MySQL em mysql:3306..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "MySQL pronto."

php artisan migrate --force

exec "$@"