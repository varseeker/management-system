# Setup Supabase (shared DB: Inventory + POS)

Inventory dan POS memakai **satu** database PostgreSQL Supabase.

Deploy di Render juga memakai Supabase (bukan Postgres Render). Panduan lengkap:

`../Tugas_Kampus/Semester-4/Web-Pro-III/uc_master-main/SUPABASE.md`

## Ringkas — Environment Render

Set di **warkop-inventory** dan **pos-warkop-kayu** (URI sama):

```env
DB_CONNECTION=pgsql
DB_SSLMODE=require
DATABASE_URL=postgresql://postgres.REF:PASSWORD@aws-0-ap-southeast-1.pooler.supabase.com:5432/postgres
DB_URL=<sama dengan DATABASE_URL>
```

POS saja: `DB_PREFIX=pos_`

Lalu redeploy Inventory → POS. Database Render `warkop-db` boleh dihapus.
