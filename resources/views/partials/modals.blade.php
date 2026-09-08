<div class="modal fade" id="deleteEntryModal" tabindex="-1" aria-labelledby="deleteEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" id="deleteEntryForm">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEntryModalLabel">Delete Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">The entry will be removed from reports. Full history stays in the Audit Log and can be restored.</p>
                    <label class="form-label">Reason for deletion <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required minlength="3" placeholder="Why is this entry being deleted?"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger">Delete Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Entry History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="historyModalBody">
                <p class="text-muted mb-0">Loading…</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const deleteModalEl = document.getElementById('deleteEntryModal');
    if (deleteModalEl) {
        const deleteForm = document.getElementById('deleteEntryForm');
        document.querySelectorAll('[data-delete-url]').forEach((btn) => {
            btn.addEventListener('click', () => {
                deleteForm.action = btn.dataset.deleteUrl;
                deleteForm.querySelector('[name="reason"]').value = '';
                bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
            });
        });
    }

    const historyModalEl = document.getElementById('historyModal');
    const fieldLabels = {
        entry_date: 'Date',
        type: 'Type',
        amount: 'Amount',
        category_name: 'Category',
        payment_method: 'Payment Method',
        party_name: 'Paid To / Received From',
        reference: 'Reference',
        details: 'Details',
    };

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderSnapshot(title, data) {
        if (!data) {
            return `<div class="col-md-6"><h6 class="fw-semibold">${escapeHtml(title)}</h6><p class="text-muted">No data</p></div>`;
        }
        const rows = Object.keys(fieldLabels).map((key) => {
            let value = data[key] ?? '—';
            if (key === 'amount' && value && value !== '—') {
                value = 'PKR ' + Number(value).toLocaleString();
            }
            return `<div class="small mb-2"><div class="text-muted">${escapeHtml(fieldLabels[key])}</div><div>${escapeHtml(value || '—')}</div></div>`;
        }).join('');
        return `<div class="col-md-6"><h6 class="fw-semibold">${escapeHtml(title)}</h6>${rows}</div>`;
    }

    function renderLogs(payload) {
        const logs = payload.logs || [payload];
        if (!logs.length) {
            return '<p class="text-muted mb-0">No history found.</p>';
        }
        return logs.map((log) => {
            const action = (log.action || '').charAt(0).toUpperCase() + (log.action || '').slice(1);
            const reason = log.reason
                ? `<p class="small mb-3"><span class="text-muted">Reason:</span> ${escapeHtml(log.reason)}</p>`
                : '';
            return `
                <div class="history-block mb-4">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <strong>${escapeHtml(action)}</strong>
                        <span class="text-muted small">${escapeHtml(log.performed_by || '')} · ${escapeHtml(log.performed_at || '')}</span>
                    </div>
                    ${reason}
                    <div class="row g-3">${renderSnapshot('Before', log.old)} ${renderSnapshot('After', log.new)}</div>
                </div>`;
        }).join('<hr>');
    }

    async function openHistory(url) {
        const body = document.getElementById('historyModalBody');
        body.innerHTML = '<p class="text-muted mb-0">Loading…</p>';
        bootstrap.Modal.getOrCreateInstance(historyModalEl).show();
        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('Unable to load history');
            const data = await res.json();
            body.innerHTML = renderLogs(data);
        } catch (e) {
            body.innerHTML = '<p class="text-danger mb-0">Unable to load history.</p>';
        }
    }

    document.querySelectorAll('[data-entry-history], [data-audit-url]').forEach((btn) => {
        btn.addEventListener('click', () => openHistory(btn.dataset.entryHistory || btn.dataset.auditUrl));
    });
</script>
@endpush
