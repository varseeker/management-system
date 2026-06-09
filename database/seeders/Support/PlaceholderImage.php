<?php

namespace Database\Seeders\Support;

class PlaceholderImage
{
    public static function create(string $relativePath, string $label, array $rgb = [70, 130, 180]): string
    {
        if (! str_starts_with($relativePath, 'uploads/')) {
            $relativePath = 'uploads/borrowings/' . ltrim($relativePath, '/');
        }

        $fullPath = public_path($relativePath);
        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
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

        return str_replace('\\', '/', $relativePath);
    }
}
