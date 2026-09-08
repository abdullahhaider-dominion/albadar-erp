<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\EntryAuditLog;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
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
            'defaultMethod' => session('last_payment_method', 'Cash'),
        ]);
    }

    public function createExpense(): View
    {
        return view('entries.form', [
            'type' => Entry::TYPE_EXPENSE,
            'entry' => null,
            'categories' => ExpenseCategory::query()->where('active', true)->orderBy('name')->get(),
            'methods' => Entry::PAYMENT_METHODS,
            'defaultMethod' => session('last_payment_method', 'Cash'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $entry = Entry::query()->create($data);
        $entry->load('category');

        session(['last_payment_method' => $entry->payment_method]);

        AuditLogger::entry(EntryAuditLog::ACTION_CREATED, $entry, null, $entry->toAuditArray(), null, $request);

        $label = $data['type'] === Entry::TYPE_INCOME ? 'Aamdan / Income' : 'Kharcha / Expense';

        return redirect()
            ->route('roznamcha.index')
            ->with('success', $label.' entry saved successfully.');
    }

    public function edit(Entry $entry): View
    {
        $this->authorizeEdit($entry);

        $entry->load(['category', 'auditLogs.performer']);

        return view('entries.form', [
            'type' => $entry->type,
            'entry' => $entry,
            'categories' => ExpenseCategory::query()->where('active', true)->orderBy('name')->get(),
            'methods' => Entry::PAYMENT_METHODS,
            'defaultMethod' => $entry->payment_method,
        ]);
    }

    public function update(Request $request, Entry $entry): RedirectResponse
    {
        $this->authorizeEdit($entry);

        $old = $entry->load('category')->toAuditArray();
        $data = $this->validated($request, $entry);
        $data['updated_by'] = $request->user()->id;
        $reason = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ])['reason'] ?? null;

        $entry->fill($data);
        $entry->is_edited = true;
        $entry->save();
        $entry->load('category');

        session(['last_payment_method' => $entry->payment_method]);

        AuditLogger::entry(
            EntryAuditLog::ACTION_EDITED,
            $entry,
            $old,
            $entry->toAuditArray(),
            $reason,
            $request,
        );

        return redirect()
            ->route('roznamcha.index')
            ->with('success', 'Entry updated. Previous values were saved to the audit log.');
    }

    public function destroy(Request $request, Entry $entry): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $old = $entry->load('category')->toAuditArray();
        $entry->deleted_by = $request->user()->id;
        $entry->save();
        $entry->delete();

        AuditLogger::entry(
            EntryAuditLog::ACTION_DELETED,
            $entry,
            $old,
            null,
            $data['reason'],
            $request,
        );

        return redirect()
            ->route('roznamcha.index')
            ->with('success', 'Entry deleted. The record remains in Audit Log and can be restored.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $entry = Entry::withTrashed()->findOrFail($id);
        abort_unless($entry->trashed(), 404);

        $old = $entry->load('category')->toAuditArray();
        $entry->restore();
        $entry->deleted_by = null;
        $entry->save();
        $entry->load('category');

        AuditLogger::entry(
            EntryAuditLog::ACTION_RESTORED,
            $entry,
            $old,
            $entry->toAuditArray(),
            $request->input('reason'),
            $request,
        );

        return back()->with('success', 'Entry restored successfully.');
    }

    public function history(Entry $entry): JsonResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $logs = $entry->auditLogs()->with('performer')->get();

        return response()->json([
            'entry_id' => $entry->id,
            'logs' => $logs->map(fn (EntryAuditLog $log) => $this->presentLog($log)),
        ]);
    }

    private function presentLog(EntryAuditLog $log): array
    {
        return [
            'id' => $log->id,
            'action' => $log->action,
            'performed_by' => $log->performed_by_name,
            'performed_at' => $log->performed_at?->format('d M Y h:i A'),
            'reason' => $log->reason,
            'old' => $log->old_data_json,
            'new' => $log->new_data_json,
        ];
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
