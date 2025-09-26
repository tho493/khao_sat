#!/usr/bin/env bash
set -euo pipefail

echo "Waiting for DB..."
until nc -z -v -w30 db 3306; do echo "waiting for db"; sleep 2; done

php -v
php -m

if [ ! -f .env ]; then
    if [ -f env.docker.example ]; then
        echo "[start] Creating .env from env.docker.example"
        cp env.docker.example .env
    elif [ -f .env.example ]; then
        echo "[start] Creating .env from .env.example"
        cp .env.example .env
    else
        echo "[start] No env template found; creating minimal .env"
        echo "APP_ENV=production" > .env
        echo "APP_DEBUG=false" >> .env
        echo "APP_URL=http://localhost" >> .env
    fi
fi

# Generate app key if missing or empty
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=\s*$" .env; then
    echo "[start] Generating APP_KEY"
    php artisan key:generate --force || echo "[start] WARNING: key:generate failed"
fi

# Refresh caches after ensuring key exists
php artisan config:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

php-fpm


