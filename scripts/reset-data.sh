#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

echo ">> Reset database dan seeder..."
php artisan db:reset-data --force

echo "Selesai."
