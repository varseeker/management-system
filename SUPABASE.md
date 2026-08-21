# Setup Supabase (shared DB: Inventory + POS)

Inventory dan POS memakai **satu** database PostgreSQL Supabase yang sama.

Panduan lengkap (langkah buat project, URI, `pdo_pgsql`, migrate): lihat mirror di repo POS:

`../Tugas_Kampus/Semester-4/Web-Pro-III/uc_master-main/SUPABASE.md`

Ringkas:

1. Buat project di [supabase.com/dashboard](https://supabase.com/dashboard)
2. Ambil **Session** connection URI (port **5432**)
3. Aktifkan PHP `extension=pdo_pgsql`
4. Set di `.env` (URI sama dengan POS):

```env
DB_CONNECTION=pgsql
DB_SSLMODE=require
DATABASE_URL=postgresql://postgres.REF:PASSWORD@HOST:5432/postgres
DB_URL="${DATABASE_URL}"
```

5. Migrate Inventory **dulu**, baru POS:

```powershell
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
```
