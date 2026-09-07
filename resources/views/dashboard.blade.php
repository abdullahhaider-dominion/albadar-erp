@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Aamdan, Kharcha & Net Balance overview')

@section('content')
<div class="panel mb-3 no-print">
    <div class="panel-body">
        @include('partials.date-filters', ['action' => route('dashboard'), 'presets' => $presets, 'range' => $range, 'from' => $from, 'to' => $to])
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card income">
            <div class="label">Total Aamdan / Income</div>
            <div class="value">{{ pkr($income) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card expense">
            <div class="label">Total Kharcha / Expense</div>
            <div class="value">{{ pkr($expense) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card balance">
            <div class="label">Net Balance</div>
            <div class="value">{{ pkr($balance) }}</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="panel h-100">
            <div class="panel-header">
                <strong>Income vs Expense (Last 14 Days)</strong>
            </div>
            <div class="panel-body">
                <canvas id="incomeExpenseChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel h-100">
            <div class="panel-header"><strong>This Month</strong></div>
            <div class="panel-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Aamdan</span>
                    <span class="amount-pos">{{ pkr($monthlyIncome) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Kharcha</span>
                    <span class="amount-neg">{{ pkr($monthlyExpense) }}</span>
                </div>
                <hr>
                <div class="mb-2 fw-semibold">Top Expense Categories</div>
                @forelse($topCategories as $row)
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $row->category?->name ?? '—' }}</span>
                        <span class="amount-neg">{{ pkr($row->total) }}</span>
                    </div>
                @empty
                    <div class="text-muted small">No expense data for this range.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <strong>Recent Roznamcha Entries</strong>
        <div class="d-flex gap-2 no-print">
            <a href="{{ route('entries.income.create') }}" class="btn btn-sm btn-success">+ Aamdan</a>
            <a href="{{ route('entries.expense.create') }}" class="btn btn-sm btn-danger">+ Kharcha</a>
            <a href="{{ route('roznamcha.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
        </div>
    </div>
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-roznamcha mb-0">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Payment</th>
                    <th>Party</th>
                    <th>Details</th>
                    <th>User</th>
                    <th class="text-end">Amount</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recent as $entry)
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
                        <td>{{ \Illuminate\Support\Str::limit($entry->details, 40) }}</td>
                        <td>{{ $entry->creator?->name }}</td>
                        <td class="text-end {{ $entry->isIncome() ? 'amount-pos' : 'amount-neg' }}">{{ pkr($entry->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No entries for this period. Add your first Aamdan or Kharcha.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('incomeExpenseChart'), {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [
            {
                label: 'Aamdan',
                data: @json($chartIncome),
                backgroundColor: 'rgba(5, 150, 105, .75)',
                borderRadius: 4,
            },
            {
                label: 'Kharcha',
                data: @json($chartExpense),
                backgroundColor: 'rgba(220, 38, 38, .7)',
                borderRadius: 4,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: {
                ticks: {
                    callback: (v) => 'PKR ' + Number(v).toLocaleString()
                }
            }
        }
    }
});
</script>
@endpush
