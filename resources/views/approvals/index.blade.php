@extends('layouts.app')



@section('title', 'Persetujuan Peminjaman')



@section('content')



<div class="row g-3 mb-4">

    <div class="col-md-4">

        <div class="card dashboard-card border-warning h-100">

            <div class="card-body">

                <p class="text-muted mb-1 small">Menunggu Persetujuan</p>

                <h3 class="fw-bold text-warning mb-0">{{ $pendingBorrowings->count() }}</h3>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card dashboard-card border-success h-100">

            <div class="card-body">

                <p class="text-muted mb-1 small">Sedang Dipinjam</p>

                <h3 class="fw-bold text-success mb-0">{{ $activeBorrowings->count() }}</h3>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card dashboard-card h-100">

            <div class="card-body">

                <p class="text-muted mb-1 small">Riwayat Terakhir</p>

                <h3 class="fw-bold mb-0">{{ $recentHistory->count() }}</h3>

                <span class="small text-muted">Ditolak / dikembalikan</span>

            </div>

        </div>

    </div>

</div>



<div class="card dashboard-card mb-4">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <div>

                <h5 class="fw-bold mb-1">

                    <i class="bi bi-hourglass-split text-warning"></i> Menunggu Persetujuan

                </h5>

                <p class="text-muted mb-0">Pengajuan peminjaman yang perlu disetujui atau ditolak</p>

            </div>

            <a href="{{ route('export.borrowings') }}" class="btn btn-success btn-sm">

                <i class="bi bi-file-earmark-excel"></i> Ekspor Excel

            </a>

        </div>



        @include('partials.datatable-filters', [
            'tableId' => 'pendingApprovalsTable',
            'filters' => [
                [
                    'label' => 'Filter Peminjam',
                    'column' => 0,
                    'placeholder' => 'Semua peminjam',
                    'options' => $pendingBorrowerOptions,
                ],
                [
                    'label' => 'Filter Barang',
                    'column' => 1,
                    'placeholder' => 'Semua barang',
                    'options' => $pendingItemOptions,
                ],
            ],
        ])

        <div class="table-responsive">

            <table id="pendingApprovalsTable"
                class="table align-middle js-filterable-table">

                <thead class="table-light">

                    <tr>

                        <th class="js-sort-col">Peminjam</th>

                        <th class="js-sort-col">Barang</th>

                        <th class="js-sort-col" data-sort-type="number">Jumlah</th>

                        <th class="js-sort-col" data-sort-type="date">Tanggal Pinjam</th>

                        <th class="js-sort-col" data-sort-type="date">Rencana Kembali</th>

                        <th class="js-sort-col">Deskripsi</th>

                        <th>Foto Pengajuan</th>

                        <th class="js-sort-col" data-sort-type="number">Stok Tersedia</th>

                        <th width="280">Tindakan</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($pendingBorrowings as $borrowing)

                    <tr>

                        <td data-filter="{{ $borrowing->user->name }}" data-sort="{{ $borrowing->user->name }}">{{ $borrowing->user->name }}</td>

                        <td data-filter="{{ $borrowing->item->name }}" data-sort="{{ $borrowing->item->name }}">{{ $borrowing->item->name }}</td>

                        <td data-sort="{{ $borrowing->quantity }}">{{ $borrowing->quantity }}</td>

                        <td data-sort="{{ $borrowing->borrow_date }}">{{ $borrowing->borrow_date }}</td>

                        <td data-sort="{{ $borrowing->expected_return_date ?? '' }}">{{ $borrowing->expected_return_date ?? '-' }}</td>

                        <td data-sort="{{ $borrowing->description ?? $borrowing->note ?? '' }}">{{ $borrowing->description ?? $borrowing->note ?? '-' }}</td>

                        <td>
                            @include('partials.borrowing-image', [
                                'path' => $borrowing->borrow_image,
                                'label' => 'Foto pengajuan',
                            ])
                        </td>

                        <td data-sort="{{ $borrowing->item->stock }}">

                            @if($borrowing->item->stock >= $borrowing->quantity)

                            <span class="badge bg-success">{{ $borrowing->item->stock }}</span>

                            @else

                            <span class="badge bg-danger">{{ $borrowing->item->stock }} (kurang)</span>

                            @endif

                        </td>

                        <td>

                            <div class="d-flex flex-column gap-2">

                                <form action="{{ route('approvals.approve', $borrowing) }}" method="POST">

                                    @csrf

                                    <textarea name="approval_note"

                                        class="form-control form-control-sm mb-1"

                                        rows="2"

                                        placeholder="Catatan persetujuan (wajib)"

                                        required></textarea>

                                    <button class="btn btn-success btn-sm w-100"

                                        onclick="return confirm('Setujui pengajuan peminjaman ini?')">

                                        <i class="bi bi-check-lg"></i> Setujui

                                    </button>

                                </form>

                                <form action="{{ route('approvals.reject', $borrowing) }}" method="POST">

                                    @csrf

                                    <textarea name="approval_note"

                                        class="form-control form-control-sm mb-1"

                                        rows="2"

                                        placeholder="Alasan penolakan (wajib)"

                                        required></textarea>

                                    <button class="btn btn-danger btn-sm w-100"

                                        onclick="return confirm('Tolak pengajuan peminjaman ini?')">

                                        <i class="bi bi-x-lg"></i> Tolak

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    @empty

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($pendingBorrowings->isEmpty())

        <p class="text-muted text-center py-3 mb-0">Tidak ada pengajuan yang menunggu persetujuan.</p>

        @endif

    </div>

</div>



