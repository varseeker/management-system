<div class="modal fade"
    id="approvalActionModal"
    tabindex="-1"
    aria-labelledby="approvalActionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered approval-modal-dialog">
        <div class="modal-content approval-modal-content">
            <div class="modal-header approval-modal-header border-0 pb-0">
                <div class="d-flex align-items-start gap-2 min-w-0">
                    <span class="approval-modal-icon" id="approvalModalIcon" aria-hidden="true">
                        <i class="bi bi-check-lg"></i>
                    </span>
                    <div class="min-w-0">
                        <h5 class="modal-title fw-bold mb-0" id="approvalActionModalLabel">
                            Konfirmasi Tindakan
                        </h5>
                        <p class="text-muted small mb-0 mt-1" id="approvalModalSubtitle"></p>
                    </div>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body approval-modal-body pt-2">
                <div id="approvalModalSummary"></div>

                <form id="approvalActionForm" method="POST" novalidate class="approval-modal-form">
                    @csrf
                    <label class="form-label fw-semibold small mb-1" for="approvalModalNote" id="approvalModalNoteLabel">
                        Catatan
                    </label>
                    <textarea name="approval_note"
                        id="approvalModalNote"
                        class="form-control form-control-sm"
                        rows="3"
                        maxlength="1000"
                        required
                        placeholder="Tulis catatan..."></textarea>
                    <div class="form-text small" id="approvalModalNoteHint"></div>
                    <div class="invalid-feedback d-block" id="approvalModalNoteError"></div>
                </form>
            </div>
            <div class="modal-footer approval-modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="approvalActionForm" class="btn btn-sm" id="approvalModalSubmit">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>
