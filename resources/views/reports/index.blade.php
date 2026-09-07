@extends('layouts.app')

@section('title', 'Reports')
@section('page_title', 'Reports')
@section('page_subtitle', 'Daily, monthly & category summaries')

@section('content')
<div class="panel mb-3 no-print">
    <div class="panel-body">
        @include('partials.date-filters', [
            'action' => route('reports.index'),
            'presets' => $presets,
            'range' => $range,
            'from' => $from,
            'to' => $to,
        ])
    </div>
</div>

<div class="mb-3">
    @include('partials.totals')
</div>

<div class="d-flex gap-2 mb-3 no-print">
    <a href="{{ route('roznamcha.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-printer"></i> Print Roznamcha
    </a>
    <a href="{{ route('roznamcha.export.csv', request()->query()) }}" class="btn btn-sm btn-outline-secondary">CSV Export</a>
    <a href="{{ route('roznamcha.export.excel', request()->query()) }}" class="btn btn-sm btn-outline-secondary">Excel Export</a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-header"><strong>Daily Summary</strong></div>
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Aamdan</th>
                            <th class="text-end">Kharcha</th>
                            <th class="text-end">Balance</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($daily as $row)
                            @php $dayBalance = $row->income_total - $row->expense_total; @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->entry_date)->format('d M Y') }}</td>
                                <td class="text-end amount-pos">{{ pkr($row->income_total) }}</td>
                                <td class="text-end amount-neg">{{ pkr($row->expense_total) }}</td>
                                <td class="text-end {{ $dayBalance >= 0 ? 'amount-pos' : 'amount-neg' }}">{{ pkr($dayBalance) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($daily->hasPages())
                <div class="panel-body border-top">{{ $daily->links() }}</div>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel mb-3">
            <div class="panel-header"><strong>Monthly Summary (12 months)</strong></div>
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                        <tr>
                            <th>Month</th>
                            <th class="text-end">Aamdan</th>
                            <th class="text-end">Kharcha</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($monthly as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row->month_key)->format('M Y') }}</td>
                                <td class="text-end amount-pos">{{ pkr($row->income_total) }}</td>
                                <td class="text-end amount-neg">{{ pkr($row->expense_total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><strong>Most Common Expense Categories</strong></div>
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Entries</th>
                            <th class="text-end">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($topCategories as $row)
                            <tr>
                                <td>{{ $row->category?->name ?? '—' }}</td>
                                <td class="text-end">{{ $row->cnt }}</td>
                                <td class="text-end amount-neg">{{ pkr($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
