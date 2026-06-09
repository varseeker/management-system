@extends('layouts.app')



@section('title', 'Peminjaman Barang')



@section('content')



<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">

        <div class="card dashboard-card h-100">

            <div class="card-body py-3">

                <p class="text-muted mb-0 small">Total</p>

                <h4 class="fw-bold mb-0">{{ $stats['total'] }}</h4>

            </div>

        </div>

    </div>

    <div class="col-6 col-md-3">

        <div class="card dashboard-card border-warning h-100">

            <div class="card-body py-3">

                <p class="text-muted mb-0 small">Menunggu</p>

                <h4 class="fw-bold text-warning mb-0">{{ $stats['pending'] }}</h4>

            </div>

        </div>

    </div>

    <div class="col-6 col-md-3">

        <div class="card dashboard-card border-success h-100">

            <div class="card-body py-3">

                <p class="text-muted mb-0 small">Dipinjam</p>

                <h4 class="fw-bold text-success mb-0">{{ $stats['approved'] }}</h4>

            </div>

        </div>

    </div>

    <div class="col-6 col-md-3">

        <div class="card dashboard-card h-100">

            <div class="card-body py-3">

                <p class="text-muted mb-0 small">Dikembalikan</p>

                <h4 class="fw-bold mb-0">{{ $stats['returned'] }}</h4>

            </div>

        </div>

    </div>

</div>



