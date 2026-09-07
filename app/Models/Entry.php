<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'type',
    'entry_date',
    'amount',
    'expense_category_id',
    'payment_method',
    'party_name',
    'reference',
    'details',
    'created_by',
    'updated_by',
])]
class Entry extends Model
{
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

    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
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
