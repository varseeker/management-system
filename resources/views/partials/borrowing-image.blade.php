@php
    $url = \App\Support\BorrowingImageStorage::url($path ?? null);
@endphp

@if($url)
<a href="{{ $url }}" target="_blank" rel="noopener" title="{{ $label ?? 'Lihat gambar' }}">
    <img src="{{ $url }}"
        alt="{{ $label ?? 'Gambar barang' }}"
        class="rounded border"
        style="width: {{ $size ?? 56 }}px; height: {{ $size ?? 56 }}px; object-fit: cover;">
</a>
@else
<span class="text-muted">-</span>
@endif
