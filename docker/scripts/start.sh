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

# Ensure APP_KEY is present and properly formatted in .env (should be base64:... for CBC/GCM)
if ! grep -q "^APP_KEY=" .env; then
    echo "[start] APP_KEY is missing in .env"
    php artisan key:generate
fi

# Run database migrations (non-interactive); do not block startup on failure
php artisan migrate --force --no-interaction || echo "[start] Migrate failed; continuing startup"

# Refresh all caches
php artisan config:clear
php artisan config:cache
php artisan cache:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Start PHP-FPM and Laravel scheduler concurrently
echo "[start] Launching PHP-FPM and scheduler (php artisan schedule:work)"

# Run PHP-FPM in background
php-fpm &
PHP_FPM_PID=$!

# Run Laravel scheduler worker in background
php artisan schedule:work --verbose --no-interaction &
SCHEDULER_PID=$!

# Forward termination signals to child processes
trap 'echo "[start] Shutting down..."; kill -TERM ${PHP_FPM_PID} ${SCHEDULER_PID} 2>/dev/null; wait' SIGINT SIGTERM

# Wait for any process to exit, then exit with that status
wait -n ${PHP_FPM_PID} ${SCHEDULER_PID}
EXIT_CODE=$?

echo "[start] One of the processes exited with code ${EXIT_CODE}. Exiting."
exit ${EXIT_CODE}
