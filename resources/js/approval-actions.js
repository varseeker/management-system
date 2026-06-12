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
        submitLabel: 'Ya, Setujui Pengajuan',
        submitClass: 'btn-success',
        noteLabel: 'Catatan Persetujuan',
        notePlaceholder: 'Contoh: Disetujui untuk keperluan operasional harian.',
        noteHint: 'Catatan persetujuan wajib diisi sebelum mengonfirmasi.',
        confirmQuestion: 'Anda akan menyetujui pengajuan peminjaman ini.',
    },
    reject: {
        title: 'Konfirmasi Penolakan',
        submitLabel: 'Ya, Tolak Pengajuan',
        submitClass: 'btn-danger',
        noteLabel: 'Alasan Penolakan',
        notePlaceholder: 'Contoh: Stok tidak mencukupi / jangka waktu tidak sesuai ketentuan.',
        noteHint: 'Alasan penolakan wajib diisi sebelum mengonfirmasi.',
        confirmQuestion: 'Anda akan menolak pengajuan peminjaman ini.',
    },
};

function buildSummaryHtml(data) {
    const stockBadge =
        data.stockSufficient === '1'
            ? `<span class="badge bg-success">${escapeHtml(data.stock)} tersedia</span>`
            : `<span class="badge bg-danger">${escapeHtml(data.stock)} (kurang)</span>`;

    return `
        <p class="text-muted small mb-3">${escapeHtml(data.confirmQuestion)}</p>
        <div class="approval-summary-card">
            <div class="row g-2 small">
                <div class="col-sm-6">
                    <span class="approval-summary-label">Peminjam</span>
                    <div class="fw-semibold">${escapeHtml(data.borrower)}</div>
                </div>
                <div class="col-sm-6">
                    <span class="approval-summary-label">Barang</span>
                    <div class="fw-semibold">${escapeHtml(data.item)}</div>
                </div>
                <div class="col-4">
                    <span class="approval-summary-label">Jumlah</span>
                    <div class="fw-semibold">${escapeHtml(data.quantity)} unit</div>
                </div>
                <div class="col-4">
                    <span class="approval-summary-label">Tanggal Pinjam</span>
                    <div class="fw-semibold">${escapeHtml(data.borrowDate)}</div>
                </div>
                <div class="col-4">
                    <span class="approval-summary-label">Rencana Kembali</span>
                    <div class="fw-semibold">${escapeHtml(data.returnDate)}</div>
                </div>
                <div class="col-12">
                    <span class="approval-summary-label">Stok Tersedia</span>
                    <div>${stockBadge}</div>
                </div>
                ${
                    data.description
                        ? `<div class="col-12">
                            <span class="approval-summary-label">Deskripsi</span>
                            <div>${escapeHtml(data.description)}</div>
                           </div>`
                        : ''
                }
            </div>
        </div>
    `;
}

export function initApprovalActions() {
    const modal = document.getElementById('approvalActionModal');

    if (!modal) {
        return;
    }

    const form = document.getElementById('approvalActionForm');
    const summary = document.getElementById('approvalModalSummary');
    const title = document.getElementById('approvalActionModalLabel');
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

        title.textContent = config.title;
        noteLabel.textContent = config.noteLabel;
        note.placeholder = config.notePlaceholder;
        noteHint.textContent = config.noteHint;
        note.value = '';
        note.classList.remove('is-invalid');
        noteError.textContent = '';

        submitBtn.textContent = config.submitLabel;
        submitBtn.className = `btn ${config.submitClass}`;

        summary.innerHTML = buildSummaryHtml({
            confirmQuestion: config.confirmQuestion,
            borrower: trigger.dataset.borrower ?? '-',
            item: trigger.dataset.item ?? '-',
            quantity: trigger.dataset.quantity ?? '-',
            borrowDate: trigger.dataset.borrowDate ?? '-',
            returnDate: trigger.dataset.returnDate ?? '-',
            stock: trigger.dataset.stock ?? '0',
            stockSufficient: trigger.dataset.stockSufficient ?? '0',
            description: trigger.dataset.description ?? '',
        });

        if (window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
            window.setTimeout(() => note.focus(), 250);
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
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
    });

    modal.addEventListener('hidden.bs.modal', () => {
        form.reset();
        form.action = '';
        note.classList.remove('is-invalid');
        noteError.textContent = '';
        submitBtn.disabled = false;

        const action = form.dataset.action;
        const config = APPROVAL_CONFIG[action] ?? APPROVAL_CONFIG.approve;
        submitBtn.textContent = config.submitLabel;
        submitBtn.className = `btn ${config.submitClass}`;
    });
}
