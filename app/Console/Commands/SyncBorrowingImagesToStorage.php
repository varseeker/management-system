<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncBorrowingImagesToStorage extends Command
{
    protected $signature = 'borrowings:sync-images-to-storage';

    protected $description = 'Salin foto peminjaman dari public/uploads ke storage (kompatibilitas deploy)';

    public function handle(): int
    {
        $legacyRoot = public_path('uploads/borrowings');
        $storageRoot = storage_path('app/public/borrowings');

        if (! is_dir($legacyRoot)) {
            $this->info('Tidak ada foto legacy di public/uploads/borrowings.');

            return self::SUCCESS;
        }

        $copied = 0;

        foreach (File::allFiles($legacyRoot) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $target = $storageRoot . '/' . $relative;

            if (is_file($target)) {
                continue;
            }

            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getPathname(), $target);
            $copied++;
        }

        $this->info("Foto disalin ke storage: {$copied}");

        return self::SUCCESS;
    }
}
