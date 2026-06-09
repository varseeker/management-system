<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use Illuminate\Http\Request;

class BorrowingApprovalActionController extends Controller
{
    public function approve(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->status !== 'pending') {
            return back()->withErrors([
                'status' => 'Status pengajuan tidak valid.',
            ]);
        }

        $request->validate([
            'approval_note' => 'required|string|max:1000',
        ], [
            'approval_note.required' => 'Catatan persetujuan wajib diisi.',
        ]);

        $item = $borrowing->item;

        if ($borrowing->quantity > $item->stock) {
            return back()->withErrors([
                'stock' => 'Stok tidak mencukupi.',
            ]);
        }

        $item->decrement('stock', $borrowing->quantity);

        $borrowing->update([
            'status' => 'approved',
            'approval_note' => $request->approval_note,
        ]);

        return redirect()
            ->route('approvals.index')
            ->with('success', 'Pengajuan peminjaman berhasil disetujui.');
    }

    public function reject(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->status !== 'pending') {
            return back()->withErrors([
                'status' => 'Status pengajuan tidak valid.',
            ]);
        }

        $request->validate([
            'approval_note' => 'required|string|max:1000',
        ], [
            'approval_note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $borrowing->update([
            'status' => 'rejected',
            'approval_note' => $request->approval_note,
        ]);

        return redirect()
            ->route('approvals.index')
            ->with('success', 'Pengajuan peminjaman berhasil ditolak.');
    }
}
