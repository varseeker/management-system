@extends('layouts.app')

@section('title', 'Laporan Pembayaran POS')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="mb-1 fw-bold">Laporan Pembayaran</h5>
                <p class="text-muted mb-0">Rekapitulasi pembayaran dari transaksi kasir POS</p>
            </div>
            <a href="{{ route('reports.payments.export') }}" class="btn btn-outline-secondary">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Order ID</th>
                        <th>Jumlah Dibayar</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Referensi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>{{ $payment->pos_order_id ?? $payment->external_order_id }}</td>
                        <td>Rp{{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                        <td>{{ $payment->payment_method ?: '-' }}</td>
                        <td>{{ $payment->payment_status ?: '-' }}</td>
                        <td><code class="small">{{ $payment->displayReference() }}</code></td>
                        <td>
                            <a href="{{ route('reports.payments.show', $payment) }}" class="btn btn-outline-primary btn-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada pembayaran POS tersinkron.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
