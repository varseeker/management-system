<div class="d-flex gap-2 flex-wrap">
    <div class="text-center">
        <div class="small text-muted mb-1">Pengajuan</div>
        @include('partials.borrowing-image', [
            'path' => $borrowing->borrow_image,
            'label' => 'Foto pengajuan',
            'size' => $size ?? 48,
        ])
    </div>
    <div class="text-center">
        <div class="small text-muted mb-1">Pengembalian</div>
        @include('partials.borrowing-image', [
            'path' => $borrowing->return_image,
            'label' => 'Foto pengembalian',
            'size' => $size ?? 48,
        ])
    </div>
</div>
