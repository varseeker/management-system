<?php

namespace App\Observers;

use App\Models\Borrowing;
use App\Support\PendingBorrowingCount;

class BorrowingObserver
{
    public function saved(Borrowing $borrowing): void
    {
        if ($borrowing->wasRecentlyCreated || $borrowing->wasChanged('status')) {
            PendingBorrowingCount::forget();
        }
    }

    public function deleted(Borrowing $borrowing): void
    {
        if ($borrowing->status === 'pending') {
            PendingBorrowingCount::forget();
        }
    }
}
