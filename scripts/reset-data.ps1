# Reset database + seeder
# Menghapus semua tabel, menjalankan migrasi ulang, dan mengisi data contoh.

$ErrorActionPreference = "Stop"

Set-Location $PSScriptRoot\..

Write-Host ">> Reset database dan seeder..." -ForegroundColor Cyan
php artisan db:reset-data --force

if ($LASTEXITCODE -ne 0) {
    Write-Host "Gagal." -ForegroundColor Red
    exit $LASTEXITCODE
}

Write-Host "Selesai." -ForegroundColor Green
