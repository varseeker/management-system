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
- **Shell tidak tersedia** di tier gratis — migrasi & seed jalan otomatis saat deploy (lihat bawah).
- Foto peminjaman dan **gambar menu** disimpan di **PostgreSQL** (persisten), bukan hanya di disk container.

---

### Gambar menu hilang setelah redeploy

**Penyebab:** File upload disimpan di disk container (`storage/app/public/menus/`) yang bersifat sementara di Render. Path di tabel `menus` tetap ada, tetapi file fisiknya hilang saat redeploy/restart.

**Perbaikan (otomatis setelah deploy kode terbaru):**
- Setiap upload gambar menu disalin ke **PostgreSQL** (tabel `menu_image_files`)
- Gambar dilayani dari database jika file disk tidak ada
- Saat deploy, `menus:backfill-images-db` mengisi ulang dari file yang masih ada di disk

**Gambar lama yang sudah hilang dari disk** tidak bisa dipulihkan otomatis — unggah ulang di halaman edit menu.

---

### Foto peminjaman tidak muncul / hilang setelah login ulang

**Penyebab:** Disk container Render bersifat sementara — file di `storage/` hilang saat restart/redeploy, sementara path di database tetap ada.

**Perbaikan (otomatis setelah deploy kode terbaru):**
- Setiap upload foto disalin ke **PostgreSQL** (tabel `borrowing_image_files`)
- Gambar dilayani dari database jika file disk tidak ada
- Saat deploy, `borrowings:backfill-images-db` mengisi ulang dari file yang masih ada

**Setelah push kode terbaru:** redeploy sekali. Foto **baru** langsung aman. Foto **lama** yang sudah hilang dari disk tidak bisa dipulihkan — unggah ulang atau jalankan `RUN_SEED=true` untuk data demo.

> Data peminjaman (teks, status, path) tetap di PostgreSQL. Hanya **isi file gambar** yang sebelumnya hilang jika belum sempat tersimpan ke database.

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

### Set APP_URL (wajib — tanpa ini CSS/JS tidak load)

1. Buka web service → **Environment**
2. Edit `APP_URL` menjadi URL Render Anda **dengan https**, tanpa trailing slash:
   ```
   https://warkop-inventory.onrender.com
   ```
3. Simpan → service akan redeploy otomatis

> Jika `APP_URL` kosong atau `http://localhost`, halaman tampil tanpa styling.

### Data awal (seeder) — otomatis, tanpa Shell

> **Shell Render hanya untuk plan berbayar (Starter+).** Tier gratis **tidak bisa** buka Shell.

Tidak perlu Shell — seeder jalan otomatis saat container start jika tabel `users` masih kosong.

| Perintah | Kapan jalan | Butuh Shell? |
|----------|-------------|:------------:|
| `migrate --force` | Setiap deploy | Tidak |
| `db:seed --force` | DB kosong (0 user) | Tidak |
| `db:reset-data` | Manual via env var | Tidak |

**Paksa seed ulang** (mis. setelah hapus data): di Environment set `RUN_SEED=true` → redeploy → hapus `RUN_SEED` setelah sukses.

**Reset database penuh** tanpa Shell: set `RUN_DB_RESET=true` → redeploy → hapus variabel setelah sukses.

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

### Reset database + seeder (tanpa Shell)

1. Environment → tambah `RUN_DB_RESET=true`
2. Simpan → tunggu redeploy
3. **Hapus** `RUN_DB_RESET` setelah sukses (cegah reset tiap deploy)

### Lihat log

Dashboard Render → Web Service → **Logs**

---

## Integrasi POS (`pos-warkop-kayu`)

Inventory dan POS adalah **dua web service terpisah** di Render yang saling terhubung lewat API + database shared.

### Yang harus diset di Inventory (`warkop-inventory`)

| Variabel | Nilai | Keterangan |
|----------|-------|------------|
| `APP_URL` | `https://warkop-inventory.onrender.com` | Wajib untuk CSS/JS |
| `INVENTORY_API_TOKEN` | token rahasia Anda | POS memakai ini sebagai Bearer token |
| `POS_SERVICE_URL` | `https://pos-warkop-kayu.onrender.com` | Link "Kasir (POS)" di sidebar |

