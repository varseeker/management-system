@extends('layouts.app')

@section('title', 'Detail Pemasok')

@section('content')

<div class="card dashboard-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="fw-bold mb-1">{{ $supplier->name }}</h5>
                <p class="text-muted mb-0">
                    <i class="bi bi-geo-alt"></i> {{ $supplier->location }}
                </p>
                @if($supplier->phone)
                <p class="text-muted mb-0">
                    <i class="bi bi-telephone"></i> {{ $supplier->phone }}
                </p>
                @endif
                @if($supplier->note)
                <p class="mt-2 mb-0">{{ $supplier->note }}</p>
                @endif
            </div>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
        </div>
    </div>
</div>

<div class="card dashboard-card">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Daftar Barang & Penawaran</h6>
        <div class="table-responsive">
            <table id="supplierOffersTable" class="table align-middle js-paginated-table">
                <thead class="table-light">
                    <tr>
                        <th>Bahan Baku</th>
                        <th>Harga</th>
                        <th>Kualitas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($supplier->rawMaterials as $material)
                    <tr>
                        <td>{{ $material->name }} <span class="text-muted">({{ $material->unit }})</span></td>
                        <td>Rp {{ number_format($material->pivot->price, 0, ',', '.') }}</td>
                        <td>
                            @php $q = $material->pivot->quality; @endphp
                            <span class="badge bg-{{ $q === 'excellent' ? 'success' : ($q === 'good' ? 'primary' : ($q === 'fair' ? 'warning' : 'danger')) }}">
                                {{ \App\Models\Supplier::qualityLabel($q) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Belum ada barang</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-light mt-2">Kembali</a>
    </div>
</div>

@endsection
