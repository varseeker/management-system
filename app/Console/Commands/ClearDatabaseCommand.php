<?php

namespace App\Console\Commands;

use App\Support\DatabaseMaintenance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearDatabaseCommand extends Command
{
    protected $signature = 'db:clear-data
                            {--force : Jalankan tanpa konfirmasi}
                            {--keep-users : Pertahankan data pengguna}';

    protected $description = 'Kosongkan data aplikasi tanpa menghapus struktur tabel';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Semua data aplikasi akan dikosongkan. Struktur tabel tetap ada. Lanjutkan?')) {
            $this->warn('Dibatalkan.');

            return self::FAILURE;
        }

        $tables = DatabaseMaintenance::existingDataTables();

        if ($this->option('keep-users')) {
            $tables = array_values(array_diff($tables, ['users', 'password_reset_tokens', 'sessions']));
        }

        if ($tables === []) {
            $this->warn('Tidak ada tabel yang dapat dikosongkan.');

            return self::FAILURE;
        }

        $this->info('Mengosongkan data tabel...');

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                DB::table($table)->truncate();
                $this->line("  - {$table}");
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('Membersihkan unggahan foto peminjaman...');
        DatabaseMaintenance::clearBorrowingUploads();

        $this->newLine();
        $this->info('Data aplikasi berhasil dikosongkan. Struktur tabel tidak berubah.');

        if ($this->option('keep-users')) {
            $this->comment('Data pengguna dipertahankan.');
        } else {
            $this->comment('Jalankan `php artisan db:reset-data` atau `composer db:reset` untuk mengisi data contoh.');
        }

        return self::SUCCESS;
    }
}
