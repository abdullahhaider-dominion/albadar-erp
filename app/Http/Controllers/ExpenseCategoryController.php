<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ExpenseCategory::query()->orderBy('name')->paginate(30);

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name'],
        ]);

        $category = ExpenseCategory::query()->create([
            'name' => $data['name'],
            'active' => true,
        ]);

        AuditLogger::activity('category_created', ExpenseCategory::class, $category->id, [
            'name' => $category->name,
        ], $request);

        return back()->with('success', 'Expense category added.');
    }

    public function update(Request $request, ExpenseCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories', 'name')->ignore($category->id)],
            'active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $data['name'],
            'active' => $request->boolean('active'),
        ]);

        AuditLogger::activity('category_updated', ExpenseCategory::class, $category->id, [
            'name' => $category->name,
            'active' => $category->active,
        ], $request);

        return back()->with('success', 'Expense category updated.');
    }

    public function toggle(ExpenseCategory $category): RedirectResponse
    {
        $category->active = ! $category->active;
        $category->save();

        AuditLogger::activity('category_toggled', ExpenseCategory::class, $category->id, [
            'name' => $category->name,
            'active' => $category->active,
        ]);

        return back()->with('success', 'Expense category status updated.');
    }
}
