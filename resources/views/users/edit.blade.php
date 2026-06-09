@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')

<div class="card dashboard-card">

    <div class="card-body">

        <form action="{{ route('users.update', $user->id) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="mb-3">

                <label class="form-label">
                    Nama
                </label>

                <input type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name', $user->name) }}"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Email
                </label>

                <input type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email', $user->email) }}"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Peran
                </label>

                <select name="role"
                    class="form-select"
                    required>

                    <option value="staff"
                        {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>
                        Staf
                    </option>

                    <option value="owner"
                        {{ old('role', $user->role) == 'owner' ? 'selected' : '' }}>
                        Pemilik
                    </option>

                    <option value="admin"
                        {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Kata Sandi Baru
                </label>

                <input type="password"
                    name="password"
                    class="form-control"
                    autocomplete="new-password">

                <div class="form-text">Kosongkan jika tidak ingin mengubah kata sandi.</div>

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Konfirmasi Kata Sandi
                </label>

                <input type="password"
                    name="password_confirmation"
                    class="form-control"
                    autocomplete="new-password">

            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">
                    <i class="bi bi-check-lg"></i>
                    Simpan Perubahan
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
            </div>

        </form>

    </div>

</div>

@endsection
