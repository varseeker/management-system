<div class="modal fade"
    id="approvalActionModal"
    tabindex="-1"
    aria-labelledby="approvalActionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="approvalActionModalLabel">
                    Konfirmasi Tindakan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="approval-modal-summary mb-3" id="approvalModalSummary"></div>

                <form id="approvalActionForm" method="POST" novalidate>
                    @csrf
                    <label class="form-label fw-semibold" for="approvalModalNote" id="approvalModalNoteLabel">
                        Catatan
                    </label>
                    <textarea name="approval_note"
                        id="approvalModalNote"
                        class="form-control"
                        rows="4"
                        maxlength="1000"
                        required
                        placeholder="Tulis catatan..."></textarea>
                    <div class="form-text" id="approvalModalNoteHint"></div>
                    <div class="invalid-feedback d-block" id="approvalModalNoteError"></div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="approvalActionForm" class="btn" id="approvalModalSubmit">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>
