@php
    $variant = $variant ?? 'thumb';
    $useThumb = $thumb ?? ($variant === 'thumb' || $variant === 'compare');
    $fullUrl = \App\Support\BorrowingImageStorage::url($path ?? null);
    $displayUrl = $useThumb && $fullUrl
        ? \App\Support\BorrowingImageStorage::thumbUrl($path ?? null)
        : $fullUrl;
    $pixelSize = match ($variant) {
        'preview' => $size ?? 160,
        'hero' => $size ?? 220,
        'compare' => $size ?? 52,
        default => $size ?? 56,
    };
    $photoLabel = $label ?? 'Gambar barang';
@endphp

@if($fullUrl)
<div class="borrowing-photo borrowing-photo--{{ $variant }}" style="--photo-size: {{ $pixelSize }}px;">
    <button type="button"
        class="borrowing-photo__trigger js-borrowing-photo-view"
        data-photo-url="{{ $fullUrl }}"
        data-photo-title="{{ $photoLabel }}"
        aria-label="Perbesar {{ $photoLabel }}">
        <img src="{{ $displayUrl }}"
            alt="{{ $photoLabel }}"
            class="borrowing-photo__img"
            width="{{ $pixelSize }}"
            height="{{ $pixelSize }}"
            loading="{{ ($lazy ?? true) ? 'lazy' : 'eager' }}"
            decoding="async">
        <span class="borrowing-photo__overlay" aria-hidden="true">
            <i class="bi bi-zoom-in"></i>
        </span>
    </button>
</div>
@else
<div class="borrowing-photo borrowing-photo--empty borrowing-photo--{{ $variant }}" style="--photo-size: {{ $pixelSize }}px;" aria-label="Belum ada foto">
    <span class="borrowing-photo__empty-icon" aria-hidden="true">
        <i class="bi bi-image"></i>
    </span>
    <span class="borrowing-photo__empty-text">Belum ada</span>
</div>
@endif
