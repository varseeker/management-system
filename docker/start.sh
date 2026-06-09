#!/bin/sh
set -e

cd /var/www/html

export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY belum diset. Generate dengan: php artisan key:generate --show"
    exit 1
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

php artisan storage:link 2>/dev/null || true

exec supervisord -c /etc/supervisord.conf
