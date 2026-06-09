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

### Set APP_KEY (wajib)

Render `generateValue` **tidak cocok** untuk Laravel. Generate manual:

```powershell
# Di komputer lokal (folder proyek)
php artisan key:generate --show
```

Output contoh: `base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=`

1. Buka web service → **Environment**
2. Set `APP_KEY` = paste output **lengkap** (termasuk awalan `base64:`)
3. Simpan → redeploy

> **Penting:** Jangan ubah `APP_KEY` setelah production dipakai — session & data terenkripsi akan rusak.

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

### Error 500 / APP_KEY / "Unsupported cipher or incorrect key length"

`APP_KEY` dari Render auto-generate **salah format**. Laravel butuh:

```
base64:...   ← harus ada awalan ini
```

Perbaikan:
1. Lokal: `php artisan key:generate --show`
2. Render Environment → set `APP_KEY` = hasil command (paste penuh)
3. Redeploy

Jangan pakai `generateValue: true` di `render.yaml` untuk `APP_KEY`.

### Database connection error (`could not translate host name "dpg-..."`)

Penyebab umum: hostname database tidak lengkap atau env var salah.

**Perbaikan di Render Dashboard:**

1. Buka **PostgreSQL** (`warkop-db`) → tab **Info**
2. Salin **External Database URL** (bukan hostname pendek `dpg-xxx-a` saja)
   - Format benar: `postgresql://user:pass@dpg-xxx-a.singapore-postgres.render.com:5432/warkop`
3. Buka **Web Service** → **Environment**
4. Set / perbarui:
   - `DATABASE_URL` = External Database URL (paste penuh)
   - `DB_URL` = sama dengan `DATABASE_URL`
   - `DB_CONNECTION` = `pgsql`
   - `DB_SSLMODE` = `require`
5. **Hapus** env var manual yang bentrok: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (biarkan Laravel parse dari URL)
6. Pastikan web service dan database **region sama** (Singapore)
7. **Manual Deploy** → redeploy

> Jika pakai **Internal URL** (`dpg-xxx-a` tanpa domain), hanya berfungsi di jaringan privat Render region yang sama. Jika DNS gagal, gunakan **External URL**.

### Database connection error (umum)

- Pastikan PostgreSQL status **Available**
- Cek `DATABASE_URL` / `DB_URL` berisi URL lengkap, bukan hostname pendek

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