### Yang harus diset di POS (`pos-warkop-kayu`)

| Variabel | Nilai | Keterangan |
|----------|-------|------------|
| `APP_URL` | `https://pos-warkop-kayu.onrender.com` | Wajib untuk asset CSS/JS |
| `DATABASE_URL` / `DB_URL` | **External Database URL** `warkop-db` | Database **sama** dengan inventory |
| `INVENTORY_SERVICE_URL` | `https://warkop-inventory.onrender.com` | Base URL inventory |
| `INVENTORY_API_TOKEN` | **sama persis** dengan inventory | Penghubung API |
| `INVENTORY_SERVICE_ENABLED` | `true` | Sudah default di blueprint POS |
| `MIDTRANS_*` | kunci Midtrans | Pembayaran QRIS di POS |

### Token API (penting)

```
INVENTORY_API_TOKEN di warkop-inventory  ═══  INVENTORY_API_TOKEN di pos-warkop-kayu
```

Buat satu token kuat, paste ke **kedua** service, lalu redeploy keduanya.

### Alur koneksi

1. POS login → baca user dari tabel `users` (shared DB, tanpa prefix)
2. POS sync menu → `GET {INVENTORY_SERVICE_URL}/api/menus` + Bearer token
3. POS checkout → `POST {INVENTORY_SERVICE_URL}/api/orders` + Bearer token
4. Inventory sidebar → link ke `{POS_SERVICE_URL}`

### Verifikasi cepat

```bash
curl -H "Authorization: Bearer <INVENTORY_API_TOKEN>" \
  https://warkop-inventory.onrender.com/api/menus
```

Login POS: `letoy@warkopkayu.test` / `password` (role **staff**).

Panduan lengkap POS: lihat `DEPLOY.md` di repo `uc_master-main`.

---

## Variabel lingkungan (referensi)

| Variabel | Nilai production | Keterangan |
|----------|------------------|------------|
| `APP_ENV` | `production` | Otomatis via render.yaml |
| `APP_DEBUG` | `false` | Jangan `true` di production |
| `APP_KEY` | `base64:...` | Generate: `php artisan key:generate --show` |
| `RUN_SEED` | `true` (opsional) | Paksa seed ulang, hapus setelah deploy |
| `RUN_DB_RESET` | `true` (opsional) | Reset DB penuh, hapus setelah deploy |
| `APP_URL` | URL Render Anda | **Wajib diset manual** |
| `INVENTORY_API_TOKEN` | token rahasia | **Wajib** — samakan dengan POS |
| `POS_SERVICE_URL` | URL service POS | **Wajib** setelah POS deploy |
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

Penyebab: hostname database internal Render (`dpg-xxx-a`) tidak bisa di-resolve DNS.

**Otomatis (setelah deploy terbaru):** `docker/fix-render-env.sh` mengubah hostname internal ke eksternal saat container start.

**Manual di Render Dashboard (jika masih gagal):**

1. Buka **PostgreSQL** (`warkop-db`) → tab **Info**
2. Salin **External Database URL** lengkap:
   `postgresql://user:pass@dpg-xxx-a.singapore-postgres.render.com:5432/warkop`
3. **Web Service** → **Environment** → set:
   - `DATABASE_URL` dan `DB_URL` = URL tersebut
   - `DB_CONNECTION` = `pgsql`
   - `DB_SSLMODE` = `require`
   - `RENDER_REGION` = `singapore`
4. **Hapus** `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
5. Redeploy

### CSS/JS tidak load (halaman tanpa styling)

Penyebab umum:

| Penyebab | Perbaikan |
|----------|-----------|
| `APP_URL` belum diset | Set ke `https://nama-app.onrender.com` |
| `APP_KEY` salah format | `php artisan key:generate --show` → paste ke Render |
| File `public/hot` ada | Dihapus otomatis saat start; jangan commit file ini |
| Vite build gagal | Cek log build Docker, pastikan `public/build/manifest.json` ada |

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
