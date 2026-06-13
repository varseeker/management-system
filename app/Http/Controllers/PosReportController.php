<?php

namespace App\Http\Controllers;

use App\Models\PosOrder;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PosReportController extends Controller
{
    public function indexOrders()
    {
        $orders = PosOrder::query()
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->get();

        return view('reports.orders.index', compact('orders'));
    }

    public function showOrder(PosOrder $order)
    {
        $order->load('items');

        return view('reports.orders.show', compact('order'));
    }

    public function exportOrders(): StreamedResponse
    {
        abort_unless(RoleAccess::canExport(Auth::user()->role), 403);

        $orders = PosOrder::query()
            ->orderByDesc('ordered_at')
            ->get();

        return $this->csvResponse('laporan_pesanan_' . now()->format('Y-m-d_His') . '.csv', [
            'ID POS',
            'Pelanggan',
            'Total',
            'Dibayar',
            'Kembalian',
            'Status Order',
            'Status Bayar',
            'Metode',
            'Referensi',
            'Kasir',
            'Waktu',
        ], $orders->map(fn (PosOrder $order) => [
            $order->pos_order_id ?? $order->external_order_id,
            $order->customer ?? '-',
            $order->total,
            $order->amount_paid,
            $order->amount_change,
            $order->order_status ?? '-',
            $order->payment_status ?? '-',
            $order->payment_method ?? '-',
            $order->displayReference(),
            $order->cashier_name ?? '-',
            $order->ordered_at?->format('d/m/Y H:i') ?? '-',
        ]));
    }

    public function indexPayments()
    {
        $payments = PosOrder::query()
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->get();

        return view('reports.payments.index', compact('payments'));
    }

    public function showPayment(PosOrder $payment)
    {
        $payment->load('items');

        return view('reports.payments.show', compact('payment'));
    }

    public function exportPayments(): StreamedResponse
    {
        abort_unless(RoleAccess::canExport(Auth::user()->role), 403);

        $payments = PosOrder::query()
            ->orderByDesc('ordered_at')
            ->get();

        return $this->csvResponse('laporan_pembayaran_' . now()->format('Y-m-d_His') . '.csv', [
            'ID',
            'Order ID',
            'Jumlah Dibayar',
            'Metode',
            'Status',
            'Referensi',
            'Pelanggan',
            'Waktu',
        ], $payments->map(fn (PosOrder $payment) => [
            $payment->id,
            $payment->pos_order_id ?? $payment->external_order_id,
            $payment->amount_paid,
            $payment->payment_method ?? '-',
            $payment->payment_status ?? '-',
            $payment->displayReference(),
            $payment->customer ?? '-',
            $payment->ordered_at?->format('d/m/Y H:i') ?? '-',
        ]));
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
}
