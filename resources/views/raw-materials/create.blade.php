@extends('layouts.app')

@section('title', 'Tambah Bahan Baku')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <form action="{{ route('raw-materials.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Kode</label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Bahan</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Stok Awal</label>
                <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Satuan</label>
                <input type="text" name="unit" class="form-control" value="{{ old('unit', 'pcs') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            </div>
            <button class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>

@endsection
