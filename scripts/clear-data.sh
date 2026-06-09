#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

KEEP_USERS=false

while [[ $# -gt 0 ]]; do
    case "$1" in
        --keep-users)
            KEEP_USERS=true
            shift
            ;;
        *)
            echo "Opsi tidak dikenal: $1"
            exit 1
            ;;
    esac
done

echo ">> Mengosongkan data aplikasi..."

if [[ "$KEEP_USERS" == true ]]; then
    php artisan db:clear-data --force --keep-users
else
    php artisan db:clear-data --force
fi

echo "Selesai."
