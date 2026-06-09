@extends('layouts.app')

@section('title', 'Edit Menu')

@section('content')

<div class="card dashboard-card">
    <div class="card-body">
        <form action="{{ route('menus.update', $menu) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Kode Menu</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $menu->code) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Menu</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $menu->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control">{{ old('description', $menu->description) }}</textarea>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active"
                    @checked(old('is_active', $menu->is_active))>
                <label class="form-check-label" for="is_active">Menu aktif</label>
            </div>

            <hr>
            <h6 class="fw-bold mb-3">Resep Bahan Baku (per 1 porsi)</h6>
            @php
                $ingredients = old('ingredients', $menu->rawMaterials->map(fn ($m) => [
                    'raw_material_id' => $m->id,
                    'quantity' => $m->pivot->quantity,
                ])->all());
            @endphp
            @include('partials.menu-ingredients', [
                'rawMaterials' => $rawMaterials,
                'ingredients' => $ingredients,
            ])

            <div class="mt-4">
                <button class="btn btn-primary">Update Menu</button>
            </div>
        </form>
    </div>
</div>

@endsection
