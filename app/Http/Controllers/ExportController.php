<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\MenuSale;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function borrowings(Request $request): StreamedResponse
    {
        abort_unless(RoleAccess::canExport(Auth::user()->role), 403);

        $query = Borrowing::with(['user', 'item'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->get();

        $filename = 'peminjaman_' . now()->format('Y-m-d_His') . '.csv';

        return $this->csvResponse($filename, [
            'Peminjam',
            'Barang',
            'Jumlah',
            'Tanggal Pinjam',
            'Tanggal Kembali Rencana',
            'Tanggal Kembali Aktual',
            'Deskripsi Pengajuan',
            'Catatan Persetujuan',
            'Status',
            'Kondisi Pengembalian',
            'Catatan Pengembalian',
        ], $borrowings->map(function (Borrowing $borrowing) {
            return [
                $borrowing->user->name,
                $borrowing->item->name,
                $borrowing->quantity,
                $borrowing->borrow_date,
                $borrowing->expected_return_date ?? '-',
                $borrowing->return_date ?? '-',
                $borrowing->description ?? $borrowing->note ?? '-',
                $borrowing->approval_note ?? '-',
                $this->statusLabel($borrowing->status),
                $this->returnConditionLabel($borrowing->return_condition),
                $borrowing->return_note ?? '-',
            ];
        }));
    }

    public function menuSales(): StreamedResponse
    {
        abort_unless(RoleAccess::canExport(Auth::user()->role), 403);

        $sales = MenuSale::with(['menu', 'user'])->latest()->get();

        $filename = 'pesanan_menu_' . now()->format('Y-m-d_His') . '.csv';

        return $this->csvResponse($filename, [
            'Waktu',
            'Menu',
            'Porsi',
            'Pengguna',
            'Catatan',
        ], $sales->map(function (MenuSale $sale) {
            return [
                $sale->created_at->format('d/m/Y H:i'),
                $sale->menu->name,
                $sale->quantity,
                $sale->user->name,
                $sale->note ?? '-',
            ];
        }));
    }

    private function csvResponse(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Sedang Dipinjam',
            'rejected' => 'Ditolak',
            'returned' => 'Dikembalikan',
            default => $status,
        };
    }

    private function returnConditionLabel(?string $condition): string
    {
        return match ($condition) {
            'good' => 'Baik',
            'minor_damage' => 'Rusak Ringan',
            'broken' => 'Rusak Berat',
            default => '-',
        };
    }
}
