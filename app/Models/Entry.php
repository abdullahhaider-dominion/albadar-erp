<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'type',
    'entry_date',
    'amount',
    'expense_category_id',
    'payment_method',
    'party_name',
    'reference',
    'details',
    'is_edited',
    'created_by',
    'updated_by',
    'deleted_by',
])]
class Entry extends Model
{
    use SoftDeletes;

    public const TYPE_INCOME = 'income';

    public const TYPE_EXPENSE = 'expense';

    public const PAYMENT_METHODS = [
        'Cash',
        'Online',
        'Bank Transfer',
        'Cheque',
        'Other',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'amount' => 'decimal:2',
            'is_edited' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(EntryAuditLog::class)->orderByDesc('performed_at')->orderByDesc('id');
    }

    public function latestEdit(): BelongsTo
    {
        return $this->belongsTo(EntryAuditLog::class, 'id', 'entry_id')
            ->where('action', EntryAuditLog::ACTION_EDITED)
            ->orderByDesc('performed_at');
    }

    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    public function toAuditArray(): array
    {
        $this->loadMissing('category');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'entry_date' => optional($this->entry_date)->toDateString(),
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'expense_category_id' => $this->expense_category_id,
            'category_name' => $this->isExpense() ? ($this->category?->name ?? '') : 'Aamdan',
            'payment_method' => $this->payment_method,
            'party_name' => $this->party_name,
            'reference' => $this->reference,
            'details' => $this->details,
        ];
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like, $term) {
            $q->where('details', 'like', $like)
                ->orWhere('reference', 'like', $like)
                ->orWhere('party_name', 'like', $like)
                ->orWhere('payment_method', 'like', $like)
                ->orWhere('amount', 'like', $like)
                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', $like));

            if (is_numeric(str_replace(',', '', $term))) {
                $q->orWhere('amount', (float) str_replace(',', '', $term));
            }
        });
    }
}
