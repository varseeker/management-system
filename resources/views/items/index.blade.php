@extends('layouts.app')

@push('styles')
    @vite('resources/css/datatables.css')
@endpush

@section('title', 'Daftar Barang')

@section('content')

<div class="card dashboard-card">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <div>
                <h5 class="mb-1 fw-bold">
                    Data Barang
                </h5>

                <p class="text-muted mb-0">
                    Kelola seluruh data inventory barang
                </p>
            </div>

            <a href="{{ route('items.create') }}"
                class="btn btn-primary">

                <i class="bi bi-plus-lg"></i>
                Tambah Barang

            </a>

        </div>

        <div class="table-responsive">

            <table id="dataTable"
                class="table align-middle">

                <thead class="table-light">

                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Stok</th>
                        <th>Deskripsi</th>
                        <th width="180">Tindakan</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($items as $item)

                    <tr>

                        <td>
                            <span class="fw-semibold">
                                {{ $item->code }}
                            </span>
                        </td>

                        <td>
                            {{ $item->name }}
                        </td>

                        <td>

                            @if($item->stock > 10)

                            <span class="badge bg-success">
                                {{ $item->stock }}
                            </span>

                            @elseif($item->stock > 0)

                            <span class="badge bg-warning">
                                {{ $item->stock }}
                            </span>

                            @else

                            <span class="badge bg-danger">
                                Habis
                            </span>

                            @endif

                        </td>

                        <td>
                            {{ $item->description ?? '-' }}
                        </td>

                        <td>

                            <div class="d-flex gap-2">

                                <a href="{{ route('items.edit', $item->id) }}"
                                    class="btn btn-warning btn-sm">

                                    <i class="bi bi-pencil-square"></i>

                                </a>

                                <form action="{{ route('items.destroy', $item->id) }}"
                                    method="POST">

                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus barang ini?')">

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