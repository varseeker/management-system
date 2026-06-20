<?php

namespace App\Console\Commands;

use App\Models\Menu;
use App\Support\MenuImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackfillMenuImagesToDatabase extends Command
{
    protected $signature = 'menus:backfill-images-db';

    protected $description = 'Simpan salinan foto menu ke database agar tidak hilang saat redeploy Render';

    public function handle(): int
    {
        $paths = collect();

        $menusRoot = storage_path('app/public/menus');

        if (is_dir($menusRoot)) {
            foreach (File::allFiles($menusRoot) as $file) {
                $paths->push('menus/'.str_replace('\\', '/', $file->getRelativePathname()));
            }
        }

        Menu::query()
            ->whereNotNull('image_path')
            ->pluck('image_path')
            ->each(function (?string $path) use ($paths) {
                if ($path) {
                    $paths->push(MenuImageStorage::normalizePath($path));
                }
            });

        $stored = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($paths->unique()->filter() as $path) {
            try {
                if (MenuImageStorage::persistPath($path)) {
                    $stored++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $exception) {
                $failed++;
                $this->warn("Gagal menyimpan {$path}: {$exception->getMessage()}");
            }
        }

        $this->info("Gambar menu disimpan ke database: {$stored}");

        if ($skipped > 0) {
            $this->line("Dilewati (file tidak ditemukan di disk): {$skipped}");
        }

        if ($failed > 0) {
            $this->error("Gagal: {$failed}");
        }

        return self::SUCCESS;
    }
}
