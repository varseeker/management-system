@extends('layouts.app')

@section('title', 'Edit Pemasok')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nama Pemasok</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Lokasi Pemasok</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $supplier->location) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="note" class="form-control">{{ old('note', $supplier->note) }}</textarea>
            </div>

            <hr>
            <h6 class="fw-bold mb-3">Barang yang Ditawarkan</h6>
            @php
                $offers = old('offers', $supplier->rawMaterials->map(fn ($m) => [
                    'raw_material_id' => $m->id,
                    'price' => $m->pivot->price,
                    'quality' => $m->pivot->quality,
                ])->all());
            @endphp
            <div class="row g-2 mb-2 fw-semibold small text-muted">
                <div class="col-md-5">Bahan Baku</div>
                <div class="col-md-3">Harga (Rp)</div>
                <div class="col-md-3">Kualitas</div>
            </div>
            @include('partials.supplier-offers', [
                'rawMaterials' => $rawMaterials,
                'offers' => $offers,
            ])

            <div class="mt-4">
                <button class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection
