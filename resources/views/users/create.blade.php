@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')

<div class="card dashboard-card">

    <div class="card-body">

        <form action="{{ route('users.store') }}"
            method="POST">

            @csrf

            <div class="mb-3">

                <label class="form-label">
                    Nama
                </label>

                <input type="text"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Email
                </label>

                <input type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Peran
                </label>

                <select name="role"
                    class="form-select @error('role') is-invalid @enderror"
                    required>

                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staf</option>
                    <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Pemilik</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Kata Sandi
                </label>

                <input type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="new-password">

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Konfirmasi Kata Sandi
                </label>

                <input type="password"
                    name="password_confirmation"
                    class="form-control"
                    required
                    autocomplete="new-password">

            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    Simpan Pengguna
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
            </div>

        </form>

    </div>

</div>

@endsection
