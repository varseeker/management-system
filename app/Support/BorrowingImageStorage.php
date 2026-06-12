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

    private const DISK = 'public';

    public static function store(UploadedFile $file, string $prefix): string
    {
        $disk = Storage::disk(self::DISK);
        $baseDir = "borrowings/{$prefix}";
        $thumbDir = "borrowings/{$prefix}/thumbs";

        $disk->makeDirectory($baseDir);
        $disk->makeDirectory($thumbDir);

        $filename = Str::random(40) . '.jpg';
        $relativePath = "{$baseDir}/{$filename}";
        $absolutePath = $disk->path($relativePath);
        $thumbAbsolutePath = $disk->path("{$thumbDir}/{$filename}");

        if (self::saveOptimizedJpeg($file->getRealPath(), $absolutePath, self::MAX_WIDTH, self::JPEG_QUALITY)) {
            self::saveOptimizedJpeg($file->getRealPath(), $thumbAbsolutePath, self::THUMB_WIDTH, self::THUMB_QUALITY);

            return $relativePath;
        }

        $fallbackName = $file->hashName();
        $disk->putFileAs($baseDir, $file, $fallbackName);

        return "{$baseDir}/{$fallbackName}";
    }

    public static function url(?string $path): ?string
    {
        if (! $path || ! self::absolutePath($path)) {
            return null;
        }

        return self::serveUrl($path);
    }

    public static function thumbUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = self::normalizePath($path);

        if (preg_match('#^borrowings/([^/]+)/#', $normalized, $matches)) {
            $thumbRelative = preg_replace(
                '#^borrowings/([^/]+)/#',
                'borrowings/$1/thumbs/',
                $normalized,
            );

            if ($thumbRelative !== $normalized && self::absolutePath($thumbRelative)) {
                return self::serveUrl($thumbRelative);
            }
        }

        return self::url($path);
    }

    public static function absolutePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = self::normalizePath($path);

        $storageFile = storage_path('app/public/' . $normalized);

        if (is_file($storageFile)) {
            return $storageFile;
        }

        $legacyFile = public_path('uploads/' . $normalized);

        if (is_file($legacyFile)) {
            return $legacyFile;
        }

        if (is_file(public_path($path))) {
            return public_path($path);
        }

        return null;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $normalized = self::normalizePath($path);
        $absolute = self::absolutePath($path);

        if ($absolute && is_file($absolute)) {
            File::delete($absolute);
        }

        if (preg_match('#^borrowings/([^/]+)/#', $normalized)) {
            $thumbRelative = preg_replace(
                '#^borrowings/([^/]+)/#',
                'borrowings/$1/thumbs/',
                $normalized,
            );

            if ($thumbRelative !== $normalized) {
                $thumbAbsolute = self::absolutePath($thumbRelative);

                if ($thumbAbsolute && is_file($thumbAbsolute)) {
                    File::delete($thumbAbsolute);
                }
            }
        }

        Storage::disk(self::DISK)->delete($normalized);
    }

    public static function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        return ltrim($path, '/');
    }

    private static function serveUrl(string $path): string
    {
        return '/borrowings/images/' . self::normalizePath($path);
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

        $directory = dirname($destPath);

        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
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
}
