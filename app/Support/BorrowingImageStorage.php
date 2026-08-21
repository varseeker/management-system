<?php

namespace App\Support;

use App\Models\BorrowingImageFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
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
        $thumbRelativePath = "{$thumbDir}/{$filename}";
        $thumbAbsolutePath = $disk->path($thumbRelativePath);

        if (self::saveOptimizedJpeg($file->getRealPath(), $absolutePath, self::MAX_WIDTH, self::JPEG_QUALITY)) {
            self::saveOptimizedJpeg($file->getRealPath(), $thumbAbsolutePath, self::THUMB_WIDTH, self::THUMB_QUALITY);
            self::persistPath($relativePath);
            self::persistPath($thumbRelativePath);

            return $relativePath;
        }

        $fallbackName = $file->hashName();
        $relativePath = "{$baseDir}/{$fallbackName}";
        $disk->putFileAs($baseDir, $file, $fallbackName);
        self::persistPath($relativePath);

        return $relativePath;
    }

    public static function url(?string $path): ?string
    {
        if (! $path || ! self::exists($path)) {
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

        if (preg_match('#^borrowings/([^/]+)/#', $normalized)) {
            $thumbRelative = preg_replace(
                '#^borrowings/([^/]+)/#',
                'borrowings/$1/thumbs/',
                $normalized,
            );

            if ($thumbRelative !== $normalized && self::exists($thumbRelative)) {
                return self::serveUrl($thumbRelative);
            }
        }

        return self::url($path);
    }

    public static function exists(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $normalized = self::normalizePath($path);

        if (self::absolutePathOnDisk($normalized) !== null) {
            return true;
        }

        return self::hasDatabaseRecord($normalized);
    }

    public static function contents(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = self::normalizePath($path);
        $diskPath = self::absolutePathOnDisk($normalized);

        if ($diskPath !== null) {
            return file_get_contents($diskPath) ?: null;
        }

        if (! self::hasDatabaseRecord($normalized)) {
            return null;
        }

        return PostgresBinary::decode(
            BorrowingImageFile::query()
                ->where('path', $normalized)
                ->value('contents')
        );
    }

    public static function mimeType(?string $path): string
    {
        if (! $path) {
            return 'application/octet-stream';
        }

        $normalized = self::normalizePath($path);
        $diskPath = self::absolutePathOnDisk($normalized);

        if ($diskPath !== null) {
            return mime_content_type($diskPath) ?: 'image/jpeg';
        }

        return BorrowingImageFile::query()
            ->where('path', $normalized)
            ->value('mime_type') ?? 'image/jpeg';
    }

    public static function absolutePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = self::normalizePath($path);
        $diskPath = self::absolutePathOnDisk($normalized);

        if ($diskPath !== null) {
            return $diskPath;
        }

        if (self::hasDatabaseRecord($normalized)) {
            return null;
        }

        return null;
    }

    public static function persistPath(string $path): bool
    {
        if (! Schema::hasTable('borrowing_image_files')) {
            return false;
        }

        $normalized = self::normalizePath($path);
        $diskPath = self::absolutePathOnDisk($normalized);

        if ($diskPath === null) {
            return false;
        }

        $contents = file_get_contents($diskPath);

        if ($contents === false || $contents === '') {
            return false;
        }

        PostgresBinary::upsert(
            'borrowing_image_files',
            $normalized,
            mime_content_type($diskPath) ?: 'image/jpeg',
            $contents,
        );

        return true;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $normalized = self::normalizePath($path);
        $diskPath = self::absolutePathOnDisk($normalized);

        if ($diskPath !== null && is_file($diskPath)) {
            File::delete($diskPath);
        }

        if (preg_match('#^borrowings/([^/]+)/#', $normalized)) {
            $thumbRelative = preg_replace(
                '#^borrowings/([^/]+)/#',
                'borrowings/$1/thumbs/',
                $normalized,
            );

            if ($thumbRelative !== $normalized) {
                $thumbDiskPath = self::absolutePathOnDisk($thumbRelative);

                if ($thumbDiskPath !== null && is_file($thumbDiskPath)) {
                    File::delete($thumbDiskPath);
                }

                self::deleteDatabaseRecord($thumbRelative);
            }
        }

        Storage::disk(self::DISK)->delete($normalized);
        self::deleteDatabaseRecord($normalized);
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

    private static function absolutePathOnDisk(string $normalized): ?string
    {
        $storageFile = storage_path('app/public/' . $normalized);

        if (is_file($storageFile)) {
            return $storageFile;
        }

        $legacyFile = public_path('uploads/' . $normalized);

        if (is_file($legacyFile)) {
            return $legacyFile;
        }

        if (is_file(public_path($normalized))) {
            return public_path($normalized);
        }

        return null;
    }

    private static function hasDatabaseRecord(string $normalized): bool
    {
        if (! Schema::hasTable('borrowing_image_files')) {
            return false;
        }

        return BorrowingImageFile::query()
            ->where('path', $normalized)
            ->exists();
    }

    private static function deleteDatabaseRecord(string $normalized): void
    {
        if (! Schema::hasTable('borrowing_image_files')) {
            return;
        }

        BorrowingImageFile::query()
            ->where('path', $normalized)
            ->delete();
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
