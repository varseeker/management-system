@extends('layouts.app')

@section('title', 'Pemasok')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1 fw-bold">Management Pemasok</h5>
                <p class="text-muted mb-0">Data pemasok bahan baku, harga, dan kualitas</p>
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Pemasok
            </a>
        </div>

        <div class="table-responsive">
            <table id="dataTable" class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Lokasi</th>
                        <th>Telepon</th>
                        <th>Barang Ditawarkan</th>
                        <th width="200">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                    <tr>
                        <td class="fw-semibold">{{ $supplier->name }}</td>
                        <td>{{ $supplier->location }}</td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                        <td>
                            @forelse($supplier->rawMaterials as $material)
                            <span class="badge bg-secondary me-1 mb-1">
                                {{ $material->name }}
                            </span>
                            @empty
                            <span class="text-muted">-</span>
                            @endforelse
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-info btn-sm text-white">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus pemasok ini?')">
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
