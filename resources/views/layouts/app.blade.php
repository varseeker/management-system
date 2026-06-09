<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sistem Manajemen Warkop Kayu</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">

            <div class="brand">
                Sistem Manajemen Warkop Kayu
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
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'owner', 'staff']))
                <a href="{{ route('menus.sell.index') }}"
                    class="{{ request()->is('menus/sell') ? 'active' : '' }}">

                    <i class="bi bi-cart-check"></i>
                    Pesanan
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
                    @php $pendingApproval = \App\Models\Borrowing::where('status', 'pending')->count(); @endphp
                    @if($pendingApproval > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingApproval }}</span>
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

                <div>

                    <div class="header-title">
                        <h4>@yield('title')</h4>
                    </div>

                    <div class="header-subtitle">
                        Sistem Manajemen Warkop Kayu
                    </div>

                </div>

                <div class="dropdown">

                    <button class="btn btn-light dropdown-toggle"
                        data-bs-toggle="dropdown">

                        <i class="bi bi-person-circle"></i>

                        {{ auth()->user()->name ?? 'Tamu' }}

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

                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

                @endif

                @yield('content')

            </section>

        </main>

    </div>

</body>

</html>
