@extends('layouts.app')

@section('title', 'Tambah Pemasok')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama Pemasok</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Lokasi Pemasok</label>
                <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Kota / alamat" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="note" class="form-control">{{ old('note') }}</textarea>
            </div>

            <hr>
            <h6 class="fw-bold mb-3">Barang yang Ditawarkan</h6>
            @if($rawMaterials->isEmpty())
            <div class="alert alert-warning">
                Tambah <a href="{{ route('raw-materials.create') }}">bahan baku</a> terlebih dahulu.
            </div>
            @else
            <div class="row g-2 mb-2 fw-semibold small text-muted">
                <div class="col-md-5">Bahan Baku</div>
                <div class="col-md-3">Harga (Rp)</div>
                <div class="col-md-3">Kualitas</div>
            </div>
            @include('partials.supplier-offers', ['rawMaterials' => $rawMaterials])
            @endif

            <div class="mt-4">
                <button class="btn btn-primary" @disabled($rawMaterials->isEmpty())>Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection
