# Kosongkan data aplikasi (struktur tabel tetap)
# Param: -KeepUsers untuk mempertahankan akun pengguna

param(
    [switch]$KeepUsers
)

$ErrorActionPreference = "Stop"

Set-Location $PSScriptRoot\..

Write-Host ">> Mengosongkan data aplikasi..." -ForegroundColor Cyan

if ($KeepUsers) {
    php artisan db:clear-data --force --keep-users
} else {
    php artisan db:clear-data --force
}

if ($LASTEXITCODE -ne 0) {
    Write-Host "Gagal." -ForegroundColor Red
    exit $LASTEXITCODE
}

Write-Host "Selesai." -ForegroundColor Green
