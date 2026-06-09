@extends('layouts.app')

@section('title', 'Dasbor')

@section('content')

<div class="row g-3 mb-4">
    @if(in_array(auth()->user()->role, ['admin', 'owner']))
    <div class="col-md-6 col-xl-4 col-xxl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Barang Inventori</p>
                <h3 class="fw-bold mb-0">{{ $totalItems }}</h3>
                <span class="small text-muted">Stok: {{ $totalStock }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4 col-xxl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Bahan Baku</p>
                <h3 class="fw-bold mb-0">{{ $totalRawMaterials }}</h3>
                <span class="small text-muted">Stok: {{ $totalRawStock }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4 col-xxl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Menu Aktif</p>
                <h3 class="fw-bold mb-0">{{ $totalMenus }}</h3>
                <span class="small text-muted">Terjual hari ini: {{ $todayMenuSales }} porsi</span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4 col-xxl-2">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Pemasok</p>
                <h3 class="fw-bold mb-0">{{ $totalSuppliers }}</h3>
                <a href="{{ route('suppliers.index') }}" class="small">Lihat semua</a>
            </div>
        </div>
    </div>
    @endif
    <div class="col-md-6 col-xl-4 col-xxl-2">
        <div class="card dashboard-card h-100 border-warning">
            <div class="card-body">
                <p class="text-muted mb-1 small">Peminjaman Menunggu</p>
                <h3 class="fw-bold mb-0 text-warning">{{ $pendingBorrowings }}</h3>
                @if(in_array(auth()->user()->role, ['admin', 'owner']))
                <a href="{{ route('approvals.index') }}" class="small">Kelola</a>
                @else
                <a href="{{ route('borrowings.index', ['status' => 'pending']) }}" class="small">Lihat</a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4 col-xxl-2">
        <div class="card dashboard-card h-100 bg-dark text-white">
            <div class="card-body">
                <p class="mb-2 small opacity-75">Tindakan Cepat</p>
                <div class="d-flex flex-wrap gap-1">
                    @if(in_array(auth()->user()->role, ['admin', 'owner']))
                    <a href="{{ route('raw-materials.create') }}" class="btn btn-sm btn-light">+ Bahan</a>
                    @endif
                    <a href="{{ route('menus.sell.index') }}" class="btn btn-sm btn-success">Pesanan</a>
                    <a href="{{ route('borrowings.create') }}" class="btn btn-sm btn-light">Pinjam</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    @if(in_array(auth()->user()->role, ['admin', 'owner']))
    <div class="col-lg-4">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-exclamation-triangle text-warning"></i> Stok Bahan Baku Rendah
                </h6>
                @forelse($lowStockMaterials as $material)
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span>{{ $material->name }}</span>
                    <span class="badge bg-{{ $material->stock == 0 ? 'danger' : 'warning' }}">
                        {{ $material->stock }} {{ $material->unit }}
                    </span>
                </div>
                @empty
                <p class="text-muted small mb-0">Semua stok bahan baku aman.</p>
                @endforelse
                <a href="{{ route('raw-materials.index') }}" class="btn btn-sm btn-outline-primary mt-3">Kelola Bahan Baku</a>
            </div>
        </div>
    </div>
    @endif

    <div class="col-lg-4">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-cart-check text-success"></i> Pesanan Menu Terbaru
                </h6>
                @forelse($recentMenuSales as $sale)
                <div class="py-2 border-bottom">
                    <div class="fw-semibold">{{ $sale->menu->name }}</div>
                    <div class="small text-muted">
                        {{ $sale->quantity }} porsi · {{ $sale->user->name }} · {{ $sale->created_at->diffForHumans() }}
                    </div>
                </div>
                @empty
                <p class="text-muted small mb-0">Belum ada pesanan menu.</p>
                @endforelse
                <a href="{{ route('menus.sell.index') }}" class="btn btn-sm btn-outline-success mt-3">Proses Pesanan</a>
            </div>
        </div>
    </div>

    @if(in_array(auth()->user()->role, ['admin', 'owner']))
    <div class="col-lg-4">
        <div class="card dashboard-card h-100">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-truck text-primary"></i> Pemasok Terdaftar
                </h6>
                @forelse($suppliers as $supplier)
                <div class="py-2 border-bottom">
                    <div class="fw-semibold">{{ $supplier->name }}</div>
                    <div class="small text-muted mb-1">
                        <i class="bi bi-geo-alt"></i> {{ $supplier->location }}
                    </div>
                    <div>
                        @foreach($supplier->rawMaterials->take(3) as $material)
                        <span class="badge bg-secondary me-1">
                            {{ $material->name }} Rp{{ number_format($material->pivot->price, 0, ',', '.') }}
                        </span>
                        @endforeach
                        @if($supplier->rawMaterials->count() > 3)
                        <span class="small text-muted">+{{ $supplier->rawMaterials->count() - 3 }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted small mb-0">Belum ada pemasok.</p>
                @endforelse
                <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-outline-primary mt-3">Tambah Pemasok</a>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