<div class="card dashboard-card">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <div>

                <h5 class="fw-bold mb-1">Data Peminjaman</h5>

                <p class="text-muted mb-0">

                    @if(in_array(auth()->user()->role, ['admin', 'owner']))

                    Riwayat seluruh peminjaman — persetujuan di menu terpisah

                    @else

                    Riwayat peminjaman Anda

                    @endif

                </p>

            </div>

            <div class="d-flex gap-2">

                @if(in_array(auth()->user()->role, ['admin', 'owner']))

                <a href="{{ route('export.borrowings', request()->only('status')) }}" class="btn btn-success btn-sm">

                    <i class="bi bi-file-earmark-excel"></i> Ekspor Excel

                </a>

                @endif

                <a href="{{ route('borrowings.create') }}" class="btn btn-primary">

                    <i class="bi bi-plus-lg"></i> Ajukan Peminjaman

                </a>

            </div>

        </div>



        <div class="d-flex flex-wrap gap-2 mb-4">

            <a href="{{ route('borrowings.index') }}"

                class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">

                Semua

            </a>

            <a href="{{ route('borrowings.index', ['status' => 'pending']) }}"

                class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning' : 'btn-outline-warning' }}">

                Menunggu

            </a>

            <a href="{{ route('borrowings.index', ['status' => 'approved']) }}"

                class="btn btn-sm {{ request('status') === 'approved' ? 'btn-success' : 'btn-outline-success' }}">

                Dipinjam

            </a>

            <a href="{{ route('borrowings.index', ['status' => 'returned']) }}"

                class="btn btn-sm {{ request('status') === 'returned' ? 'btn-primary' : 'btn-outline-secondary' }}">

                Dikembalikan

            </a>

            <a href="{{ route('borrowings.index', ['status' => 'rejected']) }}"

                class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-danger' : 'btn-outline-danger' }}">

                Ditolak

            </a>

            @if(in_array(auth()->user()->role, ['admin', 'owner']))

            <a href="{{ route('approvals.index') }}" class="btn btn-sm btn-dark ms-auto">

                <i class="bi bi-check2-square"></i> Ke Halaman Persetujuan

            </a>

            @endif

        </div>



        @php
            $isManager = in_array(auth()->user()->role, ['admin', 'owner']);
            $borrowingFilters = [
                [
                    'label' => 'Filter Barang',
                    'column' => $isManager ? 1 : 0,
                    'placeholder' => 'Semua barang',
                    'options' => $itemFilterOptions,
                ],
            ];
            if ($isManager) {
                $borrowingFilters[] = [
                    'label' => 'Filter Peminjam',
                    'column' => 0,
                    'placeholder' => 'Semua peminjam',
                    'options' => $borrowerFilterOptions,
                ];
                $borrowingFilters[] = [
                    'label' => 'Filter Status',
                    'column' => 7,
                    'placeholder' => 'Semua status',
                    'options' => collect(['Menunggu', 'Dipinjam', 'Ditolak', 'Dikembalikan']),
                ];
            } else {
                $borrowingFilters[] = [
                    'label' => 'Filter Status',
                    'column' => 6,
                    'placeholder' => 'Semua status',
                    'options' => collect(['Menunggu', 'Dipinjam', 'Ditolak', 'Dikembalikan']),
                ];
            }
        @endphp

        @include('partials.datatable-filters', [
            'tableId' => 'borrowingsTable',
            'filters' => $borrowingFilters,
        ])

        <div class="table-responsive">

            <table id="borrowingsTable"
                class="table align-middle js-filterable-table">

                <thead class="table-light">

                    <tr>

                        @if(in_array(auth()->user()->role, ['admin', 'owner']))

                        <th class="js-sort-col">Peminjam</th>

                        @endif

                        <th class="js-sort-col">Barang</th>

                        <th class="js-sort-col" data-sort-type="number">Jumlah</th>

                        <th class="js-sort-col" data-sort-type="date">Tanggal Pinjam</th>

                        <th class="js-sort-col" data-sort-type="date">Rencana Kembali</th>

                        <th class="js-sort-col">Deskripsi</th>

                        <th>Foto</th>

                        <th class="js-sort-col">Status</th>

                        <th>Kondisi Pengembalian</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($borrowings as $borrowing)

                    <tr>

                        @if(in_array(auth()->user()->role, ['admin', 'owner']))

                        <td data-filter="{{ $borrowing->user->name }}" data-sort="{{ $borrowing->user->name }}">{{ $borrowing->user->name }}</td>

                        @endif

                        <td data-filter="{{ $borrowing->item->name }}" data-sort="{{ $borrowing->item->name }}">{{ $borrowing->item->name }}</td>

                        <td data-sort="{{ $borrowing->quantity }}">{{ $borrowing->quantity }}</td>

                        <td data-sort="{{ $borrowing->borrow_date }}">{{ $borrowing->borrow_date }}</td>

                        <td data-sort="{{ $borrowing->expected_return_date ?? '' }}">{{ $borrowing->expected_return_date ?? '-' }}</td>

                        <td data-sort="{{ $borrowing->description ?? $borrowing->note ?? '' }}">{{ $borrowing->description ?? $borrowing->note ?? '-' }}</td>

                        <td>
                            @include('partials.borrowing-images-compare', ['borrowing' => $borrowing])
                        </td>

                        <td data-filter="{{ $statusLabel = match($borrowing->status) {
                            'pending' => 'Menunggu',
                            'approved' => 'Dipinjam',
                            'rejected' => 'Ditolak',
                            default => 'Dikembalikan',
                        } }}" data-sort="{{ $statusLabel }}">

                            @if($borrowing->status == 'pending')

                            <span class="badge bg-warning">{{ $statusLabel }}</span>

                            @elseif($borrowing->status == 'approved')

                            <span class="badge bg-success">{{ $statusLabel }}</span>

                            @elseif($borrowing->status == 'rejected')

                            <span class="badge bg-danger">{{ $statusLabel }}</span>

                            @else

                            <span class="badge bg-primary">{{ $statusLabel }}</span>

                            @endif

                        </td>

                        <td>

                            @include('partials.borrowing-return-condition', ['borrowing' => $borrowing])

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        @if($borrowings->isEmpty())

        <p class="text-muted text-center py-4 mb-0">Tidak ada data peminjaman.</p>

        @endif

    </div>

</div>



@endsection

