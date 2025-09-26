#!/usr/bin/env bash
set -euo pipefail

echo "Waiting for DB..."
until nc -z -v -w30 db 3306; do echo "waiting for db"; sleep 2; done

php -v
php -m

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwX storage bootstrap/cache || true

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

# Ensure APP_CIPHER is valid (default to AES-256-CBC)
if ! grep -q "^APP_CIPHER=" .env; then
    echo "APP_CIPHER=AES-256-CBC" >> .env
else
    if ! grep -Eq "^APP_CIPHER=(AES-128-CBC|AES-256-CBC|AES-128-GCM|AES-256-GCM)$" .env; then
        sed -i 's/^APP_CIPHER=.*/APP_CIPHER=AES-256-CBC/' .env || true
    fi
fi

# Generate or repair APP_KEY if missing or invalid (must be base64:* for CBC/GCM)
CURRENT_KEY=$(grep -E '^APP_KEY=' .env | cut -d'=' -f2- || true)
if [ -z "${CURRENT_KEY}" ] || ! echo "${CURRENT_KEY}" | grep -Eq '^base64:[A-Za-z0-9+/=]+$'; then
    echo "[start] Generating APP_KEY (missing or invalid)"
    php artisan key:generate --force || echo "[start] WARNING: key:generate failed"
fi

# Refresh caches after ensuring key exists
php artisan config:clear || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

php-fpm


