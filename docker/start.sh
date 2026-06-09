#!/bin/sh
set -e

cd /var/www/html

. /var/www/html/docker/fix-render-env.sh

export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY belum diset."
    echo "Jalankan lokal: php artisan key:generate --show"
    echo "Lalu paste hasilnya ke Environment Render (format: base64:...)"
    exit 1
fi

case "$APP_KEY" in
    base64:*)
        ;;
    *)
        echo "ERROR: APP_KEY format salah: '$APP_KEY'"
        echo "Harus diawali 'base64:' — generate dengan: php artisan key:generate --show"
        exit 1
        ;;
esac

if [ -z "$APP_URL" ] || [ "$APP_URL" = "http://localhost" ]; then
    echo "WARNING: APP_URL belum diset — CSS/JS mungkin tidak load."
    echo "Set APP_URL di Render Environment, contoh: https://warkop-inventory.onrender.com"
fi

if [ ! -f public/build/manifest.json ]; then
    echo "ERROR: public/build/manifest.json tidak ditemukan — Vite build gagal."
    exit 1
fi

php artisan config:clear
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan storage:link 2>/dev/null || true

exec supervisord -c /etc/supervisord.conf
