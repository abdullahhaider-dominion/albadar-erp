<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EntryController extends Controller
{
    public function createIncome(): View
    {
        return view('entries.form', [
            'type' => Entry::TYPE_INCOME,
            'entry' => null,
            'categories' => collect(),
            'methods' => Entry::PAYMENT_METHODS,
        ]);
    }

    public function createExpense(): View
    {
        return view('entries.form', [
            'type' => Entry::TYPE_EXPENSE,
            'entry' => null,
            'categories' => ExpenseCategory::query()->where('active', true)->orderBy('name')->get(),
            'methods' => Entry::PAYMENT_METHODS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        Entry::query()->create($data);

        $label = $data['type'] === Entry::TYPE_INCOME ? 'Aamdan / Income' : 'Kharcha / Expense';

        return redirect()
            ->route('roznamcha.index')
            ->with('success', $label.' entry added successfully.');
    }

    public function edit(Entry $entry): View
    {
        $this->authorizeEdit($entry);

        return view('entries.form', [
            'type' => $entry->type,
            'entry' => $entry,
            'categories' => ExpenseCategory::query()->where('active', true)->orderBy('name')->get(),
            'methods' => Entry::PAYMENT_METHODS,
        ]);
    }

    public function update(Request $request, Entry $entry): RedirectResponse
    {
        $this->authorizeEdit($entry);

        $data = $this->validated($request, $entry);
        $data['updated_by'] = $request->user()->id;

        $entry->update($data);

        return redirect()
            ->route('roznamcha.index')
            ->with('success', 'Entry updated successfully.');
    }

    public function destroy(Entry $entry): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $entry->delete();

        return redirect()
            ->route('roznamcha.index')
            ->with('success', 'Entry deleted successfully.');
    }

    private function validated(Request $request, ?Entry $entry = null): array
    {
        $type = $request->input('type', $entry?->type);

        $rules = [
            'type' => ['required', Rule::in([Entry::TYPE_INCOME, Entry::TYPE_EXPENSE])],
            'entry_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(Entry::PAYMENT_METHODS)],
            'party_name' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:2000'],
            'expense_category_id' => [
                Rule::requiredIf($type === Entry::TYPE_EXPENSE),
                'nullable',
                'exists:expense_categories,id',
            ],
        ];

        $data = $request->validate($rules);

        if ($data['type'] === Entry::TYPE_INCOME) {
            $data['expense_category_id'] = null;
        }

        return $data;
    }

    private function authorizeEdit(Entry $entry): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if (! Setting::allowEditorEdit()) {
            abort(403, 'Editors are not allowed to edit entries.');
        }
    }
}
