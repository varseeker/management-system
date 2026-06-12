@extends('layouts.app')

@section('title', 'Ajukan Peminjaman Barang')

@section('content')

<div class="card dashboard-card">

    <div class="card-body">

        <x-callout type="info" title="Ketentuan peminjaman" class="mb-4">
            Maksimal <strong>{{ $maxQuantity }} unit</strong> per pengajuan
            dan jangka waktu maksimal <strong>{{ $maxLoanDays }} hari</strong>.
            Unggah foto kondisi barang agar persetujuan lebih jelas.
        </x-callout>

        <form action="{{ route('borrowings.store') }}"
            method="POST"
            enctype="multipart/form-data"
            id="borrowing-form">

            @csrf

            <div class="mb-3">

                <label class="form-label">
                    Barang
                </label>

                <select name="item_id"
                    class="form-select @error('item_id') is-invalid @enderror"
                    required>

                    <option value="">
                        Pilih barang
                    </option>

                    @foreach($items as $item)

                    <option value="{{ $item->id }}"
                        {{ old('item_id') == $item->id ? 'selected' : '' }}>

                        {{ $item->name }}
                        (Stok: {{ $item->stock }})

                    </option>

                    @endforeach

                </select>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Jumlah Pinjam
                </label>

                <input type="number"
                    name="quantity"
                    class="form-control @error('quantity') is-invalid @enderror"
                    min="1"
                    max="{{ $maxQuantity }}"
                    value="{{ old('quantity', 1) }}"
                    required>

                <div class="form-text">Maksimal {{ $maxQuantity }} unit per pengajuan.</div>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Tanggal Pinjam
                </label>

                <input type="date"
                    name="borrow_date"
                    class="form-control @error('borrow_date') is-invalid @enderror"
                    value="{{ old('borrow_date', now()->format('Y-m-d')) }}"
                    required>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Tanggal Rencana Pengembalian
                </label>

                <input type="date"
                    name="expected_return_date"
                    class="form-control @error('expected_return_date') is-invalid @enderror"
                    value="{{ old('expected_return_date') }}"
                    required>

                <div class="form-text">Maksimal {{ $maxLoanDays }} hari sejak tanggal pinjam.</div>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Deskripsi / Alasan Pengajuan
                </label>

                <textarea name="description"
                    class="form-control @error('description') is-invalid @enderror"
                    rows="4"
                    placeholder="Jelaskan alasan dan keperluan peminjaman barang"
                    required>{{ old('description') }}</textarea>

            </div>

            <div class="mb-4">
                <h6 class="fw-semibold mb-2">
                    <i class="bi bi-camera text-primary"></i> Foto Kondisi Barang (Saat Pengajuan)
                </h6>
                <p class="text-muted small mb-3">
                    Dokumentasikan kondisi barang sebelum dipinjam. Foto ini akan digunakan saat proses pengembalian.
                </p>
                @include('partials.borrowing-image-upload', [
                    'inputId' => 'borrow_image',
                    'inputName' => 'borrow_image',
                    'label' => 'Seret atau pilih foto pengajuan',
                    'hint' => 'JPG, PNG, atau WEBP · maks. 5 MB',
                    'errorKey' => 'borrow_image',
                ])
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary" id="btn-open-confirm-modal">
                    <i class="bi bi-check-lg"></i>
                    Kirim Pengajuan
                </button>
                <a href="{{ route('borrowings.index') }}" class="btn btn-light">Batal</a>
            </div>

        </form>

    </div>

</div>

<div class="modal fade" id="borrowingConfirmModal" tabindex="-1" aria-labelledby="borrowingConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="borrowingConfirmModalLabel">
                    <i class="bi bi-exclamation-circle text-warning"></i>
                    Konfirmasi Pengajuan Peminjaman
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    Sebelum mengirim pengajuan, bacalah ketentuan berikut. Dengan melanjutkan, Anda menyatakan memahami dan menyetujui seluruh klausul ini.
                </p>

                <div class="border rounded p-3 bg-light mb-3" style="max-height: 320px; overflow-y: auto;">
                    <h6 class="fw-bold mb-2">1. Batas Waktu Pengembalian</h6>
                    <ul class="small mb-3">
                        <li>Peminjaman maksimal <strong>{{ $maxQuantity }} unit</strong> per pengajuan.</li>
                        <li>Jangka waktu peminjaman maksimal <strong>{{ $maxLoanDays }} hari</strong> sejak tanggal pinjam yang disetujui.</li>
                        <li>Barang wajib dikembalikan paling lambat pada <strong>tanggal rencana pengembalian</strong> yang Anda ajukan dan disetujui pemilik.</li>
                        <li>{{ config('inventory.borrowing.late_penalty') }}</li>
                    </ul>

                    <h6 class="fw-bold mb-2">2. Kondisi Barang dan Dokumentasi</h6>
                    <ul class="small mb-3">
                        <li>Peminjam wajib mengunggah foto kondisi barang saat pengajuan dan saat pengembalian sebagai bukti keadaan barang.</li>
                        <li>Barang harus dikembalikan dalam keadaan sama seperti saat dipinjam, kecuali terjadi kerusakan yang wajar karena pemakaian normal.</li>
                    </ul>

                    <h6 class="fw-bold mb-2">3. Sanksi Kerusakan Barang</h6>
                    <ul class="small mb-0">
                        <li><strong>Rusak ringan:</strong> {{ config('inventory.borrowing.minor_damage_penalty') }}</li>
                        <li><strong>Rusak berat / tidak dapat diperbaiki:</strong> {{ config('inventory.borrowing.broken_penalty') }}</li>
                        <li>Penilaian kondisi barang ditentukan oleh pemilik berdasarkan foto, catatan pengembalian, dan pemeriksaan fisik.</li>
                        <li>Sanksi dapat berupa pemotongan gaji, penggantian barang, atau tindakan administratif lain sesuai kebijakan warkop.</li>
                    </ul>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="terms-agreement">
                    <label class="form-check-label" for="terms-agreement">
                        Saya telah membaca, memahami, dan menyetujui seluruh ketentuan peminjaman di atas.
                    </label>
                </div>
                <div id="terms-error" class="text-danger small mt-2 d-none">
                    Anda harus menyetujui ketentuan peminjaman terlebih dahulu.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-submit" disabled>
                    <i class="bi bi-send"></i> Ya, Kirim Pengajuan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('borrowing-form');
    const openBtn = document.getElementById('btn-open-confirm-modal');
    const confirmBtn = document.getElementById('btn-confirm-submit');
    const agreement = document.getElementById('terms-agreement');
    const termsError = document.getElementById('terms-error');
    const modalEl = document.getElementById('borrowingConfirmModal');

    if (!form || !openBtn || !confirmBtn || !agreement || !modalEl) {
        return;
    }

    openBtn.addEventListener('click', function () {
        if (!form.reportValidity()) {
            return;
        }

        agreement.checked = false;
        confirmBtn.disabled = true;
        termsError.classList.add('d-none');

        if (window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }
    });

    agreement.addEventListener('change', function () {
        confirmBtn.disabled = !agreement.checked;

        if (agreement.checked) {
            termsError.classList.add('d-none');
        }
    });

    confirmBtn.addEventListener('click', function () {
        if (!agreement.checked) {
            termsError.classList.remove('d-none');
            return;
        }

        if (window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        } else {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
        }

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    });

    modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!window.bootstrap?.Modal) {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                modalEl.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            }
        });
    });
});
</script>

@endsection
