<div class="totals-strip row g-2 text-center text-md-start">
    <div class="col-md-4">
        <div class="small text-muted">Total Aamdan / Income</div>
        <div class="fs-5 fw-bold amount-pos">{{ pkr($income) }}</div>
    </div>
    <div class="col-md-4">
        <div class="small text-muted">Total Kharcha / Expense</div>
        <div class="fs-5 fw-bold amount-neg">{{ pkr($expense) }}</div>
    </div>
    <div class="col-md-4">
        <div class="small text-muted">Net Balance</div>
        <div class="fs-5 fw-bold {{ $balance >= 0 ? 'amount-pos' : 'amount-neg' }}">{{ pkr($balance) }}</div>
    </div>
</div>
