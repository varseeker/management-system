@props([
    'inputId',
    'inputName',
    'label' => 'Unggah foto',
    'hint' => 'JPG, PNG, atau WEBP · maks. 5 MB',
    'required' => true,
    'errorKey' => null,
])

@php
    $errorKey = $errorKey ?? $inputName;
@endphp

<div class="borrowing-image-upload js-borrowing-image-upload @error($errorKey) is-invalid @enderror">
    <input type="file"
        id="{{ $inputId }}"
        name="{{ $inputName }}"
        class="borrowing-image-upload__input @error($errorKey) is-invalid @enderror"
        accept="image/jpeg,image/jpg,image/png,image/webp"
        @if($required) required @endif>

    <div class="borrowing-image-upload__dropzone js-upload-dropzone">
        <div class="borrowing-image-upload__placeholder js-upload-placeholder">
            <span class="borrowing-image-upload__icon" aria-hidden="true">
                <i class="bi bi-cloud-arrow-up"></i>
            </span>
            <span class="borrowing-image-upload__title">{{ $label }}</span>
            <span class="borrowing-image-upload__hint">{{ $hint }}</span>
            <span class="borrowing-image-upload__action btn btn-sm btn-outline-primary mt-2">
                <i class="bi bi-folder2-open"></i> Pilih berkas
            </span>
        </div>

        <div class="borrowing-image-upload__preview js-upload-preview d-none">
            <div class="borrowing-image-upload__preview-frame">
                <img alt="Pratinjau foto" class="borrowing-image-upload__preview-img">
            </div>
            <div class="borrowing-image-upload__preview-meta">
                <span class="borrowing-image-upload__filename js-upload-filename text-truncate"></span>
                <button type="button" class="btn btn-sm btn-outline-danger js-upload-remove">
                    <i class="bi bi-trash"></i> Ganti foto
                </button>
            </div>
        </div>
    </div>
</div>

@error($errorKey)
<div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
