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

# Reset penuh tanpa Shell: set RUN_DB_RESET=true di Environment, redeploy, lalu hapus variabel.
if [ "$RUN_DB_RESET" = "true" ]; then
    echo "RUN_DB_RESET=true — menjalankan db:reset-data..."
    php artisan db:reset-data --force
fi

php artisan migrate --force

# Tier gratis Render tidak punya Shell — seed otomatis jika DB masih kosong.
# Paksa seed ulang: set RUN_SEED=true di Environment (hapus setelah deploy sukses).
USER_COUNT=$(php -r "
require __DIR__.'/vendor/autoload.php';
\$app = require __DIR__.'/bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    echo (int) \$app->make('db')->table('users')->count();
} catch (Throwable \$e) {
    echo 0;
}
")

if [ "$RUN_SEED" = "true" ] || [ "$USER_COUNT" = "0" ]; then
    echo "Menjalankan db:seed (RUN_SEED=${RUN_SEED:-false}, users=${USER_COUNT})..."
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan storage:link 2>/dev/null || true

mkdir -p \
    storage/app/public/menus \
    storage/app/public/borrowings/pengajuan/thumbs \
    storage/app/public/borrowings/pengembalian/thumbs \
    storage/app/public/borrowings/seed \
    public/uploads/borrowings/pengajuan \
    public/uploads/borrowings/pengembalian

chown -R www-data:www-data storage public/uploads 2>/dev/null || true

php artisan borrowings:sync-images-to-storage --quiet 2>/dev/null || true
php artisan borrowings:backfill-images-db --quiet 2>/dev/null || true
php artisan menus:backfill-images-db --quiet 2>/dev/null || true

exec supervisord -c /etc/supervisord.conf
