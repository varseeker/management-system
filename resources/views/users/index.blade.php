@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')

<div class="card dashboard-card">

    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <div>
                <h5 class="fw-bold mb-1">
                    Manajemen Pengguna
                </h5>

                <p class="text-muted mb-0">
                    Kelola akses dan peran pengguna sistem
                </p>
            </div>

            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Pengguna
            </a>

        </div>

        <div class="table-responsive">

            <table id="dataTable"
                class="table align-middle">

                <thead class="table-light">

                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th width="150">Tindakan</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($users as $user)

                    <tr>

                        <td>
                            {{ $user->name }}
                        </td>

                        <td>
                            {{ $user->email }}
                        </td>

                        <td>

                            @if($user->role == 'admin')

                            <span class="badge bg-danger">
                                Admin
                            </span>

                            @elseif($user->role == 'owner')

                            <span class="badge bg-warning text-dark">
                                Pemilik
                            </span>

                            @else

                            <span class="badge bg-primary">
                                Staf
                            </span>

                            @endif

                        </td>

                        <td>

                            <a href="{{ route('users.edit', $user->id) }}"
                                class="btn btn-warning btn-sm">

                                <i class="bi bi-pencil-square"></i>

                            </a>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
