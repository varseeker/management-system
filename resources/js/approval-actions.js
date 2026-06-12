function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

const APPROVAL_CONFIG = {
    approve: {
        title: 'Konfirmasi Persetujuan',
        submitLabel: 'Setujui',
        submitClass: 'btn-success',
        modalClass: 'approval-modal-content--approve',
        iconClass: 'bi-check-lg',
        noteLabel: 'Catatan Persetujuan',
        notePlaceholder: 'Contoh: Disetujui untuk keperluan operasional harian.',
        noteHint: 'Wajib diisi sebelum mengonfirmasi.',
        confirmQuestion: 'Setujui pengajuan peminjaman berikut?',
    },
    reject: {
        title: 'Konfirmasi Penolakan',
        submitLabel: 'Tolak',
        submitClass: 'btn-danger',
        modalClass: 'approval-modal-content--reject',
        iconClass: 'bi-x-lg',
        noteLabel: 'Alasan Penolakan',
        notePlaceholder: 'Contoh: Stok tidak mencukupi / jangka waktu tidak sesuai.',
        noteHint: 'Wajib diisi sebelum mengonfirmasi.',
        confirmQuestion: 'Tolak pengajuan peminjaman berikut?',
    },
};

function summaryItem(label, value, extraClass = '') {
    return `
        <div class="approval-summary-item ${extraClass}">
            <span class="approval-summary-label">${escapeHtml(label)}</span>
            <span class="approval-summary-value">${value}</span>
        </div>
    `;
}

function buildSummaryHtml(data) {
    const stockBadge =
        data.stockSufficient === '1'
            ? `<span class="badge bg-success-subtle text-success border border-success-subtle">${escapeHtml(data.stock)}</span>`
            : `<span class="badge bg-danger-subtle text-danger border border-danger-subtle">${escapeHtml(data.stock)} kurang</span>`;

    const photoBlock = data.photoUrl
        ? `<a href="${escapeHtml(data.photoUrl)}" target="_blank" rel="noopener" class="approval-summary-photo" title="Lihat foto pengajuan">
                <img src="${escapeHtml(data.photoUrl)}" alt="Foto pengajuan" loading="lazy" decoding="async">
                <span class="approval-summary-photo__zoom"><i class="bi bi-zoom-in"></i></span>
           </a>`
        : `<div class="approval-summary-photo approval-summary-photo--empty" aria-hidden="true">
                <i class="bi bi-image"></i>
           </div>`;

    const descriptionBlock = data.description
        ? summaryItem(
              'Deskripsi',
              `<span class="approval-summary-desc" title="${escapeHtml(data.description)}">${escapeHtml(data.description)}</span>`,
              'approval-summary-item--full',
          )
        : '';

    return `
        <div class="approval-summary-card">
            <div class="approval-summary-layout">
                ${photoBlock}
                <div class="approval-summary-grid">
                    ${summaryItem('Peminjam', escapeHtml(data.borrower))}
                    ${summaryItem('Barang', escapeHtml(data.item))}
                    ${summaryItem('Jumlah', `${escapeHtml(data.quantity)} unit`)}
                    ${summaryItem('Stok', stockBadge)}
                    ${summaryItem('Tgl. Pinjam', escapeHtml(data.borrowDate))}
                    ${summaryItem('Rencana Kembali', escapeHtml(data.returnDate))}
                    ${descriptionBlock}
                </div>
            </div>
        </div>
    `;
}

export function initApprovalActions() {
    const modal = document.getElementById('approvalActionModal');
    const modalContent = modal?.querySelector('.approval-modal-content');

    if (!modal || !modalContent) {
        return;
    }

    const form = document.getElementById('approvalActionForm');
    const summary = document.getElementById('approvalModalSummary');
    const title = document.getElementById('approvalActionModalLabel');
    const subtitle = document.getElementById('approvalModalSubtitle');
    const icon = document.getElementById('approvalModalIcon');
    const note = document.getElementById('approvalModalNote');
    const noteLabel = document.getElementById('approvalModalNoteLabel');
    const noteHint = document.getElementById('approvalModalNoteHint');
    const noteError = document.getElementById('approvalModalNoteError');
    const submitBtn = document.getElementById('approvalModalSubmit');

    if (!form || !summary || !title || !note || !submitBtn) {
        return;
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.js-approval-open');

        if (!trigger) {
            return;
        }

        const action = trigger.dataset.action;
        const config = APPROVAL_CONFIG[action];

        if (!config) {
            return;
        }

        form.action = trigger.dataset.url ?? '';
        form.dataset.action = action;

        modalContent.classList.remove('approval-modal-content--approve', 'approval-modal-content--reject');
        modalContent.classList.add(config.modalClass);

        title.textContent = config.title;

        if (subtitle) {
            subtitle.textContent = config.confirmQuestion;
        }

        if (icon) {
            icon.innerHTML = `<i class="bi ${config.iconClass}"></i>`;
        }

        noteLabel.textContent = config.noteLabel;
        note.placeholder = config.notePlaceholder;
        noteHint.textContent = config.noteHint;
        note.value = '';
        note.classList.remove('is-invalid');
        noteError.textContent = '';

        submitBtn.innerHTML = `<i class="bi ${config.iconClass} me-1"></i> ${config.submitLabel}`;
        submitBtn.className = `btn btn-sm ${config.submitClass}`;

        summary.innerHTML = buildSummaryHtml({
            borrower: trigger.dataset.borrower ?? '-',
            item: trigger.dataset.item ?? '-',
            quantity: trigger.dataset.quantity ?? '-',
            borrowDate: trigger.dataset.borrowDate ?? '-',
            returnDate: trigger.dataset.returnDate ?? '-',
            stock: trigger.dataset.stock ?? '0',
            stockSufficient: trigger.dataset.stockSufficient ?? '0',
            description: trigger.dataset.description ?? '',
            photoUrl: trigger.dataset.photoUrl ?? '',
        });

        if (window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
            window.setTimeout(() => note.focus(), 200);
        }
    });

    form.addEventListener('submit', (event) => {
        const trimmed = note.value.trim();

        if (!trimmed) {
            event.preventDefault();
            note.classList.add('is-invalid');
            noteError.textContent =
                form.dataset.action === 'reject'
                    ? 'Alasan penolakan wajib diisi.'
                    : 'Catatan persetujuan wajib diisi.';
            note.focus();
            return;
        }

        note.classList.remove('is-invalid');
        noteError.textContent = '';
        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
    });

    modal.addEventListener('hidden.bs.modal', () => {
        const action = form.dataset.action || 'approve';

        form.reset();
        form.action = '';
        form.dataset.action = '';
        note.classList.remove('is-invalid');
        noteError.textContent = '';
        submitBtn.disabled = false;

        modalContent.classList.remove('approval-modal-content--approve', 'approval-modal-content--reject');

        const config = APPROVAL_CONFIG[action] ?? APPROVAL_CONFIG.approve;
        submitBtn.innerHTML = `<i class="bi ${config.iconClass} me-1"></i> ${config.submitLabel}`;
        submitBtn.className = `btn btn-sm ${config.submitClass}`;
    });
}
