<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Entry;
use App\Models\EntryAuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public static function entry(
        string $action,
        Entry $entry,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
        ?Request $request = null,
    ): EntryAuditLog {
        $request ??= request();
        $user = $request?->user();

        return EntryAuditLog::query()->create([
            'entry_id' => $entry->id,
            'entry_type' => $entry->type,
            'action' => $action,
            'old_data_json' => $old,
            'new_data_json' => $new,
            'old_amount' => is_array($old) ? ($old['amount'] ?? null) : null,
            'new_amount' => is_array($new) ? ($new['amount'] ?? null) : null,
            'performed_by' => $user?->id,
            'performed_by_name' => $user?->name,
            'performed_at' => now(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'reason' => $reason,
        ]);
    }

    public static function activity(
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $data = [],
        ?Request $request = null,
    ): ActivityLog {
        $request ??= request();
        $user = $request?->user();

        return ActivityLog::query()->create([
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'data_json' => $data,
            'performed_by' => $user?->id,
            'performed_by_name' => $user?->name,
            'performed_at' => now(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
