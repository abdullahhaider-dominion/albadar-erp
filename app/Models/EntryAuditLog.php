<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'entry_id',
    'entry_type',
    'action',
    'old_data_json',
    'new_data_json',
    'old_amount',
    'new_amount',
    'performed_by',
    'performed_by_name',
    'performed_at',
    'ip_address',
    'user_agent',
    'reason',
])]
class EntryAuditLog extends Model
{
    public const ACTION_CREATED = 'created';

    public const ACTION_EDITED = 'edited';

    public const ACTION_DELETED = 'deleted';

    public const ACTION_RESTORED = 'restored';

    protected function casts(): array
    {
        return [
            'old_data_json' => 'array',
            'new_data_json' => 'array',
            'old_amount' => 'decimal:2',
            'new_amount' => 'decimal:2',
            'performed_at' => 'datetime',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function amount(): ?string
    {
        $value = $this->new_amount ?? $this->old_amount;

        return $value !== null ? (string) $value : null;
    }
}
