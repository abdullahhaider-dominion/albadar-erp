@php
    $currentRange = $range ?? 'today';
    $action = $action ?? url()->current();
@endphp
<form method="GET" action="{{ $action }}" class="filters-bar row g-2 align-items-end">
    <div class="col-md-3">
        <label class="form-label small mb-1">Date Range</label>
        <select name="range" class="form-select" id="rangeSelect">
            @foreach($presets as $key => $label)
                <option value="{{ $key }}" @selected($currentRange === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 custom-dates" style="{{ $currentRange === 'custom' ? '' : 'display:none' }}">
        <label class="form-label small mb-1">From</label>
        <input type="date" name="from" value="{{ isset($from) ? $from->toDateString() : request('from') }}" class="form-control">
    </div>
    <div class="col-md-2 custom-dates" style="{{ $currentRange === 'custom' ? '' : 'display:none' }}">
        <label class="form-label small mb-1">To</label>
        <input type="date" name="to" value="{{ isset($to) ? $to->toDateString() : request('to') }}" class="form-control">
    </div>
    @if(!empty($showExtra))
        <div class="col-md-2">
            <label class="form-label small mb-1">Type</label>
            <select name="type" class="form-select">
                <option value="">All</option>
                <option value="income" @selected(request('type') === 'income')>Income only</option>
                <option value="expense" @selected(request('type') === 'expense')>Expense only</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Category</label>
            <select name="expense_category_id" class="form-select">
                <option value="">All</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string)request('expense_category_id') === (string)$cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Payment</label>
            <select name="payment_method" class="form-select">
                <option value="">All</option>
                @foreach($methods as $method)
                    <option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ $method }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">User</label>
            <select name="user_id" class="form-select">
                <option value="">All</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected((string)request('user_id') === (string)$u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Details, reference, person, amount...">
        </div>
    @endif
    <div class="col-auto">
        <button class="btn btn-accent">Apply</button>
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('rangeSelect')?.addEventListener('change', function () {
        document.querySelectorAll('.custom-dates').forEach(el => {
            el.style.display = this.value === 'custom' ? '' : 'none';
        });
    });
</script>
@endpush
