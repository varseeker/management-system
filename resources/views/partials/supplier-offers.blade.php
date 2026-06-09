<div id="offers-wrapper">
    @php
        $rows = old('offers', $offers ?? [['raw_material_id' => '', 'price' => '', 'quality' => 'good']]);
        $qualities = \App\Models\Supplier::QUALITY_LABELS;
    @endphp

    @foreach($rows as $index => $row)
    <div class="row g-2 mb-2 offer-row">
        <div class="col-md-5">
            <select name="offers[{{ $index }}][raw_material_id]" class="form-select" required>
                <option value="">Pilih bahan baku</option>
                @foreach($rawMaterials as $material)
                <option value="{{ $material->id }}"
                    @selected(($row['raw_material_id'] ?? '') == $material->id)>
                    {{ $material->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" name="offers[{{ $index }}][price]" class="form-control"
                min="0" step="0.01" placeholder="Harga" value="{{ $row['price'] ?? '' }}" required>
        </div>
        <div class="col-md-3">
            <select name="offers[{{ $index }}][quality]" class="form-select" required>
                @foreach($qualities as $value => $label)
                <option value="{{ $value }}" @selected(($row['quality'] ?? 'good') == $value)>
                    {{ $label }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger w-100 remove-offer">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    @endforeach
</div>

<button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-offer">
    <i class="bi bi-plus-lg"></i> Tambah Barang
</button>

<template id="offer-template">
    <div class="row g-2 mb-2 offer-row">
        <div class="col-md-5">
            <select name="offers[__INDEX__][raw_material_id]" class="form-select" required>
                <option value="">Pilih bahan baku</option>
                @foreach($rawMaterials as $material)
                <option value="{{ $material->id }}">{{ $material->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" name="offers[__INDEX__][price]" class="form-control"
                min="0" step="0.01" placeholder="Harga" required>
        </div>
        <div class="col-md-3">
            <select name="offers[__INDEX__][quality]" class="form-select" required>
                @foreach($qualities as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger w-100 remove-offer">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('offers-wrapper');
    const template = document.getElementById('offer-template');
    let index = wrapper.querySelectorAll('.offer-row').length;

    document.getElementById('add-offer')?.addEventListener('click', function () {
        wrapper.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index));
        index++;
    });

    wrapper.addEventListener('click', function (e) {
        if (e.target.closest('.remove-offer') && wrapper.querySelectorAll('.offer-row').length > 1) {
            e.target.closest('.offer-row').remove();
        }
    });
});
</script>
