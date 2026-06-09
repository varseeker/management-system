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

        @if($borrowing->borrow_image)
        <div class="mb-4 p-3 bg-light rounded">
            <p class="fw-semibold mb-2 small">Foto kondisi saat pengajuan peminjaman:</p>
            @include('partials.borrowing-image', [
                'path' => $borrowing->borrow_image,
                'label' => 'Foto saat pengajuan',
                'size' => 160,
            ])
        </div>
        @endif

        <form action="{{ route('borrowings.return', $borrowing) }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf

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

            <div class="mb-3">

                <label class="form-label">
                    Foto Kondisi Barang (Saat Pengembalian)
                </label>

                <input type="file"
                    name="return_image"
                    id="return_image"
                    class="form-control @error('return_image') is-invalid @enderror"
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    required>

                <div class="form-text">Unggah foto kondisi barang saat ini untuk perbandingan. Maksimal 5 MB.</div>

                <div id="return_image_preview" class="mt-3 d-none">
                    <p class="small text-muted mb-1">Pratinjau:</p>
                    <img alt="Pratinjau foto pengembalian" class="rounded border" style="max-width: 280px; max-height: 200px; object-fit: cover;">
                </div>

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

            <div class="d-flex gap-2">
                <button class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Simpan Pengembalian
                </button>
                <a href="{{ route('approvals.index') }}" class="btn btn-light">Batal</a>
            </div>

        </form>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const returnImageInput = document.getElementById('return_image');
    const preview = document.getElementById('return_image_preview');

    if (!returnImageInput || !preview) {
        return;
    }

    const previewImg = preview.querySelector('img');
    let previewObjectUrl = null;

    returnImageInput.addEventListener('change', function (e) {
        const file = e.target.files[0];

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }

        if (!file) {
            preview.classList.add('d-none');
            previewImg.removeAttribute('src');
            return;
        }

        previewObjectUrl = URL.createObjectURL(file);
        previewImg.src = previewObjectUrl;
        preview.classList.remove('d-none');
    });
});
</script>

@endsection
