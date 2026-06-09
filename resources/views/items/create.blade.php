@extends('layouts.app')

@section('title', 'Tambah Barang')

@section('content')

<div class="card dashboard-card">

    <div class="card-body">

        <form action="{{ route('items.store') }}"
              method="POST">

            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Kode Barang
                </label>

                <input type="text"
                       name="code"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Nama Barang
                </label>

                <input type="text"
                       name="name"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Stok
                </label>

                <input type="number"
                       name="stock"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Deskripsi
                </label>

                <textarea name="description"
                          class="form-control"></textarea>
            </div>

            <button class="btn btn-primary">
                Simpan Barang
            </button>

        </form>

    </div>

</div>

@endsection