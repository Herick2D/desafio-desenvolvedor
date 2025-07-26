#!/usr/bin/env sh
set -e

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775      /var/www/html/storage /var/www/html/bootstrap/cache

if [ ! -f /var/www/html/vendor/autoload.php ]; then
  composer install --no-progress --no-interaction
fi

cd /var/www/html

if [ ! -f .env ]; then
  cat > .env <<EOF
APP_NAME=${APP_NAME:-Laravel}
APP_ENV=${APP_ENV:-local}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-true}
APP_URL=${APP_URL:-http://localhost}

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-debug}

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-mysql}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-desafio_dev}
DB_USERNAME=${DB_USERNAME:-sail}
DB_PASSWORD=${DB_PASSWORD:-password}

QUEUE_CONNECTION=${QUEUE_CONNECTION:-redis}
CACHE_STORE=${CACHE_STORE:-redis}
REDIS_HOST=${REDIS_HOST:-redis}
REDIS_PASSWORD=${REDIS_PASSWORD:-null}
REDIS_PORT=${REDIS_PORT:-6379}

DB_MONGO_HOST=${DB_MONGO_HOST:-mongodb}
DB_MONGO_PORT=${DB_MONGO_PORT:-27017}
DB_MONGO_DATABASE=${DB_MONGO_DATABASE:-desafio_dev}

BROADCAST_CONNECTION=${BROADCAST_CONNECTION:-log}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
SESSION_DRIVER=${SESSION_DRIVER:-file}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
EOF
fi

if ! grep -q '^APP_KEY=' .env || [ -z "$(grep -E '^APP_KEY=' .env | cut -d '=' -f2)" ]; then
  php artisan key:generate --force
fi

php artisan config:clear

echo "Esperando MySQL em ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
while ! nc -z ${DB_HOST:-mysql} ${DB_PORT:-3306}; do
  sleep 1
done
 echo "MySQL pronto."

php artisan migrate --force

exec "$@"
