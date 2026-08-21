<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Laravel + PostgreSQL tidak mengikat bytea sebagai LOB secara default,
 * sehingga binary PNG/JPEG gagal dengan "invalid byte sequence for encoding UTF8".
 */
class PostgresBinary
{
    public static function upsert(string $table, string $path, string $mimeType, string $contents): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $now = now();

        if ($driver !== 'pgsql') {
            DB::table($table)->updateOrInsert(
                ['path' => $path],
                [
                    'mime_type' => $mimeType,
                    'contents' => $contents,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            return;
        }

        $exists = DB::table($table)->where('path', $path)->exists();

        if ($exists) {
            DB::update(
                "update {$table} set mime_type = ?, contents = decode(?, 'base64'), updated_at = ? where path = ?",
                [$mimeType, base64_encode($contents), $now, $path],
            );

            return;
        }

        DB::insert(
            "insert into {$table} (path, mime_type, contents, created_at, updated_at) values (?, ?, decode(?, 'base64'), ?, ?)",
            [$path, $mimeType, base64_encode($contents), $now, $now],
        );
    }

    public static function decode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value) ?: null;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }
}
