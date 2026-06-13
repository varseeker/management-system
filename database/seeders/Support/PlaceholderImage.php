<?php

namespace Database\Seeders\Support;

use App\Support\BorrowingImageStorage;
use Illuminate\Support\Facades\File;

class PlaceholderImage
{
    public static function create(string $relativePath, string $label, array $rgb = [70, 130, 180]): string
    {
        $relativePath = self::normalizeRelativePath($relativePath);
        $fullPath = storage_path('app/public/' . $relativePath);
        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (extension_loaded('gd')) {
            $image = imagecreatetruecolor(480, 360);
            $background = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
            imagefill($image, 0, 0, $background);
            $textColor = imagecolorallocate($image, 255, 255, 255);
            imagestring($image, 5, 16, 168, substr($label, 0, 42), $textColor);
            imagepng($image, $fullPath);
            imagedestroy($image);
        } else {
            file_put_contents(
                $fullPath,
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
            );
        }

        BorrowingImageStorage::persistPath($relativePath);

        return $relativePath;
    }

    private static function normalizeRelativePath(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', $relativePath);

        if (str_starts_with($relativePath, 'uploads/borrowings/')) {
            $relativePath = substr($relativePath, strlen('uploads/'));
        }

        if (! str_starts_with($relativePath, 'borrowings/')) {
            $relativePath = 'borrowings/' . ltrim($relativePath, '/');
        }

        return $relativePath;
    }
}
