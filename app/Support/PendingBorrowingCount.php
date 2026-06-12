<?php

namespace App\Support;

use App\Models\Borrowing;
use Illuminate\Support\Facades\Cache;

class PendingBorrowingCount
{
    public const CACHE_KEY = 'borrowings.pending_count';

    public const CACHE_TTL_SECONDS = 30;

    public static function get(): int
    {
        return (int) Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn () => Borrowing::where('status', 'pending')->count(),
        );
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
