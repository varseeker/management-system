@extends('layouts.app')

@section('title', 'Edit Barang')

@section('content')

<div class="card dashboard-card">

    <div class="card-body">

        <form action="{{ route('items.update', $item->id) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="mb-3">

                <label class="form-label">
                    Kode Barang
                </label>

                <input type="text"
                    name="code"
                    class="form-control"
                    value="{{ $item->code }}">

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Nama Barang
                </label>

                <input type="text"
                    name="name"
                    class="form-control"
                    value="{{ $item->name }}">

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Stok
                </label>

                <input type="number"
                    name="stock"
                    class="form-control"
                    value="{{ $item->stock }}">

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Deskripsi
                </label>

                <textarea name="description"
                    class="form-control">{{ $item->description }}</textarea>

            </div>

            <button class="btn btn-primary">

                <i class="bi bi-check-lg"></i>
                Update Barang

            </button>

        </form>

    </div>

</div>

@endsection