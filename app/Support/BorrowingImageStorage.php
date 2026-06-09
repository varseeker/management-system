<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BorrowingImageStorage
{
    public static function store(UploadedFile $file, string $prefix): string
    {
        $directory = public_path("uploads/borrowings/{$prefix}");

        if (! is_dir($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = $file->hashName();
        $file->move($directory, $filename);

        return "uploads/borrowings/{$prefix}/{$filename}";
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

            return;
        }

        Storage::disk('public')->delete($path);
    }

    private static function publicUrl(string $path): string
    {
        return '/' . ltrim(str_replace('\\', '/', $path), '/');
    }
}
