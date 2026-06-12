# Sistem Manajemen Warkop Kayu

Aplikasi manajemen inventori untuk warkop: barang, bahan baku, menu, pesanan, peminjaman barang, persetujuan, dan manajemen pengguna dengan peran **Admin**, **Pemilik**, dan **Staf**.

## Fitur Utama

- **Inventori barang** — kelola stok peralatan dan barang operasional
- **Bahan baku & menu** — resep menu otomatis mengurangi stok bahan baku saat pesanan diproses
- **Pesanan menu** — riwayat transaksi dengan filter, pencarian, dan sorting
- **Peminjaman barang** — pengajuan dengan foto, batas jumlah & jangka waktu, modal konfirmasi ketentuan
- **Persetujuan peminjaman** — setujui/tolak dengan catatan wajib, proses pengembalian + foto
- **Manajemen pengguna** — hanya Admin; registrasi publik dinonaktifkan
- **Ekspor CSV** — peminjaman dan pesanan menu (Admin & Pemilik)
- **Filter, pencarian, sorting** — pada tabel data penting

## Persyaratan

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL / MariaDB (atau SQLite untuk pengembangan)

## Instalasi

```bash
composer setup
```

Atau manual:

```bash
composer install
cp .env.example .env   # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Sesuaikan koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_inventaris
DB_USERNAME=root
DB_PASSWORD=
```

## Menjalankan Aplikasi

```bash
# Terminal 1 — server
php artisan serve

# Terminal 2 — asset (opsional, saat development)
npm run dev
```

Atau jalankan semua sekaligus:

```bash
composer dev
```

Buka http://127.0.0.1:8000

## Akun Demo (setelah seeder)

| Peran   | Nama         | Surel                          | Kata sandi |
|---------|--------------|--------------------------------|------------|
| Admin   | Admin Sistem | admin@warkopkayu.test          | password   |
| Pemilik | Dzaky Poke   | dzaky.poke@warkopkayu.test     | password   |
| Staf    | Letoy        | letoy@warkopkayu.test          | password   |
| Staf    | Ketoy        | ketoy@warkopkayu.test          | password   |

## Peran & Akses Menu

| Menu                 | Admin | Pemilik | Staf |
|----------------------|:-----:|:-------:|:----:|
| Dasbor               | ✓     | ✓       | ✓    |
| Barang, Bahan, Pemasok, Menu | ✓ | ✓   | ✗    |
| Pesanan              | ✓     | ✓       | ✓    |
| Peminjaman           | ✓     | ✓       | ✓    |
| Persetujuan          | ✓     | ✓       | ✗    |
| Manajemen Pengguna   | ✓     | ✗       | ✗    |
| Ekspor data          | ✓     | ✓       | ✗    |

## Skrip Database

### Reset data + seeder (migrate:fresh)

Menghapus **semua tabel**, menjalankan migrasi ulang, mengisi data contoh, dan membersihkan foto peminjaman.

```bash
# Artisan (dengan konfirmasi)
php artisan db:reset-data

# Artisan (tanpa konfirmasi)
php artisan db:reset-data --force

# Composer
composer db:reset

# PowerShell (Windows)
.\scripts\reset-data.ps1

# Bash (Linux / macOS / Git Bash)
bash scripts/reset-data.sh
```

### Kosongkan data (struktur tabel tetap)

Menghapus **isi data** dari tabel aplikasi tanpa `migrate:fresh` — skema/migrasi tidak diubah.

```bash
# Artisan (dengan konfirmasi)
php artisan db:clear-data

# Artisan (tanpa konfirmasi)
php artisan db:clear-data --force

# Pertahankan akun pengguna
php artisan db:clear-data --force --keep-users

# Composer
composer db:clear

# PowerShell (Windows)
.\scripts\clear-data.ps1
.\scripts\clear-data.ps1 -KeepUsers

# Bash
bash scripts/clear-data.sh
bash scripts/clear-data.sh --keep-users
```

| Perintah | Tabel di-drop | Migrasi ulang | Seeder | Foto peminjaman |
|----------|:-------------:|:-------------:|:------:|:---------------:|
| `db:reset-data` | ✓ | ✓ | ✓ | Dihapus |
| `db:clear-data` | ✗ | ✗ | ✗ | Dihapus |

> **Peringatan:** `db:reset-data` menghapus seluruh data permanen. Gunakan hanya di lingkungan development atau saat ingin memulai dari awal.

## Konfigurasi Peminjaman

Edit `config/inventory.php`:

```php
'max_quantity' => 5,      // Maksimal unit per pengajuan
'max_loan_days' => 3,     // Maksimal hari peminjaman
```

## Seeder

```bash
php artisan db:seed                  # Semua seeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=StockMenuSeeder
php artisan db:seed --class=ItemSeeder
php artisan db:seed --class=MenuSaleSeeder
php artisan db:seed --class=BorrowingSeeder
php artisan db:seed --class=SupplierSeeder
```

Urutan seeder di `DatabaseSeeder`: User → Menu/Bahan → Pemasok → Barang → Pesanan → Peminjaman.

## Struktur Proyek (ringkas)

```
app/
  Console/Commands/     # db:reset-data, db:clear-data
  Http/Controllers/     # Logika aplikasi
  Support/              # RoleAccess, DatabaseMaintenance, dll.
config/inventory.php    # Ketentuan peminjaman & peran
database/seeders/       # Data contoh
public/uploads/         # Foto peminjaman (pengajuan & pengembalian)
resources/views/        # Tampilan Blade
scripts/                # reset-data & clear-data (ps1/sh)
```

## Pengujian

```bash
composer test
```

## Deploy (Production)

Deploy gratis ke cloud dengan auto-deploy dari GitHub:

```bash
# Lihat panduan lengkap
cat DEPLOY.md
```

Ringkas: push ke GitHub → connect di [Render.com](https://render.com) via `render.yaml` → set `APP_URL` → jalankan `php artisan db:seed --force` sekali.

## Lisensi

Proyek ini menggunakan [MIT license](https://opensource.org/licenses/MIT).
