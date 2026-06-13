<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use App\Support\BorrowingImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackfillBorrowingImagesToDatabase extends Command
{
    protected $signature = 'borrowings:backfill-images-db';

    protected $description = 'Simpan salinan foto peminjaman ke database agar tidak hilang saat redeploy';

    public function handle(): int
    {
        $paths = collect();

        foreach ([
            storage_path('app/public/borrowings'),
            public_path('uploads/borrowings'),
        ] as $root) {
            if (! is_dir($root)) {
                continue;
            }

            foreach (File::allFiles($root) as $file) {
                if (str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR)) {
                    $paths->push('borrowings/' . str_replace('\\', '/', $file->getRelativePathname()));
                } else {
                    $paths->push('borrowings/' . str_replace('\\', '/', $file->getRelativePathname()));
                }
            }
        }

        Borrowing::query()
            ->select(['borrow_image', 'return_image'])
            ->get()
            ->each(function (Borrowing $borrowing) use ($paths) {
                foreach (['borrow_image', 'return_image'] as $column) {
                    $path = $borrowing->{$column};

                    if ($path) {
                        $paths->push(BorrowingImageStorage::normalizePath($path));
                    }
                }
            });

        $stored = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($paths->unique()->filter() as $path) {
            try {
                if (BorrowingImageStorage::persistPath($path)) {
                    $stored++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->warn("Gagal menyimpan {$path}: {$exception->getMessage()}");
            }
        }

        $this->info("Foto disimpan ke database: {$stored}");

        if ($skipped > 0) {
            $this->line("Dilewati (file tidak ditemukan): {$skipped}");
        }

        if ($failed > 0) {
            $this->error("Gagal: {$failed}");
        }

        return self::SUCCESS;
    }
}
