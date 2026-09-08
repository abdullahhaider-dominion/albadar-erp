@extends('layouts.app')

@php
    $isIncome = $type === \App\Models\Entry::TYPE_INCOME;
    $title = $entry ? 'Edit Entry' : ($isIncome ? 'Add Aamdan / Income' : 'Add Kharcha / Expense');
    $defaultMethod = $defaultMethod ?? 'Cash';
@endphp

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'Fast daily Roznamcha entry')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-header">
                <strong>{{ $title }}</strong>
                <a href="{{ route('roznamcha.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ $entry ? route('entries.update', $entry) : route('entries.store') }}" id="entryForm">
                    @csrf
                    @if($entry) @method('PUT') @endif
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="entry_date" class="form-control form-control-lg" required
                                   value="{{ old('entry_date', optional($entry)->entry_date?->toDateString() ?? now()->toDateString()) }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Amount (PKR)</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-lg amount-input" required autofocus
                                   value="{{ old('amount', optional($entry)->amount) }}" placeholder="e.g. 50000">
                        </div>

                        @unless($isIncome)
                            <div class="col-12 col-md-6">
                                <label class="form-label">Expense Category</label>
                                <select name="expense_category_id" class="form-select form-select-lg" required>
                                    <option value="">Select category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected((string)old('expense_category_id', optional($entry)->expense_category_id) === (string)$cat->id)>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endunless

                        <div class="col-12 col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-lg" required>
                                @foreach($methods as $method)
                                    <option value="{{ $method }}" @selected(old('payment_method', optional($entry)->payment_method ?? $defaultMethod) === $method)>
                                        {{ $method }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ $isIncome ? 'Received From' : 'Paid To' }}</label>
                            <input type="text" name="party_name" class="form-control form-control-lg"
                                   value="{{ old('party_name', optional($entry)->party_name) }}"
                                   placeholder="{{ $isIncome ? 'Customer / person name' : 'Vendor / person name' }}">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control form-control-lg"
                                   value="{{ old('reference', optional($entry)->reference) }}"
                                   placeholder="Cheque no / txn id (optional)">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Details / Notes (Tafaseel)</label>
                            <textarea name="details" rows="3" class="form-control" placeholder="Short description">{{ old('details', optional($entry)->details) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button class="btn btn-accent btn-lg px-4" type="{{ $entry ? 'button' : 'submit' }}" @if($entry) id="openEditConfirm" @endif>
                            {{ $entry ? 'Update Entry' : 'Save Entry' }}
                        </button>
                        <a href="{{ route('roznamcha.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        @if($entry && auth()->user()->isAdmin() && $entry->auditLogs->isNotEmpty())
            <div class="panel mt-3">
                <div class="panel-header">
                    <strong>Entry History</strong>
                    @include('partials.edited-badge', ['entry' => $entry])
                </div>
                <div class="panel-body">
                    @foreach($entry->auditLogs as $log)
                        <div class="small mb-2">
                            <span class="badge text-bg-light text-capitalize">{{ $log->action }}</span>
                            {{ $log->performed_by_name }} · {{ $log->performed_at?->format('d M Y h:i A') }}
                            @if($log->reason)
                                <div class="text-muted">Reason: {{ $log->reason }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@if($entry)
<div class="modal fade" id="editConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Previous values will be kept in the Audit Log.</p>
                <label class="form-label">Reason for change (recommended)</label>
                <textarea id="editReasonField" class="form-control" rows="3" maxlength="1000" placeholder="Why is this entry being changed?">{{ old('reason') }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-accent" id="confirmEditBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if($entry)
@push('scripts')
<script>
    const form = document.getElementById('entryForm');
    const openBtn = document.getElementById('openEditConfirm');
    const confirmBtn = document.getElementById('confirmEditBtn');
    const modal = document.getElementById('editConfirmModal');
    openBtn?.addEventListener('click', () => bootstrap.Modal.getOrCreateInstance(modal).show());
    confirmBtn?.addEventListener('click', () => {
        let reason = form.querySelector('input[name="reason"]');
        if (!reason) {
            reason = document.createElement('input');
            reason.type = 'hidden';
            reason.name = 'reason';
            form.appendChild(reason);
        }
        reason.value = document.getElementById('editReasonField').value;
        form.submit();
    });
</script>
@endpush
@endif
