@extends('layouts.app')



@section('title', 'Proses Pesanan Menu')



@section('content')



<div class="row g-4">

    <div class="col-lg-5">

        <div class="card dashboard-card">

            <div class="card-body">

                <h5 class="fw-bold mb-3">Kurangi Stok melalui Menu</h5>

                <p class="text-muted small">Contoh: 2 porsi Indomie Telur = 2× indomie + 2× telur</p>



                @foreach($menus as $menu)

                <div class="border rounded p-3 mb-3">

                    <div class="fw-semibold">{{ $menu->name }}</div>

                    <div class="small text-muted mb-2">

                        @foreach($menu->rawMaterials as $material)

                        {{ $material->pivot->quantity }}× {{ $material->name }}

                        @if(!$loop->last) + @endif

                        @endforeach

                    </div>



                    <form action="{{ route('menus.sell', $menu) }}" method="POST" class="row g-2 align-items-end">

                        @csrf

                        <div class="col-4">

                            <label class="form-label small">Porsi</label>

                            <input type="number" name="quantity" class="form-control" min="1" value="1" required>

                        </div>

                        <div class="col-5">

                            <label class="form-label small">Catatan</label>

                            <input type="text" name="note" class="form-control" placeholder="Opsional">

                        </div>

                        <div class="col-3">

                            <button class="btn btn-success w-100">Proses</button>

                        </div>

                    </form>

                </div>

                @endforeach



                @if($menus->isEmpty())

                <p class="text-muted">Belum ada menu aktif.

                    @if(in_array(auth()->user()->role, ['admin', 'owner']))

                    <a href="{{ route('menus.create') }}">Buat menu</a>

                    @endif

                </p>

                @endif

            </div>

        </div>

    </div>



    <div class="col-lg-7">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <h5 class="fw-bold mb-0">Riwayat Pesanan</h5>

                    @if(in_array(auth()->user()->role, ['admin', 'owner']))

                    <a href="{{ route('export.menu-sales') }}" class="btn btn-success btn-sm">

                        <i class="bi bi-file-earmark-excel"></i> Ekspor Excel

                    </a>

                    @endif

                </div>

                @include('partials.datatable-filters', [
                    'tableId' => 'salesTable',
                    'filters' => [
                        [
                            'label' => 'Filter Menu',
                            'column' => 1,
                            'placeholder' => 'Semua menu',
                            'options' => $menuFilterOptions,
                        ],
                        [
                            'label' => 'Filter Pengguna',
                            'column' => 3,
                            'placeholder' => 'Semua pengguna',
                            'options' => $userFilterOptions,
                        ],
                    ],
                ])

                <div class="table-responsive">

                    <table id="salesTable" class="table align-middle js-filterable-table">

                        <thead class="table-light">

                            <tr>

                                <th class="js-sort-col" data-sort-type="date">Waktu</th>

                                <th class="js-sort-col">Menu</th>

                                <th class="js-sort-col" data-sort-type="number">Porsi</th>

                                <th class="js-sort-col">Pengguna</th>

                                <th class="js-sort-col">Catatan</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($sales as $sale)

                            <tr>

                                <td data-sort="{{ $sale->created_at->timestamp }}">{{ $sale->created_at->format('d/m/Y H:i') }}</td>

                                <td data-filter="{{ $sale->menu->name }}" data-sort="{{ $sale->menu->name }}">{{ $sale->menu->name }}</td>

                                <td data-sort="{{ $sale->quantity }}">{{ $sale->quantity }}</td>

                                <td data-filter="{{ $sale->user->name }}" data-sort="{{ $sale->user->name }}">{{ $sale->user->name }}</td>

                                <td data-sort="{{ $sale->note ?? '' }}">{{ $sale->note ?? '-' }}</td>

                            </tr>

                            @empty

                            <tr>

                                <td colspan="5" class="text-center text-muted">Belum ada transaksi</td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>



@endsection

