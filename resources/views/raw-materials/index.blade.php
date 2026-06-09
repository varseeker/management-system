@extends('layouts.app')

@section('title', 'Bahan Baku')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1 fw-bold">Stok Bahan Baku</h5>
                <p class="text-muted mb-0">Telur, indomie, kapal api, nutrisari, dll.</p>
            </div>
            <a href="{{ route('raw-materials.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Bahan
            </a>
        </div>

        <div class="table-responsive">
            <table id="dataTable" class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Deskripsi</th>
                        <th width="180">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rawMaterials as $material)
                    <tr>
                        <td><span class="fw-semibold">{{ $material->code }}</span></td>
                        <td>{{ $material->name }}</td>
                        <td>
                            @if($material->stock > 10)
                            <span class="badge bg-success">{{ $material->stock }}</span>
                            @elseif($material->stock > 0)
                            <span class="badge bg-warning">{{ $material->stock }}</span>
                            @else
                            <span class="badge bg-danger">Habis</span>
                            @endif
                        </td>
                        <td>{{ $material->unit }}</td>
                        <td>{{ $material->description ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('raw-materials.edit', $material) }}" class="btn btn-warning btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('raw-materials.destroy', $material) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus bahan baku ini?')">
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
