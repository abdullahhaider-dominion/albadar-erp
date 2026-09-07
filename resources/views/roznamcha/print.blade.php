<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Roznamcha Print — {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; color: #111; }
        h1 { font-size: 1.4rem; margin-bottom: .25rem; }
        .meta { color: #555; margin-bottom: 1rem; }
        table { font-size: .9rem; }
        .totals { margin-top: 1rem; border-top: 2px solid #111; padding-top: .75rem; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="p-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1>{{ \App\Models\Setting::companyName() }} — Roznamcha</h1>
            <div class="meta">
                Period: {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
                · Printed: {{ now()->format('d M Y h:i A') }}
            </div>
        </div>
        <button class="btn btn-dark no-print" onclick="window.print()">Print</button>
    </div>

    <div class="totals row mb-3">
        <div class="col-4"><strong>Total Aamdan:</strong> {{ pkr($income) }}</div>
        <div class="col-4"><strong>Total Kharcha:</strong> {{ pkr($expense) }}</div>
        <div class="col-4"><strong>Net Balance:</strong> {{ pkr($balance) }}</div>
    </div>

    <table class="table table-sm table-bordered">
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
        @foreach($entries as $entry)
            <tr>
                <td>{{ $entry->entry_date->format('d M Y') }}</td>
                <td>{{ $entry->isIncome() ? 'Income' : 'Expense' }}</td>
                <td>{{ $entry->isExpense() ? ($entry->category?->name ?? '') : 'Aamdan' }}</td>
                <td>{{ $entry->payment_method }}</td>
                <td>{{ $entry->party_name }}</td>
                <td>{{ $entry->details }}@if($entry->reference) (Ref: {{ $entry->reference }})@endif</td>
                <td>{{ $entry->creator?->name }}</td>
                <td class="text-end">{{ pkr($entry->amount) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals row">
        <div class="col-4"><strong>Total Aamdan:</strong> {{ pkr($income) }}</div>
        <div class="col-4"><strong>Total Kharcha:</strong> {{ pkr($expense) }}</div>
        <div class="col-4"><strong>Net Balance:</strong> {{ pkr($balance) }}</div>
    </div>

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
