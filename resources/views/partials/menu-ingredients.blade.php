<div id="ingredients-wrapper">
    @php
        $rows = old('ingredients', $ingredients ?? [['raw_material_id' => '', 'quantity' => 1]]);
    @endphp

    @foreach($rows as $index => $row)
    <div class="row g-2 mb-2 ingredient-row">
        <div class="col-12 col-md-7">
            <select name="ingredients[{{ $index }}][raw_material_id]" class="form-select" required>
                <option value="">Pilih bahan baku</option>
                @foreach($rawMaterials as $material)
                <option value="{{ $material->id }}"
                    @selected(($row['raw_material_id'] ?? '') == $material->id)>
                    {{ $material->name }} (stok: {{ $material->stock }} {{ $material->unit }})
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-8 col-md-3">
            <input type="number" name="ingredients[{{ $index }}][quantity]"
                class="form-control" min="1" value="{{ $row['quantity'] ?? 1 }}" placeholder="Jumlah" required>
        </div>
        <div class="col-4 col-md-2">
            <button type="button" class="btn btn-outline-danger w-100 remove-ingredient">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
    @endforeach
</div>

<button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-ingredient">
    <i class="bi bi-plus-lg"></i> Tambah Bahan
</button>

<template id="ingredient-template">
    <div class="row g-2 mb-2 ingredient-row">
        <div class="col-12 col-md-7">
            <select name="ingredients[__INDEX__][raw_material_id]" class="form-select" required>
                <option value="">Pilih bahan baku</option>
                @foreach($rawMaterials as $material)
                <option value="{{ $material->id }}">
                    {{ $material->name }} (stok: {{ $material->stock }} {{ $material->unit }})
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-8 col-md-3">
            <input type="number" name="ingredients[__INDEX__][quantity]"
                class="form-control" min="1" value="1" placeholder="Jumlah" required>
        </div>
        <div class="col-4 col-md-2">
            <button type="button" class="btn btn-outline-danger w-100 remove-ingredient">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('ingredients-wrapper');
    const template = document.getElementById('ingredient-template');
    let index = wrapper.querySelectorAll('.ingredient-row').length;

    document.getElementById('add-ingredient')?.addEventListener('click', function () {
        const html = template.innerHTML.replaceAll('__INDEX__', index);
        wrapper.insertAdjacentHTML('beforeend', html);
        index++;
    });

    wrapper.addEventListener('click', function (e) {
        if (e.target.closest('.remove-ingredient') && wrapper.querySelectorAll('.ingredient-row').length > 1) {
            e.target.closest('.ingredient-row').remove();
        }
    });
});
</script>
