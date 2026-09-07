@extends('layouts.app')

@php
    $isIncome = $type === \App\Models\Entry::TYPE_INCOME;
    $title = $entry ? 'Edit Entry' : ($isIncome ? 'Add Aamdan / Income' : 'Add Kharcha / Expense');
@endphp

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'Fast daily entry')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-header">
                <strong>{{ $title }}</strong>
                <a href="{{ route('roznamcha.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ $entry ? route('entries.update', $entry) : route('entries.store') }}">
                    @csrf
                    @if($entry) @method('PUT') @endif
                    <input type="hidden" name="type" value="{{ $type }}">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" name="entry_date" class="form-control form-control-lg" required
                                   value="{{ old('entry_date', optional($entry)->entry_date?->toDateString() ?? now()->toDateString()) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount (PKR)</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-lg" required autofocus
                                   value="{{ old('amount', optional($entry)->amount) }}" placeholder="e.g. 50000">
                        </div>

                        @unless($isIncome)
                            <div class="col-md-6">
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

                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-lg" required>
                                @foreach($methods as $method)
                                    <option value="{{ $method }}" @selected(old('payment_method', optional($entry)->payment_method ?? 'Cash') === $method)>
                                        {{ $method }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ $isIncome ? 'Received From' : 'Paid To' }}</label>
                            <input type="text" name="party_name" class="form-control form-control-lg"
                                   value="{{ old('party_name', optional($entry)->party_name) }}"
                                   placeholder="{{ $isIncome ? 'Customer / person name' : 'Vendor / person name' }}">
                        </div>

                        <div class="col-md-6">
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

                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-accent btn-lg px-4">{{ $entry ? 'Update Entry' : 'Save Entry' }}</button>
                        <a href="{{ route('roznamcha.index') }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
