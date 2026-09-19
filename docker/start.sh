#!/bin/sh
# Container entry point: prepare the app with the real environment variables
# (Railway injects them at start, not at build time), then hand over to
# supervisord, which runs nginx, php-fpm, the earthquake listener and the scheduler.
set -e

cd /var/www/html

# nginx must listen on the port Railway gives us.
export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/nginx-site.conf.template > /etc/nginx/conf.d/default.conf

# A volume mounted over storage/app/public starts empty: recreate what Laravel
# expects to exist.
mkdir -p storage/app/public storage/framework/cache storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache

# public/storage -> storage/app/public (profile pictures are served through it).
php artisan storage:link --force || true

# Cache config and views for speed. (Routes are not cached: routes/web.php
# uses closures.)
php artisan config:cache
php artisan view:cache

# Create/update the database tables. The database service may still be
# starting on the very first deploy, so retry for a while before giving up.
tries=0
until php artisan migrate --force; do
    tries=$((tries + 1))
    if [ "$tries" -ge 12 ]; then
        echo "Database still unreachable or a migration failed after $tries tries; giving up." >&2
        exit 1
    fi
    echo "migrate failed (attempt $tries) - retrying in 5s..." >&2
    sleep 5
done

# --- One-time setup, driven by environment variables set in the hosting
# dashboard (no shell access needed). Both are safe to leave on: they only
# fill in / create what is missing, and never overwrite an existing account.
# A failure here is logged but does not stop the app from starting.

# RUN_PRODUCTION_SEED=true : governorates, disaster types, relief teams.
if [ "${RUN_PRODUCTION_SEED:-false}" = "true" ]; then
    php artisan db:seed --class=ProductionSeeder --force \
        || echo "WARNING: production seeding failed - see the messages above." >&2
fi

# ADMIN_EMAIL + ADMIN_PASSWORD (+ optional ADMIN_NAME): the first super_admin.
# Remove ADMIN_PASSWORD from the dashboard once you have logged in.
if [ -n "${ADMIN_EMAIL:-}" ] && [ -n "${ADMIN_PASSWORD:-}" ]; then
    php artisan admin:create-super --no-interaction \
        || echo "WARNING: creating the admin account failed - see the messages above." >&2
fi

# The artisan commands above ran as root: give the web/worker user back
# ownership of everything it writes to.
chown -R www-data:www-data storage bootstrap/cache

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/app.conf
