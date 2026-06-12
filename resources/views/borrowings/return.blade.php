@extends('layouts.app')

@section('title', 'Pengembalian Barang')

@section('content')

<div class="card dashboard-card">

    <div class="card-body">

        <div class="mb-4">

            <h5 class="fw-bold mb-1">
                Formulir Pengembalian Barang
            </h5>

            <p class="text-muted mb-0">
                {{ $borrowing->item->name }}
            </p>

        </div>

        <div class="mb-3 small text-muted">
            Peminjam: <strong>{{ $borrowing->user->name }}</strong> ·
            Jumlah: <strong>{{ $borrowing->quantity }}</strong> ·
            Rencana kembali: <strong>{{ $borrowing->expected_return_date ?? '-' }}</strong>
        </div>

        <form action="{{ route('borrowings.return', $borrowing) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

            <div class="mb-4">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-images text-primary"></i> Dokumentasi Foto Kondisi Barang
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="borrowing-photo-panel borrowing-photo-panel--borrow h-100">
                            <div class="borrowing-photo-panel__header">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Foto Saat Pengajuan
                            </div>
                            <div class="borrowing-photo-panel__body text-center">
                                <p class="borrowing-photo-panel__hint">
                                    Referensi kondisi barang ketika pengajuan disetujui.
                                </p>
                                @if($borrowing->borrow_image)
                                    @include('partials.borrowing-image', [
                                        'path' => $borrowing->borrow_image,
                                        'label' => 'Foto saat pengajuan',
                                        'variant' => 'preview',
                                        'thumb' => false,
                                        'lazy' => false,
                                    ])
                                @else
                                    @include('partials.borrowing-image', [
                                        'path' => null,
                                        'variant' => 'preview',
                                    ])
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="borrowing-photo-panel borrowing-photo-panel--return h-100">
                            <div class="borrowing-photo-panel__header">
                                <i class="bi bi-box-arrow-in-left"></i>
                                Foto Saat Pengembalian
                            </div>
                            <div class="borrowing-photo-panel__body">
                                <p class="borrowing-photo-panel__hint">
                                    Unggah foto kondisi barang saat ini untuk dibandingkan dengan foto pengajuan.
                                </p>
                                @include('partials.borrowing-image-upload', [
                                    'inputId' => 'return_image',
                                    'inputName' => 'return_image',
                                    'label' => 'Seret atau pilih foto pengembalian',
                                    'hint' => 'Pastikan kondisi barang terlihat jelas · maks. 5 MB',
                                    'errorKey' => 'return_image',
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">

                <label class="form-label">
                    Kondisi Barang
                </label>

                <select name="return_condition"
                    class="form-select @error('return_condition') is-invalid @enderror"
                    required>

                    <option value="">
                        Pilih kondisi
                    </option>

                    <option value="good" {{ old('return_condition') == 'good' ? 'selected' : '' }}>
                        Baik
                    </option>

                    <option value="minor_damage" {{ old('return_condition') == 'minor_damage' ? 'selected' : '' }}>
                        Rusak Ringan
                    </option>

                    <option value="broken" {{ old('return_condition') == 'broken' ? 'selected' : '' }}>
                        Rusak Berat
                    </option>

                </select>

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Catatan Pengembalian
                </label>

                <textarea name="return_note"
                    class="form-control"
                    rows="4"
                    placeholder="Catatan tambahan mengenai kondisi barang (opsional)">{{ old('return_note') }}</textarea>

            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Simpan Pengembalian
                </button>
                <a href="{{ route('approvals.index') }}" class="btn btn-light">Batal</a>
            </div>

        </form>

    </div>

</div>

@endsection
