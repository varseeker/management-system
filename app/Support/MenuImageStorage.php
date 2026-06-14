<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuImageStorage
{
    private const DISK = 'public';

    public static function store(UploadedFile $file, string $menuCode): string
    {
        $disk = Storage::disk(self::DISK);
        $directory = 'menus/' . self::sanitizeCode($menuCode);
        $disk->makeDirectory($directory);

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = Str::random(24) . '.' . $extension;
        $relativePath = $directory . '/' . $filename;

        $disk->putFileAs($directory, $file, $filename);

        return $relativePath;
    }

    public static function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk(self::DISK)->delete(self::normalizePath($path));
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

        $absolute = self::absolutePath($path);
        $version = $absolute ? (string) @filemtime($absolute) : null;
        $relative = '/menus/images/'.self::normalizePath($path);

        return $version ? $relative.'?v='.$version : $relative;
    }

    public static function exists(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        return Storage::disk(self::DISK)->exists(self::normalizePath($path));
    }

    public static function absolutePath(?string $path): ?string
    {
        if (! $path || ! self::exists($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->path(self::normalizePath($path));
    }

    public static function mimeType(?string $path): string
    {
        $absolute = self::absolutePath($path);

        if ($absolute === null) {
            return 'application/octet-stream';
        }

        return mime_content_type($absolute) ?: 'image/jpeg';
    }

    public static function normalizePath(string $path): string
    {
        return ltrim(str_replace('\\', '/', trim($path)), '/');
    }

    private static function sanitizeCode(string $code): string
    {
        return preg_replace('/[^A-Za-z0-9\-_]/', '-', $code) ?: 'menu';
    }
}
