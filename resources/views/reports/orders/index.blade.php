@extends('layouts.app')

@section('title', 'Laporan Pesanan POS')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="mb-1 fw-bold">Laporan Pesanan</h5>
                <p class="text-muted mb-0">Data pesanan dari kasir POS yang tersinkron ke sistem manajemen</p>
                <p class="text-muted small mb-0 mt-1">Pesanan otomatis masuk setelah pembayaran berhasil di POS.</p>
            </div>
            <a href="{{ route('reports.orders.export') }}" class="btn btn-outline-secondary">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Total</th>
                        <th>Dibayar</th>
                        <th>Kembalian</th>
                        <th>Pelanggan</th>
                        <th>Status</th>
                        <th>Bayar</th>
                        <th>Metode</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th>Referensi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->pos_order_id ?? $order->external_order_id }}</td>
                        <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($order->amount_paid, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($order->amount_change, 0, ',', '.') }}</td>
                        <td>{{ $order->customer ?: '-' }}</td>
                        <td>{{ $order->order_status ?: '-' }}</td>
                        <td>{{ $order->payment_status ?: '-' }}</td>
                        <td>{{ $order->payment_method ?: '-' }}</td>
                        <td>{{ $order->ordered_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?: '-' }}</td>
                        <td>{{ $order->cashier_name ?: '-' }}</td>
                        <td><code class="small">{{ $order->displayReference() }}</code></td>
                        <td>
                            <a href="{{ route('reports.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-4">Belum ada pesanan POS tersinkron.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
