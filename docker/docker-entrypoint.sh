#!/bin/sh

set -e

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
    echo "Criando arquivo .env a partir do .env.example"
    cp .env.example .env
fi

if [ -z "$(grep -E '^APP_KEY=' .env | cut -d '=' -f2-)" ]; then
    echo "Gerando chave da aplicação..."
    php artisan key:generate
fi

echo "Executando migrations..."
php artisan migrate --force

exec "$@"
