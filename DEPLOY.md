# Deploy ke Render.com (Gratis)

Panduan deploy **Sistem Manajemen Warkop Kayu** ke [Render.com](https://render.com) — hosting gratis dengan auto-deploy dari GitHub dan database PostgreSQL gratis.

## Mengapa Render?

| Kriteria | Render (gratis) |
|----------|-----------------|
| Biaya | $0 (Free tier) |
| Database | PostgreSQL gratis (1 GB) |
| Deploy | Push ke GitHub → otomatis rebuild |
| Maintenance | Dashboard web, tanpa SSH wajib |
| Region | Singapore (dekat Indonesia) |

**Catatan tier gratis:**
- Web service **tidur** setelah ~15 menit tidak ada traffic → cold start ~30–60 detik saat pertama dibuka.
- **Foto peminjaman** disimpan di disk container (sementara). Foto hilang saat redeploy/restart. Data database (user, stok, riwayat) tetap aman di PostgreSQL.

---

## Prasyarat

1. Akun [GitHub](https://github.com) (gratis)
2. Akun [Render](https://render.com) (gratis, login via GitHub)
3. Git terpasang di komputer Anda

---

## Langkah 1 — Siapkan repositori GitHub

Di folder proyek (`inventory-system`):

```powershell
git init
git add .
git commit -m "Initial commit: inventory system"
```

Buat repo baru di GitHub (mis. `warkop-inventory`), lalu:

```powershell
git remote add origin https://github.com/USERNAME/warkop-inventory.git
git branch -M main
git push -u origin main
```

> Jangan commit file `.env` — sudah ada di `.gitignore`.

---

## Langkah 2 — Deploy dengan Blueprint

1. Buka [Render Dashboard](https://dashboard.render.com)
2. Klik **New** → **Blueprint**
3. Hubungkan repositori GitHub `warkop-inventory`
4. Render mendeteksi `render.yaml` — klik **Apply**
5. Tunggu build selesai (~5–10 menit pertama kali)

Render otomatis membuat:
- **Web Service** `warkop-inventory` (Docker)
- **PostgreSQL** `warkop-db` (gratis)

---

## Langkah 3 — Konfigurasi setelah deploy

### Set APP_URL

1. Buka web service → **Environment**
2. Edit `APP_URL` menjadi URL Render Anda, contoh:
   ```
   https://warkop-inventory.onrender.com
   ```
3. Simpan → service akan redeploy otomatis

### Isi data awal (seeder)

Setelah deploy pertama sukses, jalankan seeder sekali via **Shell** di dashboard Render:

```bash
php artisan db:seed --force
```

Akun demo setelah seeder — lihat [README.md](README.md#akun-demo-setelah-seeder).

---

## Langkah 4 — Verifikasi

1. Buka URL Render (contoh `https://warkop-inventory.onrender.com`)
2. Halaman login harus muncul
3. Login dengan akun demo (setelah seeder)
4. Cek dasbor, barang, peminjaman

---

## Maintenance sehari-hari

### Update aplikasi

```powershell
git add .
git commit -m "Perbaikan fitur X"
git push
```

Render otomatis rebuild & deploy. Migrasi database jalan otomatis saat container start.

### Reset database + seeder

Via Render Shell:

```bash
php artisan db:reset-data --force
```

### Lihat log

Dashboard Render → Web Service → **Logs**

---

## Variabel lingkungan (referensi)

| Variabel | Nilai production | Keterangan |
|----------|------------------|------------|
| `APP_ENV` | `production` | Otomatis via render.yaml |
| `APP_DEBUG` | `false` | Jangan `true` di production |
| `APP_KEY` | auto-generate | Render generate otomatis |
| `APP_URL` | URL Render Anda | **Wajib diset manual** |
| `DB_CONNECTION` | `pgsql` | Otomatis |
| `DB_URL` | dari database | Otomatis dari PostgreSQL |
| `SESSION_DRIVER` | `database` | Otomatis |
| `CACHE_STORE` | `database` | Otomatis |

---

## Troubleshooting

### Build gagal

- Cek log build di dashboard Render
- Pastikan `composer.lock` dan `package-lock.json` ikut di-commit
- Jika error `npm ci`: Dockerfile memakai `npm install` (lockfile Windows sering gagal di Linux). Push ulang lalu **Manual Deploy** → **Clear build cache & deploy**

### Error 500 / APP_KEY

- Pastikan `APP_KEY` ada di Environment
- Redeploy: **Manual Deploy** → **Clear build cache & deploy**

### Database connection error

- Pastikan PostgreSQL status **Available**
- Cek `DB_URL` terhubung ke database yang benar

### CSS/JS tidak load

- Pastikan `APP_URL` sudah benar (https, tanpa trailing slash)

### Cold start lambat

Normal di tier gratis. Tunggu ~30–60 detik setelah idle.

---

## Alternatif lain

| Platform | Kelebihan | Kekurangan |
|----------|-----------|------------|
| **Railway** | Mudah, MySQL/PostgreSQL | Kredit gratis terbatas |
| **Fly.io** | Disk persisten (foto aman) | Setup lebih teknis |
| **Shared hosting PHP** | Familiar | Tidak cocok untuk Laravel |
