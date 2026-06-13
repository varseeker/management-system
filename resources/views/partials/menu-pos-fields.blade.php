<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Harga Jual (POS)</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" name="price" class="form-control" min="0" step="500"
                value="{{ old('price', $menu->price ?? 18000) }}" required>
        </div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Kategori POS</label>
        <select name="category" class="form-select" required>
            @foreach (['Snack' => 'Makanan / Snack', 'Non-coffee' => 'Minuman', 'Coffee' => 'Kopi'] as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $menu->category ?? 'Snack') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="checkbox" name="most_ordered" value="1" class="form-check-input" id="most_ordered"
                @checked(old('most_ordered', $menu->most_ordered ?? false))>
            <label class="form-check-label" for="most_ordered">Tandai favorit di POS</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label">Gambar Menu</label>
        <input type="file" name="image" id="menuImageInput" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
        <div class="form-text">Unggah foto menu. Akan tampil di POS setelah sinkronisasi otomatis.</div>
        <div id="menuImagePreview" class="mt-3 @if(empty($menu?->image_path)) d-none @endif">
            <p class="small text-muted mb-1">Preview gambar</p>
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

            if (!input || !preview || !img) {
                return;
            }

            input.addEventListener('change', function () {
                const file = input.files && input.files[0];

                if (!file) {
                    return;
                }

                img.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            });
        });
    </script>
    @endpush
@endonce
