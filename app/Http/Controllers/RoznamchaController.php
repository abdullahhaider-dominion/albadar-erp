<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Support\DateRange;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoznamchaController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to, $range] = DateRange::fromRequest($request, '30days');
        $query = $this->filteredQuery($request, $from, $to);

        $totalsQuery = clone $query;
        $income = (clone $totalsQuery)->income()->sum('amount');
        $expense = (clone $totalsQuery)->expense()->sum('amount');

        $entries = $query
            ->with(['category', 'creator'])
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('roznamcha.index', [
            'entries' => $entries,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'presets' => DateRange::presets(),
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
            'methods' => Entry::PAYMENT_METHODS,
            'users' => User::query()->orderBy('name')->get(),
            'filters' => $request->only(['type', 'expense_category_id', 'payment_method', 'user_id', 'q', 'from', 'to', 'range']),
        ]);
    }

    public function print(Request $request): View
    {
        [$from, $to, $range] = DateRange::fromRequest($request, '30days');
        $query = $this->filteredQuery($request, $from, $to);

        $entries = $query->with(['category', 'creator'])->orderBy('entry_date')->orderBy('id')->get();
        $income = $entries->where('type', Entry::TYPE_INCOME)->sum('amount');
        $expense = $entries->where('type', Entry::TYPE_EXPENSE)->sum('amount');

        return view('roznamcha.print', [
            'entries' => $entries,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'from' => $from,
            'to' => $to,
            'range' => $range,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$from, $to] = DateRange::fromRequest($request, '30days');
        $entries = $this->filteredQuery($request, $from, $to)
            ->with(['category', 'creator'])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $filename = 'roznamcha-'.$from->toDateString().'-to-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Type', 'Category', 'Payment Method', 'Party', 'Reference', 'Details', 'User', 'Amount']);

            foreach ($entries as $entry) {
                fputcsv($out, [
                    $entry->entry_date->format('Y-m-d'),
                    $entry->isIncome() ? 'Income' : 'Expense',
                    $entry->isExpense() ? ($entry->category?->name ?? '') : 'Aamdan',
                    $entry->payment_method,
                    $entry->party_name,
                    $entry->reference,
                    $entry->details,
                    $entry->creator?->name,
                    number_format((float) $entry->amount, 2, '.', ''),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        // SpreadsheetML XML that Excel opens natively — no extra package needed.
        [$from, $to] = DateRange::fromRequest($request, '30days');
        $entries = $this->filteredQuery($request, $from, $to)
            ->with(['category', 'creator'])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $filename = 'roznamcha-'.$from->toDateString().'-to-'.$to->toDateString().'.xls';

        $rows = '';
        foreach ($entries as $entry) {
            $rows .= '<Row>'
                .'<Cell><Data ss:Type="String">'.e($entry->entry_date->format('Y-m-d')).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.e($entry->isIncome() ? 'Income' : 'Expense').'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.e($entry->isExpense() ? ($entry->category?->name ?? '') : 'Aamdan').'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.e($entry->payment_method).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.e($entry->party_name).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.e($entry->reference).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.e($entry->details).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.e($entry->creator?->name).'</Data></Cell>'
                .'<Cell><Data ss:Type="Number">'.number_format((float) $entry->amount, 2, '.', '').'</Data></Cell>'
                .'</Row>';
        }

        $xml = '<?xml version="1.0"?>'
            .'<?mso-application progid="Excel.Sheet"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Worksheet ss:Name="Roznamcha"><Table>'
            .'<Row>'
            .'<Cell><Data ss:Type="String">Date</Data></Cell>'
            .'<Cell><Data ss:Type="String">Type</Data></Cell>'
            .'<Cell><Data ss:Type="String">Category</Data></Cell>'
            .'<Cell><Data ss:Type="String">Payment Method</Data></Cell>'
            .'<Cell><Data ss:Type="String">Party</Data></Cell>'
            .'<Cell><Data ss:Type="String">Reference</Data></Cell>'
            .'<Cell><Data ss:Type="String">Details</Data></Cell>'
            .'<Cell><Data ss:Type="String">User</Data></Cell>'
            .'<Cell><Data ss:Type="String">Amount</Data></Cell>'
            .'</Row>'
            .$rows
            .'</Table></Worksheet></Workbook>';

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function filteredQuery(Request $request, $from, $to)
    {
        $query = Entry::query()
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString());

        if ($request->filled('type') && in_array($request->type, [Entry::TYPE_INCOME, Entry::TYPE_EXPENSE], true)) {
            $query->where('type', $request->type);
        }

        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('user_id')) {
            $query->where('created_by', $request->user_id);
        }

        $query->search($request->input('q'));

        return $query;
    }
}
