@props([
    'type' => 'info',
    'title' => null,
    'icon' => null,
])

@php
    $icons = [
        'info' => 'bi-info-circle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-x-circle-fill',
    ];
    $iconClass = $icon ?? ($icons[$type] ?? $icons['info']);
@endphp

<div {{ $attributes->merge(['class' => "callout callout-{$type}"]) }}>
    <span class="callout__icon" aria-hidden="true">
        <i class="bi {{ $iconClass }}"></i>
    </span>
    <div class="callout__body">
        @if($title)
        <p class="callout__title mb-1">{{ $title }}</p>
        @endif
        <div class="callout__content">
            {{ $slot }}
        </div>
    </div>
</div>
