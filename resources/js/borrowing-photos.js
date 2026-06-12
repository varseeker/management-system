export function initBorrowingPhotos() {
    initPhotoLightbox();
    initImageUploads();
}

function initPhotoLightbox() {
    const modal = document.getElementById('borrowingPhotoModal');

    if (!modal) {
        return;
    }

    const img = modal.querySelector('#borrowingPhotoModalImg');
    const title = modal.querySelector('#borrowingPhotoModalLabel');

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.js-borrowing-photo-view');

        if (!trigger) {
            return;
        }

        event.preventDefault();

        const url = trigger.dataset.photoUrl;
        const label = trigger.dataset.photoTitle || 'Foto barang';

        if (!url || !img) {
            return;
        }

        img.src = url;
        img.alt = label;

        if (title) {
            title.textContent = label;
        }

        if (window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    });

    modal.addEventListener('hidden.bs.modal', () => {
        if (img) {
            img.removeAttribute('src');
            img.alt = '';
        }
    });
}

function initImageUploads() {
    document.querySelectorAll('.js-borrowing-image-upload').forEach((zone) => {
        const input = zone.querySelector('input[type="file"]');
        const dropzone = zone.querySelector('.js-upload-dropzone');
        const preview = zone.querySelector('.js-upload-preview');
        const previewImg = preview?.querySelector('img');
        const placeholder = zone.querySelector('.js-upload-placeholder');
        const filenameEl = zone.querySelector('.js-upload-filename');
        const removeBtn = zone.querySelector('.js-upload-remove');

        if (!input || !dropzone) {
            return;
        }

        let objectUrl = null;

        const revokeObjectUrl = () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
        };

        const clearPreview = () => {
            revokeObjectUrl();
            input.value = '';
            preview?.classList.add('d-none');
            placeholder?.classList.remove('d-none');
            zone.classList.remove('has-file', 'is-dragover');

            if (previewImg) {
                previewImg.removeAttribute('src');
            }

            if (filenameEl) {
                filenameEl.textContent = '';
            }
        };

        const showPreview = (file) => {
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            revokeObjectUrl();
            objectUrl = URL.createObjectURL(file);
            zone.classList.add('has-file');

            if (previewImg) {
                previewImg.src = objectUrl;
            }

            if (filenameEl) {
                filenameEl.textContent = file.name;
            }

            preview?.classList.remove('d-none');
            placeholder?.classList.add('d-none');
        };

        dropzone.addEventListener('click', (event) => {
            if (event.target.closest('.js-upload-remove')) {
                return;
            }

            if (event.target === input) {
                return;
            }

            input.click();
        });

        input.addEventListener('change', () => {
            const file = input.files?.[0];

            if (file) {
                showPreview(file);
            } else {
                clearPreview();
            }
        });

        removeBtn?.addEventListener('click', (event) => {
            event.stopPropagation();
            clearPreview();
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                zone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                zone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', (event) => {
            const file = event.dataTransfer?.files?.[0];

            if (!file) {
                return;
            }

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            showPreview(file);
        });
    });
}
