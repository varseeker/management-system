@extends('layouts.app')

@section('title', 'Menu & Resep')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1 fw-bold">Daftar Menu</h5>
                <p class="text-muted mb-0">Setiap menu mengkonsumsi bahan baku sesuai resep</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('menus.sell.index') }}" class="btn btn-success">
                    <i class="bi bi-cart-check"></i> Proses Pesanan
                </a>
                <a href="{{ route('menus.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Menu
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table id="dataTable" class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Menu</th>
                        <th>Resep (per porsi)</th>
                        <th>Status</th>
                        <th width="180">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($menus as $menu)
                    <tr>
                        <td><span class="fw-semibold">{{ $menu->code }}</span></td>
                        <td>{{ $menu->name }}</td>
                        <td>
                            @forelse($menu->rawMaterials as $material)
                            <span class="badge bg-secondary me-1">
                                {{ $material->pivot->quantity }}× {{ $material->name }}
                            </span>
                            @empty
                            <span class="text-muted">Belum ada resep</span>
                            @endforelse
                        </td>
                        <td>
                            @if($menu->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('menus.edit', $menu) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('menus.destroy', $menu) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus menu ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
