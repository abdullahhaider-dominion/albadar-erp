@extends('layouts.app')

@section('title', 'Audit Log')
@section('page_title', 'Audit Log')
@section('page_subtitle', 'Entry history and admin activity')

@section('content')
<div class="panel mb-3">
    <div class="panel-body">
        <form method="GET" action="{{ route('audit.index') }}" class="filters-bar row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-1">Date Range</label>
                <select name="range" class="form-select js-range-select">
                    @foreach($presets as $key => $label)
                        <option value="{{ $key }}" @selected($range === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 custom-dates" style="{{ $range === 'custom' ? '' : 'display:none' }}">
                <label class="form-label small mb-1">From</label>
                <input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control">
            </div>
            <div class="col-6 col-md-2 custom-dates" style="{{ $range === 'custom' ? '' : 'display:none' }}">
                <label class="form-label small mb-1">To</label>
                <input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Action</label>
                <select name="action" class="form-select">
                    <option value="">All</option>
                    <option value="edited" @selected(request('action') === 'edited')>Edited</option>
                    <option value="deleted" @selected(request('action') === 'deleted')>Deleted</option>
                    <option value="created" @selected(request('action') === 'created')>Created</option>
                    <option value="restored" @selected(request('action') === 'restored')>Restored</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Type</label>
                <select name="entry_type" class="form-select">
                    <option value="">All</option>
                    <option value="income" @selected(request('entry_type') === 'income')>Aamdan</option>
                    <option value="expense" @selected(request('entry_type') === 'expense')>Kharcha</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected((string)request('user_id') === (string)$u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-1">Entry ID</label>
                <input type="number" name="entry_id" value="{{ request('entry_id') }}" class="form-control" placeholder="#">
            </div>
            <div class="col-12 col-md-auto">
                <button class="btn btn-accent w-100">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="panel mb-3">
    <div class="panel-header"><strong>Entry History</strong></div>
    <div class="panel-body p-0">
        <div class="table-responsive table-scroll">
            <table class="table table-hover table-sticky mb-0 align-middle">
                <thead>
                <tr>
                    <th>Date / Time</th>
                    <th>Entry ID</th>
                    <th>Type</th>
                    <th>Action</th>
                    <th>User</th>
                    <th class="text-end">Amount</th>
                    <th>Reason</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->performed_at?->format('d M Y h:i A') }}</td>
                        <td>#{{ $log->entry_id }}</td>
                        <td>{{ $log->entry_type === 'income' ? 'Aamdan' : 'Kharcha' }}</td>
                        <td>
                            <span class="badge text-capitalize {{ $log->action === 'deleted' ? 'badge-expense' : ($log->action === 'edited' ? 'badge-edited' : 'badge-income') }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td title="{{ $log->ip_address }}">{{ $log->performed_by_name }}</td>
                        <td class="text-end text-nowrap">{{ pkr($log->amount()) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($log->reason, 40) ?: '—' }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-audit-url="{{ route('audit.show', $log) }}">View Changes</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No audit records for this filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="panel-body border-top">{{ $logs->links() }}</div>
    @endif
</div>

<div class="panel mb-3">
    <div class="panel-header"><strong>Deleted Entries (Restore)</strong></div>
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th>Deleted At</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Party</th>
                    <th class="text-end">Amount</th>
                    <th>Deleted By</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($deletedEntries as $entry)
                    <tr>
                        <td>{{ $entry->deleted_at?->format('d M Y h:i A') }}</td>
                        <td>{{ $entry->entry_date->format('d M Y') }}</td>
                        <td>{{ $entry->isIncome() ? 'Income' : 'Expense' }}</td>
                        <td>{{ $entry->party_name ?: '—' }}</td>
                        <td class="text-end">{{ pkr($entry->amount) }}</td>
                        <td>{{ $entry->deleter?->name ?? '—' }}</td>
                        <td class="text-end">
            <form method="POST" action="{{ route('entries.restore', $entry->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-success">Restore Entry</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">No deleted entries.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><strong>Other Admin Activity</strong></div>
    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                <tr>
                    <th>When</th>
                    <th>Action</th>
                    <th>User</th>
                    <th>Details</th>
                </tr>
                </thead>
                <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td class="text-nowrap">{{ $activity->performed_at?->format('d M Y h:i A') }}</td>
                        <td>{{ str_replace('_', ' ', $activity->action) }}</td>
                        <td>{{ $activity->performed_by_name }}</td>
                        <td class="small text-muted">{{ collect($activity->data_json ?? [])->map(fn ($v, $k) => $k.': '.$v)->join(', ') ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No other activity yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
