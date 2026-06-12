@php
    $layout = $layout ?? 'inline';
@endphp

<div class="borrowing-photos-compare borrowing-photos-compare--{{ $layout }}">
    <div class="borrowing-photo-slot borrowing-photo-slot--borrow">
        <div class="borrowing-photo-slot__header">
            <span class="borrowing-photo-slot__badge borrowing-photo-slot__badge--borrow">
                <i class="bi bi-box-arrow-up-right"></i>
            </span>
            <span class="borrowing-photo-slot__title">Pengajuan</span>
        </div>
        @include('partials.borrowing-image', [
            'path' => $borrowing->borrow_image,
            'label' => 'Foto pengajuan',
            'variant' => 'compare',
            'size' => $size ?? 52,
        ])
    </div>

    <div class="borrowing-photo-slot__arrow" aria-hidden="true">
        <i class="bi bi-arrow-right"></i>
    </div>

    <div class="borrowing-photo-slot borrowing-photo-slot--return">
        <div class="borrowing-photo-slot__header">
            <span class="borrowing-photo-slot__badge borrowing-photo-slot__badge--return">
                <i class="bi bi-box-arrow-in-left"></i>
            </span>
            <span class="borrowing-photo-slot__title">Pengembalian</span>
        </div>
        @include('partials.borrowing-image', [
            'path' => $borrowing->return_image,
            'label' => 'Foto pengembalian',
            'variant' => 'compare',
            'size' => $size ?? 52,
        ])
    </div>
</div>
