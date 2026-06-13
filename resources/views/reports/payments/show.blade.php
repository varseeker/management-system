@extends('layouts.app')

@section('title', 'Detail Pembayaran POS')

@section('content')

<div class="card dashboard-card mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3">Pembayaran #{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <p class="small text-muted mb-0">Order ID</p>
                <p class="fw-bold mb-0">{{ $payment->pos_order_id ?? $payment->external_order_id }}</p>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0">Metode</p>
                <p class="fw-bold mb-0">{{ $payment->payment_method ?: '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0">Status</p>
                <p class="fw-bold mb-0">{{ $payment->payment_status ?: '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0">Jumlah Dibayar</p>
                <p class="fw-bold mb-0">Rp{{ number_format($payment->amount_paid, 0, ',', '.') }}</p>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0">Pelanggan</p>
                <p class="fw-bold mb-0">{{ $payment->customer ?: '-' }}</p>
            </div>
            <div class="col-md-4">
                <p class="small text-muted mb-0">Referensi</p>
                <p class="fw-bold mb-0">{{ $payment->displayReference() }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card dashboard-card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Item Terkait</h6>
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Menu</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payment->items as $item)
                    <tr>
                        <td>{{ $item->menu_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">Tidak ada item.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex flex-wrap gap-2">
            <a href="{{ route('reports.payments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('reports.orders.show', $payment) }}" class="btn btn-outline-primary">
                Lihat Detail Pesanan
            </a>
        </div>
    </div>
</div>

@endsection
