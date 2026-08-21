#!/bin/sh
# Tunggu database siap sebelum migrate (Render free / cold start / SSL flaky).
# Dipakai dari start.sh / entrypoint.sh.

wait_for_database() {
    max_attempts="${DB_WAIT_ATTEMPTS:-30}"
    sleep_seconds="${DB_WAIT_SLEEP:-2}"
    attempt=1

    echo "Menunggu koneksi database (max ${max_attempts}x, interval ${sleep_seconds}s)..."

    while [ "$attempt" -le "$max_attempts" ]; do
        if php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    Illuminate\Support\Facades\DB::connection()->getPdo();
    Illuminate\Support\Facades\DB::select("select 1");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
' 2>/tmp/db-wait-error.txt; then
            echo "Database siap (percobaan ${attempt})."
            return 0
        fi

        echo "Belum siap (${attempt}/${max_attempts}): $(head -c 200 /tmp/db-wait-error.txt 2>/dev/null)"
        attempt=$((attempt + 1))
        sleep "$sleep_seconds"
    done

    echo "ERROR: Database tidak merespons setelah ${max_attempts} percobaan."
    cat /tmp/db-wait-error.txt 2>/dev/null || true
    return 1
}
