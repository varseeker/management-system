@php
    $isBundle = (bool) old('is_bundle', $menu->is_bundle ?? false);
    $currentCategory = old('category', $menu->category ?? 'Makanan');
    $currentCategory = match ($currentCategory) {
        'Snack' => 'Makanan',
        'Coffee', 'Non-coffee', 'Non-Coffee' => 'Minuman',
        default => $currentCategory,
    };
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Harga Jual (POS)</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" name="price" class="form-control" min="0" step="1"
                value="{{ old('price', $menu->price ?? 18000) }}" required>
        </div>
    </div>
    <div class="col-md-4" id="menuCategoryField" @if($isBundle) hidden @endif>
        <label class="form-label">Kategori POS</label>
        <select name="category" id="menuCategorySelect" class="form-select" @unless($isBundle) required @endunless>
            @foreach (['Makanan' => 'Makanan', 'Minuman' => 'Minuman'] as $value => $label)
                <option value="{{ $value }}" @selected($currentCategory === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 d-flex align-items-end flex-wrap gap-3">
        <div class="form-check mb-2">
            <input type="checkbox" name="most_ordered" value="1" class="form-check-input" id="most_ordered"
                @checked(old('most_ordered', $menu->most_ordered ?? false))>
            <label class="form-check-label" for="most_ordered">Tandai favorit di POS</label>
        </div>
        <div class="form-check mb-2">
            <input type="checkbox" name="is_bundle" value="1" class="form-check-input" id="is_bundle"
                @checked($isBundle)>
            <label class="form-check-label" for="is_bundle">Paket / Bundle</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Gambar Menu</label>
        <input type="file" name="image" id="menuImageInput" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
        <div class="form-text">Unggah foto menu. Akan tampil di POS setelah sinkronisasi otomatis.</div>
        <div id="menuImagePreview" class="mt-3 @if(empty($menu?->image_path)) d-none @endif">
            <p class="small text-muted mb-1">Preview gambar menu</p>
            <img
                id="menuImagePreviewImg"
                src="{{ !empty($menu?->image_path) ? $menu->imageUrl() : '' }}"
                alt="Preview gambar menu"
                class="rounded border shadow-sm"
                width="200"
                height="150"
                style="object-fit:cover;"
            >
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('menuImageInput');
            const preview = document.getElementById('menuImagePreview');
            const img = document.getElementById('menuImagePreviewImg');
            const bundleCheckbox = document.getElementById('is_bundle');
            const categoryField = document.getElementById('menuCategoryField');
            const categorySelect = document.getElementById('menuCategorySelect');

            if (input && preview && img) {
                input.addEventListener('change', function () {
                    const file = input.files && input.files[0];

                    if (!file) {
                        return;
                    }

                    img.src = URL.createObjectURL(file);
                    preview.classList.remove('d-none');
                });
            }

            function toggleCategoryField() {
                if (!bundleCheckbox || !categoryField || !categorySelect) {
                    return;
                }

                const isBundle = bundleCheckbox.checked;
                categoryField.hidden = isBundle;
                categorySelect.required = !isBundle;
            }

            if (bundleCheckbox) {
                bundleCheckbox.addEventListener('change', toggleCategoryField);
                toggleCategoryField();
            }
        });
    </script>
    @endpush
@endonce
