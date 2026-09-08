<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'action',
    'subject_type',
    'subject_id',
    'data_json',
    'performed_by',
    'performed_by_name',
    'performed_at',
    'ip_address',
    'user_agent',
])]
class ActivityLog extends Model
{
    protected function casts(): array
    {
        return [
            'data_json' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
