#!/bin/sh
set -e

# Wait for the db to be reachable (compose healthcheck gates depends_on,
# but this is belt-and-suspenders).
until php -r "try { new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'ok'; } catch (Throwable \$e) { exit(1); }" >/dev/null 2>&1; do
    echo "[entrypoint] waiting for database..."
    sleep 2
done

# Apply schema changes. --force is needed because we're in production env.
php artisan migrate --force

# Warm the caches — faster responses, identifies config errors at boot.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage symlink (so uploaded files served at public/storage work).
php artisan storage:link || true

exec "$@"
