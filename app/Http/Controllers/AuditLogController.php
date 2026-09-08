<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Entry;
use App\Models\EntryAuditLog;
use App\Models\User;
use App\Support\DateRange;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to, $range] = DateRange::fromRequest($request, '30days');

        $query = EntryAuditLog::query()
            ->whereBetween('performed_at', [$from, $to])
            ->with(['performer', 'entry' => fn ($q) => $q->withTrashed()->with('category')]);

        if ($request->filled('user_id')) {
            $query->where('performed_by', $request->user_id);
        }

        if ($request->filled('action') && in_array($request->action, ['edited', 'deleted', 'created', 'restored'], true)) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entry_type') && in_array($request->entry_type, [Entry::TYPE_INCOME, Entry::TYPE_EXPENSE], true)) {
            $query->where('entry_type', $request->entry_type);
        }

        if ($request->filled('entry_id')) {
            $query->where('entry_id', $request->entry_id);
        }

        $logs = $query->orderByDesc('performed_at')->orderByDesc('id')->paginate(30)->withQueryString();

        $deletedEntries = Entry::onlyTrashed()
            ->with(['category', 'creator', 'deleter'])
            ->orderByDesc('deleted_at')
            ->limit(25)
            ->get();

        $activities = ActivityLog::query()
            ->orderByDesc('performed_at')
            ->limit(20)
            ->get();

        return view('audit.index', [
            'logs' => $logs,
            'deletedEntries' => $deletedEntries,
            'activities' => $activities,
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'presets' => DateRange::presets(),
            'users' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function show(EntryAuditLog $auditLog): JsonResponse
    {
        $auditLog->load('performer');

        return response()->json([
            'id' => $auditLog->id,
            'entry_id' => $auditLog->entry_id,
            'entry_type' => $auditLog->entry_type,
            'action' => $auditLog->action,
            'performed_by' => $auditLog->performed_by_name,
            'performed_at' => $auditLog->performed_at?->format('d M Y h:i A'),
            'ip_address' => $auditLog->ip_address,
            'user_agent' => $auditLog->user_agent,
            'reason' => $auditLog->reason,
            'old' => $auditLog->old_data_json,
            'new' => $auditLog->new_data_json,
        ]);
    }
}
