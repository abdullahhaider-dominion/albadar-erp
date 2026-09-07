<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\ExpenseCategory;
use App\Support\DateRange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to, $range] = DateRange::fromRequest($request, '30days');

        $base = Entry::query()
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString());

        $income = (clone $base)->income()->sum('amount');
        $expense = (clone $base)->expense()->sum('amount');

        $daily = Entry::query()
            ->selectRaw("entry_date, SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_total, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_total")
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->groupBy('entry_date')
            ->orderByDesc('entry_date')
            ->paginate(31)
            ->withQueryString();

        $monthExpr = config('database.default') === 'sqlite'
            ? "strftime('%Y-%m', entry_date)"
            : "DATE_FORMAT(entry_date, '%Y-%m')";

        $monthly = Entry::query()
            ->selectRaw("{$monthExpr} as month_key, SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_total, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_total")
            ->whereDate('entry_date', '>=', Carbon::today()->subMonths(11)->startOfMonth()->toDateString())
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->get();

        $topCategories = Entry::query()
            ->expense()
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->whereNotNull('expense_category_id')
            ->select('expense_category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->with('category')
            ->limit(10)
            ->get();

        return view('reports.index', [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'daily' => $daily,
            'monthly' => $monthly,
            'topCategories' => $topCategories,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'presets' => DateRange::presets(),
            'categories' => ExpenseCategory::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }
}
