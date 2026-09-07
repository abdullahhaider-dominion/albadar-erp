<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'active'])]
class ExpenseCategory extends Model
{
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }
}
