<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\ExpenseCategory;
use App\Support\DateRange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to, $range] = DateRange::fromRequest($request, 'today');

        $base = Entry::query()
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString());

        $income = (clone $base)->income()->sum('amount');
        $expense = (clone $base)->expense()->sum('amount');
        $balance = $income - $expense;

        $recent = Entry::query()
            ->with(['category', 'creator'])
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $chartStart = Carbon::today()->subDays(13);
        $daily = Entry::query()
            ->selectRaw('entry_date, type, SUM(amount) as total')
            ->whereDate('entry_date', '>=', $chartStart->toDateString())
            ->groupBy('entry_date', 'type')
            ->orderBy('entry_date')
            ->get();

        $labels = [];
        $incomeSeries = [];
        $expenseSeries = [];

        for ($i = 0; $i < 14; $i++) {
            $day = $chartStart->copy()->addDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('d M');
            $incomeSeries[] = (float) $daily->first(fn ($r) => $r->entry_date->toDateString() === $key && $r->type === Entry::TYPE_INCOME)?->total;
            $expenseSeries[] = (float) $daily->first(fn ($r) => $r->entry_date->toDateString() === $key && $r->type === Entry::TYPE_EXPENSE)?->total;
        }

        $topCategories = Entry::query()
            ->expense()
            ->whereDate('entry_date', '>=', $from->toDateString())
            ->whereDate('entry_date', '<=', $to->toDateString())
            ->whereNotNull('expense_category_id')
            ->select('expense_category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->with('category')
            ->limit(5)
            ->get();

        $monthStart = Carbon::today()->startOfMonth();
        $monthlyIncome = Entry::query()->income()->whereDate('entry_date', '>=', $monthStart)->sum('amount');
        $monthlyExpense = Entry::query()->expense()->whereDate('entry_date', '>=', $monthStart)->sum('amount');

        return view('dashboard', [
            'income' => $income,
            'expense' => $expense,
            'balance' => $balance,
            'recent' => $recent,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'presets' => DateRange::presets(),
            'chartLabels' => $labels,
            'chartIncome' => $incomeSeries,
            'chartExpense' => $expenseSeries,
            'topCategories' => $topCategories,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'categories' => ExpenseCategory::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }
}
