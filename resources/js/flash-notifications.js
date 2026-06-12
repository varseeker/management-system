const TOAST_ICONS = {
    success: 'bi-check-circle-fill',
    danger: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill',
};

const TOAST_BG = {
    success: 'text-bg-success',
    danger: 'text-bg-danger',
    warning: 'text-bg-warning',
    info: 'text-bg-info',
};

function parseFlashPayload() {
    const dataEl = document.getElementById('flash-messages-data');
    const payload = { toasts: [], errors: [] };

    if (dataEl) {
        try {
            const parsed = JSON.parse(dataEl.textContent);
            payload.toasts = parsed.toasts ?? [];
            payload.errors = parsed.errors ?? [];
        } catch {
            // ignore invalid JSON
        }
    }

    document.querySelectorAll('.js-extra-flash').forEach((element) => {
        try {
            const extra = JSON.parse(element.textContent);

            if (Array.isArray(extra)) {
                payload.toasts.push(...extra);
            }
        } catch {
            // ignore invalid JSON
        }
    });

    return payload;
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export function showToast({ type = 'info', title = '', message = '' }) {
    const container = document.getElementById('toast-container');

    if (!container || !message) {
        return;
    }

    const toastType = TOAST_BG[type] ? type : 'info';
    const toastId = `toast-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
    const icon = TOAST_ICONS[toastType] ?? TOAST_ICONS.info;

    const toastEl = document.createElement('div');
    toastEl.id = toastId;
    toastEl.className = `toast align-items-center border-0 ${TOAST_BG[toastType]}`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi ${icon} flex-shrink-0 mt-1"></i>
                    <div>
                        ${title ? `<strong class="d-block">${escapeHtml(title)}</strong>` : ''}
                        <span>${escapeHtml(message)}</span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
        </div>
    `;

    container.appendChild(toastEl);

    if (window.bootstrap?.Toast) {
        const toast = window.bootstrap.Toast.getOrCreateInstance(toastEl, {
            autohide: true,
            delay: toastType === 'danger' ? 7000 : 5000,
        });

        toast.show();

        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
}

export function showErrorModal(errors) {
    const modal = document.getElementById('flashErrorModal');
    const list = document.getElementById('flashErrorModalList');

    if (!modal || !list || !errors?.length) {
        return;
    }

    list.innerHTML = errors.map((error) => `<li class="mb-1">${escapeHtml(error)}</li>`).join('');

    if (window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(modal).show();
    }
}

export function initFlashNotifications() {
    const { toasts, errors } = parseFlashPayload();

    toasts.forEach((toast) => showToast(toast));

    if (errors.length === 1 && toasts.length === 0) {
        showToast({
            type: 'danger',
            title: 'Terjadi Kesalahan',
            message: errors[0],
        });
    } else if (errors.length > 1) {
        showErrorModal(errors);
    }
}
