@extends('layouts.app')

@section('title', 'Detail Pesanan POS')

@section('content')

<div class="card dashboard-card mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Pesanan #{{ str_pad($order->pos_order_id ?? $order->id, 6, '0', STR_PAD_LEFT) }}</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <p class="small text-muted mb-0">Pelanggan</p>
                <p class="fw-bold mb-0">{{ $order->customer ?: '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0">Kasir</p>
                <p class="fw-bold mb-0">{{ $order->cashier_name ?: '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0">Referensi</p>
                <p class="fw-bold mb-0">{{ $order->displayReference() }}</p>
            </div>
            <div class="col-md-3">
                <p class="small text-muted mb-0">Total</p>
                <p class="fw-bold mb-0">Rp{{ number_format($order->total, 0, ',', '.') }}</p>
            </div>
            <div class="col-md-3">
                <p class="small text-muted mb-0">Dibayar</p>
                <p class="fw-bold mb-0">Rp{{ number_format($order->amount_paid, 0, ',', '.') }}</p>
            </div>
            <div class="col-md-3">
                <p class="small text-muted mb-0">Kembalian</p>
                <p class="fw-bold mb-0">Rp{{ number_format($order->amount_change, 0, ',', '.') }}</p>
            </div>
            <div class="col-md-3">
                <p class="small text-muted mb-0">Status</p>
                <p class="fw-bold mb-0">{{ $order->order_status }} / {{ $order->payment_status }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card dashboard-card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Item Pesanan</h6>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Menu</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Variant</th>
                        <th>Size</th>
                        <th>Ice</th>
                        <th>Sugar</th>
                        <th>Subtotal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($order->items as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->menu_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp{{ number_format($item->menu_price, 0, ',', '.') }}</td>
                        <td>{{ $item->variant ?: '—' }}</td>
                        <td>{{ $item->size ?: '—' }}</td>
                        <td>{{ $item->ice && $item->ice !== '-' ? $item->ice : '—' }}</td>
                        <td>{{ $item->sugar && $item->sugar !== '-' ? $item->sugar : '—' }}</td>
                        <td class="fw-bold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        <td>{{ $item->status ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Tidak ada item.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <a href="{{ route('reports.orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

@endsection
