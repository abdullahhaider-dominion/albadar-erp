@extends('layouts.app')

@section('title', 'Roznamcha')
@section('page_title', 'Roznamcha')
@section('page_subtitle', 'Daily income & expense ledger')

@section('content')
<div class="panel mb-3 no-print">
    <div class="panel-body">
        @include('partials.date-filters', [
            'action' => route('roznamcha.index'),
            'presets' => $presets,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'showExtra' => true,
            'categories' => $categories,
            'methods' => $methods,
            'users' => $users,
        ])
    </div>
</div>

<div class="mb-3">
    @include('partials.totals')
</div>

<div class="panel">
    <div class="panel-header">
        <strong>Entries</strong>
        <div class="d-flex flex-wrap gap-2 no-print">
            <a href="{{ route('entries.income.create') }}" class="btn btn-sm btn-success">+ Aamdan</a>
            <a href="{{ route('entries.expense.create') }}" class="btn btn-sm btn-danger">+ Kharcha</a>
            <a href="{{ route('roznamcha.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-printer"></i> Print
            </a>
            <a href="{{ route('roznamcha.export.csv', request()->query()) }}" class="btn btn-sm btn-outline-secondary">CSV</a>
            <a href="{{ route('roznamcha.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-secondary">Excel</a>
        </div>
    </div>
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-roznamcha mb-0 align-middle">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Payment Method</th>
                    <th>Paid To / Received From</th>
                    <th>Details</th>
                    <th>User</th>
                    <th class="text-end">Amount</th>
                    <th class="no-print"></th>
                </tr>
                </thead>
                <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>{{ $entry->entry_date->format('d M Y') }}</td>
                        <td>
                            @if($entry->isIncome())
                                <span class="badge badge-income">Income</span>
                            @else
                                <span class="badge badge-expense">Expense</span>
                            @endif
                        </td>
                        <td>{{ $entry->isExpense() ? ($entry->category?->name ?? '—') : 'Aamdan' }}</td>
                        <td>{{ $entry->payment_method }}</td>
                        <td>{{ $entry->party_name ?: '—' }}</td>
                        <td>
                            <div>{{ \Illuminate\Support\Str::limit($entry->details, 50) }}</div>
                            @if($entry->reference)
                                <div class="small text-muted">Ref: {{ $entry->reference }}</div>
                            @endif
                        </td>
                        <td>{{ $entry->creator?->name }}</td>
                        <td class="text-end {{ $entry->isIncome() ? 'amount-pos' : 'amount-neg' }}">{{ pkr($entry->amount) }}</td>
                        <td class="text-end no-print text-nowrap">
                            @if(auth()->user()->isAdmin() || \App\Models\Setting::allowEditorEdit())
                                <a href="{{ route('entries.edit', $entry) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @endif
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('entries.destroy', $entry) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No entries found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($entries->hasPages())
        <div class="panel-body border-top">{{ $entries->links() }}</div>
    @endif
</div>

<div class="mt-3">
    @include('partials.totals')
</div>
@endsection
