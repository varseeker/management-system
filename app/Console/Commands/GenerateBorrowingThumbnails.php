<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateBorrowingThumbnails extends Command
{
    protected $signature = 'borrowings:generate-thumbs';

    protected $description = 'Buat thumbnail untuk foto peminjaman yang sudah ada (optimasi tampilan tabel)';

    public function handle(): int
    {
        if (! extension_loaded('gd')) {
            $this->error('Ekstensi PHP GD diperlukan.');

            return self::FAILURE;
        }

        $created = 0;
        $basePath = public_path('uploads/borrowings');

        if (! is_dir($basePath)) {
            $this->info('Tidak ada folder uploads/borrowings.');

            return self::SUCCESS;
        }

        foreach (['pengajuan', 'pengembalian'] as $prefix) {
            $directory = "{$basePath}/{$prefix}";

            if (! is_dir($directory)) {
                continue;
            }

            $thumbDirectory = "{$directory}/thumbs";

            if (! is_dir($thumbDirectory)) {
                File::makeDirectory($thumbDirectory, 0755, true);
            }

            foreach (glob("{$directory}/*.{jpg,jpeg,png,webp}", GLOB_BRACE) ?: [] as $imagePath) {
                $filename = basename($imagePath);
                $thumbPath = "{$thumbDirectory}/{$filename}";

                if (is_file($thumbPath)) {
                    continue;
                }

                if ($this->createThumb($imagePath, $thumbPath)) {
                    $created++;
                }
            }
        }

        $this->info("Thumbnail dibuat: {$created}");

        return self::SUCCESS;
    }

    private function createThumb(string $sourcePath, string $destPath): bool
    {
        $info = @getimagesize($sourcePath);

        if ($info === false) {
            return false;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp')
                ? @imagecreatefromwebp($sourcePath)
                : false,
            default => false,
        };

        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxWidth = 128;

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagescale($image, $maxWidth, max($newHeight, 1), IMG_BILINEAR_FIXED);
            imagedestroy($image);

            if ($resized === false) {
                return false;
            }

            $image = $resized;
        }

        $dest = str_ends_with(strtolower($destPath), '.png')
            ? $destPath
            : preg_replace('/\.[^.]+$/', '.jpg', $destPath);

        $saved = imagejpeg($image, $dest, 75);
        imagedestroy($image);

        return $saved;
    }
}
