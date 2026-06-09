@extends('layouts.app')

@section('title', 'Edit Bahan Baku')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <form action="{{ route('raw-materials.update', $rawMaterial) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Kode</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $rawMaterial->code) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Bahan</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $rawMaterial->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Stok</label>
                <input type="number" name="stock" class="form-control" value="{{ old('stock', $rawMaterial->stock) }}" min="0" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Satuan</label>
                <input type="text" name="unit" class="form-control" value="{{ old('unit', $rawMaterial->unit) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control">{{ old('description', $rawMaterial->description) }}</textarea>
            </div>
            <button class="btn btn-primary">Update</button>
        </form>
    </div>
</div>

@endsection
