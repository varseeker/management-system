<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistem Manajemen Warkop Kayu</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>

    <div class="wrapper">

        <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar" aria-label="Menu navigasi">

            <div class="brand">
                <span class="brand-text">Sistem Manajemen Warkop Kayu</span>
                <button type="button" class="sidebar-close d-lg-none" id="sidebarClose" aria-label="Tutup menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="sidebar-menu">

                <a href="/dashboard"
                    class="{{ request()->is('dashboard') ? 'active' : '' }}">

                    <i class="bi bi-grid-fill"></i>
                    Dasbor
                </a>

                @if(in_array(auth()->user()->role, ['admin', 'owner']))
                <a href="{{ route('items.index') }}"
                    class="{{ request()->is('items*') ? 'active' : '' }}">

                    <i class="bi bi-box-seam"></i>
                    Barang
                </a>

                <a href="{{ route('raw-materials.index') }}"
                    class="{{ request()->is('raw-materials*') ? 'active' : '' }}">

                    <i class="bi bi-basket"></i>
                    Bahan Baku
                </a>

                <a href="{{ route('suppliers.index') }}"
                    class="{{ request()->is('suppliers*') ? 'active' : '' }}">

                    <i class="bi bi-truck"></i>
                    Pemasok
                </a>

                <a href="{{ route('menus.index') }}"
                    class="{{ request()->is('menus') || request()->is('menus/create') || request()->is('menus/*/edit') ? 'active' : '' }}">

                    <i class="bi bi-cup-hot"></i>
                    Menu
                </a>

                @if(config('inventory.pos_url'))
                <a href="{{ config('inventory.pos_url') }}" target="_blank" rel="noopener">
                    <i class="bi bi-display"></i>
                    Kasir (POS)
                </a>
                @endif

                <a href="{{ route('reports.orders.index') }}"
                    class="{{ request()->is('reports/orders*') ? 'active' : '' }}">

                    <i class="bi bi-receipt"></i>
                    Laporan Pesanan
                </a>

                <a href="{{ route('reports.payments.index') }}"
                    class="{{ request()->is('reports/payments*') ? 'active' : '' }}">

                    <i class="bi bi-credit-card"></i>
                    Laporan Pembayaran
                </a>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'owner', 'staff']))
                <a href="{{ route('menus.sell.index') }}"
                    class="{{ request()->is('menus/sell') ? 'active' : '' }}">

                    <i class="bi bi-cart-check"></i>
                    Penjualan Langsung
                </a>

                <a href="{{ route('borrowings.index') }}"
                    class="{{ request()->is('borrowings') || request()->is('borrowings/create') ? 'active' : '' }}">

                    <i class="bi bi-arrow-left-right"></i>
                    Peminjaman
                </a>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'owner']))
                <a href="{{ route('approvals.index') }}"
                    class="{{ request()->is('approvals*') ? 'active' : '' }}">

                    <i class="bi bi-check2-square"></i>
                    Persetujuan
                    @if(($pendingApprovalCount ?? 0) > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingApprovalCount }}</span>
                    @endif
                </a>
                @endif

                @if(auth()->user()->role == 'admin')
                <a href="{{ route('users.index') }}"
                    class="{{ request()->is('users*') ? 'active' : '' }}">

                    <i class="bi bi-people"></i>
                    Manajemen Pengguna
                </a>
                @endif

            </div>

        </aside>

        <!-- MAIN -->
        <main class="main">

            <!-- HEADER -->
            <header class="header">

                <div class="header-left">

                    <button type="button" class="btn btn-light sidebar-toggle d-lg-none" id="sidebarToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="sidebar">
                        <i class="bi bi-list"></i>
                    </button>

                    <div class="header-titles">
                        <div class="header-title">
                            <h4>@yield('title')</h4>
                        </div>

                        <div class="header-subtitle d-none d-sm-block">
                            Sistem Manajemen Warkop Kayu
                        </div>
                    </div>

                </div>

                <div class="dropdown header-user">

                    <button class="btn btn-light dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-label="Menu pengguna">

                        <i class="bi bi-person-circle"></i>

                        <span class="header-user-name">{{ auth()->user()->name ?? 'Tamu' }}</span>

                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">

                        <li>
                            <form method="POST"
                                action="{{ route('logout') }}">

                                @csrf

                                <button class="dropdown-item">
                                    Keluar
                                </button>

                            </form>
                        </li>

                    </ul>

                </div>

            </header>

            <!-- CONTENT -->
            <section class="content">

                @yield('content')

            </section>

        </main>

    </div>

    @include('partials.toast-container')
    @include('partials.flash-error-modal')
    @include('partials.flash-messages')
    @include('partials.borrowing-photo-modal')

    @stack('scripts')
</body>

</html>
