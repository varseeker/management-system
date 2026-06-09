<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use Illuminate\Http\Request;

class BorrowingApprovalController extends Controller
{
    public function index(Request $request)
    {
        $pendingBorrowings = Borrowing::with(['user', 'item'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $activeBorrowings = Borrowing::with(['user', 'item'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        $recentHistory = Borrowing::with(['user', 'item'])
            ->whereIn('status', ['rejected', 'returned'])
            ->latest()
            ->limit(100)
            ->get();

        $pendingBorrowerOptions = $pendingBorrowings->pluck('user.name')->unique()->sort()->values();
        $pendingItemOptions = $pendingBorrowings->pluck('item.name')->unique()->sort()->values();

        $activeBorrowerOptions = $activeBorrowings->pluck('user.name')->unique()->sort()->values();
        $activeItemOptions = $activeBorrowings->pluck('item.name')->unique()->sort()->values();

        $historyBorrowerOptions = $recentHistory->pluck('user.name')->unique()->sort()->values();
        $historyItemOptions = $recentHistory->pluck('item.name')->unique()->sort()->values();

        return view('approvals.index', compact(
            'pendingBorrowings',
            'activeBorrowings',
            'recentHistory',
            'pendingBorrowerOptions',
            'pendingItemOptions',
            'activeBorrowerOptions',
            'activeItemOptions',
            'historyBorrowerOptions',
            'historyItemOptions',
        ));
    }
}
