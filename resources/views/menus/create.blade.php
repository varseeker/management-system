@extends('layouts.app')

@section('title', 'Tambah Menu')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <form action="{{ route('menus.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Kode Menu</label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Menu</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Indomie Telur" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            </div>

            @include('partials.menu-pos-fields')

            <hr class="my-4">
            <h6 class="fw-bold mb-3">Resep Bahan Baku (per 1 porsi)</h6>
            @if($rawMaterials->isEmpty())
            @push('flash-toasts')
            <script type="application/json" class="js-extra-flash">
            [{"type":"warning","title":"Perhatian","message":"Tambah bahan baku terlebih dahulu sebelum membuat menu."}]
            </script>
            @endpush
            <x-callout type="warning" title="Bahan baku belum tersedia" class="mb-3">
                Tambah <a href="{{ route('raw-materials.create') }}">bahan baku</a> terlebih dahulu untuk menyusun resep menu.
            </x-callout>
            @else
            @include('partials.menu-ingredients', ['rawMaterials' => $rawMaterials])
            @endif

            <div class="mt-4">
                <button class="btn btn-primary" @disabled($rawMaterials->isEmpty())>Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

@endsection
