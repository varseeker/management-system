<?php

namespace App\Support;

use App\Models\MenuImageFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuImageStorage
{
    private const DISK = 'public';

    public static function store(UploadedFile $file, string $menuCode): string
    {
        $disk = Storage::disk(self::DISK);
        $directory = 'menus/'.self::sanitizeCode($menuCode);
        $disk->makeDirectory($directory);

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::random(24).'.'.$extension;
        $relativePath = $directory.'/'.$filename;

        $disk->putFileAs($directory, $file, $filename);
        self::persistPath($relativePath);

        return $relativePath;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        $normalized = self::normalizePath($path);

        Storage::disk(self::DISK)->delete($normalized);
        self::deleteDatabaseRecord($normalized);
    }

    public static function publicUrl(?string $path): ?string
    {
        $apiPath = self::apiPath($path);

        if (! $apiPath) {
            return null;
        }

        return url($apiPath);
    }

    /**
     * Path relatif untuk API POS — di-resolve dengan INVENTORY_SERVICE_URL di sisi POS.
     */
    public static function apiPath(?string $path): ?string
    {
        if (! $path || ! self::exists($path)) {
            return null;
        }

        $normalized = self::normalizePath($path);
        $absolute = self::absolutePathOnDisk($normalized);
        $version = $absolute ? (string) @filemtime($absolute) : null;

        if (! $version && self::hasDatabaseRecord($normalized)) {
            $version = MenuImageFile::query()
                ->where('path', $normalized)
                ->value('updated_at');

            $version = $version ? (string) strtotime((string) $version) : null;
        }

        $relative = '/menus/images/'.$normalized;

        return $version ? $relative.'?v='.$version : $relative;
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

        return MenuImageFile::query()
            ->where('path', $normalized)
            ->value('contents');
    }

    public static function absolutePath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return self::absolutePathOnDisk(self::normalizePath($path));
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

        return MenuImageFile::query()
            ->where('path', $normalized)
            ->value('mime_type') ?? 'image/jpeg';
    }

    public static function persistPath(string $path): bool
    {
        if (! Schema::hasTable('menu_image_files')) {
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

        MenuImageFile::updateOrCreate(
            ['path' => $normalized],
            [
                'mime_type' => mime_content_type($diskPath) ?: 'image/jpeg',
                'contents' => $contents,
            ],
        );

        return true;
    }

    public static function normalizePath(string $path): string
    {
        return ltrim(str_replace('\\', '/', trim($path)), '/');
    }

    private static function absolutePathOnDisk(string $normalized): ?string
    {
        $storageFile = storage_path('app/public/'.$normalized);

        if (is_file($storageFile)) {
            return $storageFile;
        }

        return null;
    }

    private static function hasDatabaseRecord(string $normalized): bool
    {
        if (! Schema::hasTable('menu_image_files')) {
            return false;
        }

        return MenuImageFile::query()
            ->where('path', $normalized)
            ->exists();
    }

    private static function deleteDatabaseRecord(string $normalized): void
    {
        if (! Schema::hasTable('menu_image_files')) {
            return;
        }

        MenuImageFile::query()
            ->where('path', $normalized)
            ->delete();
    }

    private static function sanitizeCode(string $code): string
    {
        return preg_replace('/[^A-Za-z0-9\-_]/', '-', $code) ?: 'menu';
    }
}
