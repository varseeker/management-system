<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BorrowingImageStorage
{
    private const MAX_WIDTH = 1280;

    private const JPEG_QUALITY = 82;

    private const THUMB_WIDTH = 128;

    private const THUMB_QUALITY = 75;

    public static function store(UploadedFile $file, string $prefix): string
    {
        $directory = public_path("uploads/borrowings/{$prefix}");
        $thumbDirectory = public_path("uploads/borrowings/{$prefix}/thumbs");

        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (! is_dir($thumbDirectory)) {
            File::makeDirectory($thumbDirectory, 0755, true);
        }

        $filename = Str::random(40) . '.jpg';
        $relativePath = "uploads/borrowings/{$prefix}/{$filename}";
        $absolutePath = public_path($relativePath);
        $thumbAbsolutePath = public_path("uploads/borrowings/{$prefix}/thumbs/{$filename}");

        if (self::saveOptimizedJpeg($file->getRealPath(), $absolutePath, self::MAX_WIDTH, self::JPEG_QUALITY)) {
            self::saveOptimizedJpeg($file->getRealPath(), $thumbAbsolutePath, self::THUMB_WIDTH, self::THUMB_QUALITY);

            return $relativePath;
        }

        $fallbackName = $file->hashName();
        $file->move($directory, $fallbackName);

        return "uploads/borrowings/{$prefix}/{$fallbackName}";
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, 'uploads/')) {
            return self::publicUrl($path);
        }

        if (str_starts_with($path, 'borrowings/')) {
            $uploadsPath = 'uploads/' . $path;

            if (is_file(public_path($uploadsPath))) {
                return self::publicUrl($uploadsPath);
            }
        }

        if (is_file(public_path($path))) {
            return self::publicUrl($path);
        }

        if (is_file(public_path('storage/' . $path))) {
            return self::publicUrl('storage/' . $path);
        }

        if (is_file(storage_path('app/public/' . $path))) {
            return route('borrowings.image', ['path' => $path]);
        }

        return self::publicUrl('storage/' . $path);
    }

    public static function thumbUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, 'uploads/borrowings/')) {
            $thumbPath = preg_replace(
                '#^uploads/borrowings/([^/]+)/#',
                'uploads/borrowings/$1/thumbs/',
                $path,
            );

            if ($thumbPath !== $path && is_file(public_path($thumbPath))) {
                return self::publicUrl($thumbPath);
            }
        }

        return self::url($path);
    }

    public static function absolutePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, 'uploads/') && is_file(public_path($path))) {
            return public_path($path);
        }

        if (is_file(public_path('storage/' . $path))) {
            return public_path('storage/' . $path);
        }

        if (is_file(storage_path('app/public/' . $path))) {
            return storage_path('app/public/' . $path);
        }

        return null;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $absolute = self::absolutePath($path);

        if ($absolute && is_file($absolute)) {
            File::delete($absolute);
        }

        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, 'uploads/borrowings/')) {
            $thumbPath = preg_replace(
                '#^uploads/borrowings/([^/]+)/#',
                'uploads/borrowings/$1/thumbs/',
                $path,
            );

            if ($thumbPath !== $path && is_file(public_path($thumbPath))) {
                File::delete(public_path($thumbPath));
            }
        }

        if (! $absolute) {
            Storage::disk('public')->delete($path);
        }
    }

    private static function saveOptimizedJpeg(
        string $sourcePath,
        string $destPath,
        int $maxWidth,
        int $quality,
    ): bool {
        if (! extension_loaded('gd')) {
            return false;
        }

        $image = self::readImage($sourcePath);

        if ($image === null) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);

            return false;
        }

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagescale($image, $maxWidth, max($newHeight, 1), IMG_BILINEAR_FIXED);

            imagedestroy($image);

            if ($resized === false) {
                return false;
            }

            $image = $resized;
        }

        $saved = imagejpeg($image, $destPath, $quality);
        imagedestroy($image);

        return $saved;
    }

    /**
     * @return \GdImage|resource|null
     */
    private static function readImage(string $sourcePath)
    {
        $info = @getimagesize($sourcePath);

        if ($info === false) {
            return null;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp')
                ? @imagecreatefromwebp($sourcePath)
                : false,
            default => false,
        };

        return $image === false ? null : $image;
    }

    private static function publicUrl(string $path): string
    {
        return '/' . ltrim(str_replace('\\', '/', $path), '/');
    }
}
