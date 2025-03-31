#!/bin/bash
set -e

#
mkdir -p /var/www

rm /var/www/var/*.db || true

if [ ! -f /var/www/.env ]; then
    cp .env.example .env
fi

composer clear-cache

composer install --no-interaction --prefer-dist --optimize-autoloader || {
    composer install --no-interaction --prefer-dist --optimize-autoloader
}

chmod -R o+s+w /var/www

exec "$@"