<div class="card dashboard-card mb-4">

    <div class="card-body">

        <h5 class="fw-bold mb-1">

            <i class="bi bi-box-arrow-right text-success"></i> Barang Sedang Dipinjam

        </h5>

        <p class="text-muted mb-3">Peminjaman yang telah disetujui — proses pengembalian di sini</p>



        @include('partials.datatable-filters', [
            'tableId' => 'activeBorrowingsTable',
            'filters' => [
                [
                    'label' => 'Filter Peminjam',
                    'column' => 0,
                    'placeholder' => 'Semua peminjam',
                    'options' => $activeBorrowerOptions,
                ],
                [
                    'label' => 'Filter Barang',
                    'column' => 1,
                    'placeholder' => 'Semua barang',
                    'options' => $activeItemOptions,
                ],
            ],
        ])

        <div class="table-responsive">

            <table id="activeBorrowingsTable"
                class="table align-middle js-filterable-table">

                <thead class="table-light">

                    <tr>

                        <th class="js-sort-col">Peminjam</th>

                        <th class="js-sort-col">Barang</th>

                        <th class="js-sort-col" data-sort-type="number">Jumlah</th>

                        <th class="js-sort-col" data-sort-type="date">Tanggal Pinjam</th>

                        <th class="js-sort-col" data-sort-type="date">Rencana Kembali</th>

                        <th>Foto Pengajuan</th>

                        <th width="120">Tindakan</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($activeBorrowings as $borrowing)

                    <tr>

                        <td data-filter="{{ $borrowing->user->name }}" data-sort="{{ $borrowing->user->name }}">{{ $borrowing->user->name }}</td>

                        <td data-filter="{{ $borrowing->item->name }}" data-sort="{{ $borrowing->item->name }}">{{ $borrowing->item->name }}</td>

                        <td data-sort="{{ $borrowing->quantity }}">{{ $borrowing->quantity }}</td>

                        <td data-sort="{{ $borrowing->borrow_date }}">{{ $borrowing->borrow_date }}</td>

                        <td data-sort="{{ $borrowing->expected_return_date ?? '' }}">{{ $borrowing->expected_return_date ?? '-' }}</td>

                        <td>
                            @include('partials.borrowing-image', [
                                'path' => $borrowing->borrow_image,
                                'label' => 'Foto pengajuan',
                            ])
                        </td>

                        <td>

                            <a href="{{ route('borrowings.return.form', $borrowing) }}"

                                class="btn btn-primary btn-sm">

                                <i class="bi bi-arrow-return-left"></i> Kembalikan

                            </a>

                        </td>

                    </tr>

                    @empty

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($activeBorrowings->isEmpty())

        <p class="text-muted text-center py-3 mb-0">Tidak ada barang yang sedang dipinjam.</p>

        @endif

    </div>

</div>



<div class="card dashboard-card">

    <div class="card-body">

        <h5 class="fw-bold mb-3">

            <i class="bi bi-clock-history"></i> Riwayat Persetujuan Terakhir

        </h5>



        @include('partials.datatable-filters', [
            'tableId' => 'historyApprovalsTable',
            'filters' => [
                [
                    'label' => 'Filter Peminjam',
                    'column' => 0,
                    'placeholder' => 'Semua peminjam',
                    'options' => $historyBorrowerOptions,
                ],
                [
                    'label' => 'Filter Barang',
                    'column' => 1,
                    'placeholder' => 'Semua barang',
                    'options' => $historyItemOptions,
                ],
                [
                    'label' => 'Filter Status',
                    'column' => 2,
                    'placeholder' => 'Semua status',
                    'options' => collect(['Ditolak', 'Dikembalikan']),
                ],
            ],
        ])

        <div class="table-responsive">

            <table id="historyApprovalsTable"
                class="table align-middle table-sm js-filterable-table">

                <thead class="table-light">

                    <tr>

                        <th class="js-sort-col">Peminjam</th>

                        <th class="js-sort-col">Barang</th>

                        <th class="js-sort-col">Status</th>

                        <th class="js-sort-col">Catatan Persetujuan</th>

                        <th>Foto</th>

                        <th>Kondisi Pengembalian</th>

                        <th class="js-sort-col" data-sort-type="date">Tanggal</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($recentHistory as $borrowing)

                    <tr>

                        <td data-filter="{{ $borrowing->user->name }}" data-sort="{{ $borrowing->user->name }}">{{ $borrowing->user->name }}</td>

                        <td data-filter="{{ $borrowing->item->name }}" data-sort="{{ $borrowing->item->name }}">{{ $borrowing->item->name }}</td>

                        <td data-filter="{{ $historyStatus = $borrowing->status === 'rejected' ? 'Ditolak' : 'Dikembalikan' }}" data-sort="{{ $historyStatus }}">

                            @if($borrowing->status === 'rejected')

                            <span class="badge bg-danger">{{ $historyStatus }}</span>

                            @else

                            <span class="badge bg-primary">{{ $historyStatus }}</span>

                            @endif

                        </td>

                        <td data-sort="{{ $borrowing->approval_note ?? '' }}">{{ $borrowing->approval_note ?? '-' }}</td>

                        <td>
                            @include('partials.borrowing-images-compare', ['borrowing' => $borrowing])
                        </td>

                        <td>

                            @include('partials.borrowing-return-condition', ['borrowing' => $borrowing])

                        </td>

                        <td data-sort="{{ $borrowing->updated_at->timestamp }}">{{ $borrowing->updated_at->format('d/m/Y H:i') }}</td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        @if($recentHistory->isEmpty())

        <p class="text-muted text-center py-3 mb-0">Belum ada riwayat.</p>

        @endif

    </div>

</div>



@endsection

