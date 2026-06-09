<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Item;
use App\Support\BorrowingImageStorage;
use App\Support\RoleAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with(['user', 'item'])->latest();

        if (! RoleAccess::canViewAllBorrowings(Auth::user()->role)) {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->get();

        $itemFilterOptions = $borrowings->pluck('item.name')->unique()->sort()->values();
        $borrowerFilterOptions = RoleAccess::canViewAllBorrowings(Auth::user()->role)
            ? $borrowings->pluck('user.name')->unique()->sort()->values()
            : collect();

        $statsQuery = Borrowing::query();
        if (! RoleAccess::canViewAllBorrowings(Auth::user()->role)) {
            $statsQuery->where('user_id', Auth::id());
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'returned' => (clone $statsQuery)->where('status', 'returned')->count(),
        ];

        return view('borrowings.index', compact(
            'borrowings',
            'stats',
            'itemFilterOptions',
            'borrowerFilterOptions',
        ));
    }

    public function create()
    {
        $items = Item::where('stock', '>', 0)->get();
        $maxQuantity = config('inventory.borrowing.max_quantity');
        $maxLoanDays = config('inventory.borrowing.max_loan_days');

        return view('borrowings.create', compact('items', 'maxQuantity', 'maxLoanDays'));
    }

    public function store(Request $request)
    {
        $maxQuantity = config('inventory.borrowing.max_quantity');
        $maxLoanDays = config('inventory.borrowing.max_loan_days');

        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => "required|integer|min:1|max:{$maxQuantity}",
            'borrow_date' => 'required|date|after_or_equal:today',
            'expected_return_date' => 'required|date|after:borrow_date',
            'description' => 'required|string|max:1000',
            'borrow_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'quantity.max' => "Jumlah peminjaman maksimal {$maxQuantity} unit per pengajuan.",
            'expected_return_date.after' => 'Tanggal rencana pengembalian harus setelah tanggal pinjam.',
            'description.required' => 'Deskripsi alasan pengajuan wajib diisi.',
            'borrow_image.required' => 'Foto kondisi barang saat pengajuan wajib diunggah.',
            'borrow_image.image' => 'Berkas harus berupa gambar.',
            'borrow_image.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $borrowDate = Carbon::parse($request->borrow_date);
        $expectedReturnDate = Carbon::parse($request->expected_return_date);
        $maxReturnDate = $borrowDate->copy()->addDays($maxLoanDays);

        if ($expectedReturnDate->gt($maxReturnDate)) {
            return back()->withErrors([
                'expected_return_date' => "Jangka waktu peminjaman maksimal {$maxLoanDays} hari.",
            ])->withInput();
        }

        $item = Item::findOrFail($request->item_id);

        if ($request->quantity > $item->stock) {
            return back()->withErrors([
                'quantity' => 'Stok tidak mencukupi.',
            ])->withInput();
        }

        Borrowing::create([
            'user_id' => Auth::id(),
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'borrow_date' => $request->borrow_date,
            'expected_return_date' => $request->expected_return_date,
            'description' => $request->description,
            'borrow_image' => BorrowingImageStorage::store($request->file('borrow_image'), 'pengajuan'),
            'status' => 'pending',
        ]);

        return redirect()
            ->route('borrowings.index')
            ->with('success', 'Pengajuan peminjaman berhasil dikirim dan menunggu persetujuan.');
    }

    public function returnForm(Borrowing $borrowing)
    {
        return view('borrowings.return', compact('borrowing'));
    }

    public function returnItem(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->status !== 'approved') {
            return back()->withErrors([
                'status' => 'Barang belum dalam status dipinjam.',
            ]);
        }

        $request->validate([
            'return_condition' => 'required|in:good,minor_damage,broken',
            'return_note' => 'nullable|string|max:1000',
            'return_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'return_image.required' => 'Foto kondisi barang saat pengembalian wajib diunggah.',
            'return_image.image' => 'Berkas harus berupa gambar.',
            'return_image.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        $borrowing->item->increment('stock', $borrowing->quantity);

        $borrowing->update([
            'status' => 'returned',
            'return_date' => now(),
            'return_condition' => $request->return_condition,
            'return_note' => $request->return_note,
            'return_image' => BorrowingImageStorage::store($request->file('return_image'), 'pengembalian'),
        ]);

        return redirect()
            ->route('approvals.index')
            ->with('success', 'Barang berhasil dikembalikan.');
    }
}
