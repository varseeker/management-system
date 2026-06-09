<?php

namespace App\Console\Commands;

use App\Support\DatabaseMaintenance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDatabaseCommand extends Command
{
    protected $signature = 'db:reset-data
                            {--force : Jalankan tanpa konfirmasi}';

    protected $description = 'Reset database (migrate:fresh) lalu isi ulang dengan seeder';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Semua data akan dihapus dan database diisi ulang dari seeder. Lanjutkan?')) {
            $this->warn('Dibatalkan.');

            return self::FAILURE;
        }

        $this->info('Membersihkan unggahan foto peminjaman...');
        DatabaseMaintenance::clearBorrowingUploads();

        $this->info('Menjalankan migrate:fresh --seed...');

        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->line(Artisan::output());

        if (! is_link(public_path('storage')) && ! is_dir(public_path('storage'))) {
            Artisan::call('storage:link');
            $this->line(Artisan::output());
        }

        $this->newLine();
        $this->info('Database berhasil direset dan diisi ulang.');
        $this->table(
            ['Peran', 'Nama', 'Surel', 'Kata sandi'],
            [
                ['Admin', 'Admin Sistem', 'admin@warkopkayu.test', 'password'],
                ['Pemilik', 'Dzaky Poke', 'dzaky.poke@warkopkayu.test', 'password'],
                ['Staf', 'Letoy', 'letoy@warkopkayu.test', 'password'],
                ['Staf', 'Ketoy', 'ketoy@warkopkayu.test', 'password'],
            ],
        );

        return self::SUCCESS;
    }
}
