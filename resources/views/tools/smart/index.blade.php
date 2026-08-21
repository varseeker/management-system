@extends('layouts.app')

@section('title', 'Tools — SPK Menu Favorit (SMART)')

@section('content')

<div class="card dashboard-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h5 class="mb-1 fw-bold">Sistem Penunjang Keputusan — Menu Terfavorit</h5>
                <p class="text-muted mb-0">
                    Peringkat menu selama <strong>1 bulan</strong> memakai metode
                    <strong>SMART</strong> (Simple Multi-Attribute Rating Technique).
                </p>
            </div>
            <span class="badge text-bg-secondary align-self-center">Tools</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('tools.smart.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="month" class="form-label">Periode (bulan)</label>
                <input type="month" id="month" name="month" class="form-control" value="{{ $month }}" required>
            </div>

            @foreach($criteria as $key => $meta)
                <div class="col-md-2">
                    <label for="w_{{ $key }}" class="form-label">{{ $meta['label'] }}</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="w_{{ $key }}"
                        name="w_{{ $key }}"
                        class="form-control"
                        value="{{ old('w_'.$key, number_format($weights[$key], 2, '.', '')) }}"
                    >
                </div>
            @endforeach

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-calculator"></i> Hitung SMART
                </button>
            </div>
        </form>

        <p class="text-muted small mt-3 mb-0">
            Bobot akan dinormalisasi otomatis agar total = 1.
            Sumber data: pesanan POS tersinkron + penjualan langsung (tanpa double-count).
            Periode: {{ $result['period_label'] }}.
        </p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Rekomendasi menu terfavorit</h6>
                @if($result['favorite'])
                    <div class="mb-2">
                        <span class="badge text-bg-success">Peringkat #1</span>
                    </div>
                    <h4 class="fw-bold mb-1">{{ $result['favorite']['name'] }}</h4>
                    <p class="text-muted mb-2">
                        Kode: <code>{{ $result['favorite']['code'] }}</code>
                        @if($result['favorite']['category'])
                            · {{ $result['favorite']['category'] }}
                        @elseif($result['favorite']['is_bundle'])
                            · Bundle
                        @endif
                    </p>
                    <p class="mb-1">Skor SMART: <strong>{{ number_format($result['favorite']['score'], 4) }}</strong></p>
                    <ul class="small text-muted mb-3">
                        <li>Terjual: {{ number_format($result['favorite']['quantity'], 0, ',', '.') }} porsi</li>
                        <li>Frekuensi: {{ number_format($result['favorite']['frequency'], 0, ',', '.') }} pesanan</li>
                        <li>Pendapatan: Rp{{ number_format($result['favorite']['revenue'], 0, ',', '.') }}</li>
                    </ul>

                    @if($result['favorite']['menu_id'])
                        <form method="POST" action="{{ route('tools.smart.apply') }}">
                            @csrf
                            <input type="hidden" name="menu_code" value="{{ $result['favorite']['code'] }}">
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="w_quantity" value="{{ $weights['quantity'] }}">
                            <input type="hidden" name="w_frequency" value="{{ $weights['frequency'] }}">
                            <input type="hidden" name="w_revenue" value="{{ $weights['revenue'] }}">
                            <input type="hidden" name="w_avg_qty" value="{{ $weights['avg_qty'] }}">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-star-fill"></i> Jadikan flag “Paling Laris”
                            </button>
                        </form>
                    @endif
                @else
                    <p class="text-muted mb-0">Belum ada data penjualan pada periode ini.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Kriteria &amp; bobot (setelah normalisasi)</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kriteria</th>
                                <th>Tipe</th>
                                <th>Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($criteria as $key => $meta)
                                <tr>
                                    <td>{{ $meta['label'] }}</td>
                                    <td><span class="badge text-bg-light border">{{ $meta['type'] }}</span></td>
                                    <td>{{ number_format($weights[$key], 4) }} ({{ number_format($weights[$key] * 100, 1) }}%)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="small text-muted mt-3 mb-0">
                    Utility benefit: <code>(x − min) / (max − min)</code>.
                    Skor akhir: <code>Σ (bobot × utility)</code>. Peringkat tertinggi = menu terfavorit.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card dashboard-card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Hasil peringkat SMART — {{ $monthLabel }}</h6>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Menu</th>
                        <th>Qty</th>
                        <th>Frekuensi</th>
                        <th>Pendapatan</th>
                        <th>Rata-rata/pesanan</th>
                        <th>U<sub>qty</sub></th>
                        <th>U<sub>freq</sub></th>
                        <th>U<sub>rev</sub></th>
                        <th>U<sub>avg</sub></th>
                        <th>Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($result['rankings'] as $row)
                        <tr class="{{ $row['rank'] === 1 ? 'table-success' : '' }}">
                            <td><strong>{{ $row['rank'] }}</strong></td>
                            <td>
                                <div class="fw-semibold">{{ $row['name'] }}</div>
                                <code class="small">{{ $row['code'] }}</code>
                            </td>
                            <td>{{ number_format($row['quantity'], 0, ',', '.') }}</td>
                            <td>{{ number_format($row['frequency'], 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                            <td>{{ number_format($row['avg_qty'], 2, ',', '.') }}</td>
                            <td>{{ number_format($row['utilities']['quantity'], 3) }}</td>
                            <td>{{ number_format($row['utilities']['frequency'], 3) }}</td>
                            <td>{{ number_format($row['utilities']['revenue'], 3) }}</td>
                            <td>{{ number_format($row['utilities']['avg_qty'], 3) }}</td>
                            <td><strong>{{ number_format($row['score'], 4) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Tidak ada penjualan menu pada periode {{ $monthLabel }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
