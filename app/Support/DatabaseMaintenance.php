<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DatabaseMaintenance
{
    /**
     * Tabel aplikasi yang datanya boleh dikosongkan tanpa menghapus struktur.
     * Urutan dari child ke parent untuk keamanan saat foreign key aktif.
     */
    public static function dataTables(): array
    {
        return [
            'menu_sales',
            'borrowing_image_files',
            'borrowings',
            'menu_raw_material',
            'supplier_raw_material',
            'menus',
            'suppliers',
            'raw_materials',
            'items',
            'sessions',
            'password_reset_tokens',
            'users',
            'cache_locks',
            'cache',
            'job_batches',
            'failed_jobs',
            'jobs',
        ];
    }

    public static function clearBorrowingUploads(): void
    {
        $paths = [
            public_path('uploads/borrowings'),
            storage_path('app/public/borrowings'),
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            File::deleteDirectory($path);
            File::makeDirectory($path, 0755, true);
        }

        $gitkeep = public_path('uploads/borrowings/.gitkeep');

        if (! file_exists($gitkeep)) {
            File::put($gitkeep, '');
        }
    }

    public static function existingDataTables(): array
    {
        return array_values(array_filter(
            self::dataTables(),
            fn (string $table) => Schema::hasTable($table),
        ));
    }
}
