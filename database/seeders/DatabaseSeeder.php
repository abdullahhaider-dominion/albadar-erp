<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Salaries',
            'Rent',
            'Advance',
            'Miscellaneous Expense',
            'Electricity Bill',
            'Internet Bill',
            'Transport',
            'Fuel',
            'Maintenance',
            'Office Expense',
            'Food / Tea',
            'Other',
        ];

        foreach ($categories as $name) {
            ExpenseCategory::query()->firstOrCreate(
                ['name' => $name],
                ['active' => true],
            );
        }

        if (! Setting::query()->where('key', 'company_name')->exists()) {
            Setting::put('company_name', 'Al Badar');
        }

        if (! Setting::query()->where('key', 'allow_editor_edit')->exists()) {
            Setting::put('allow_editor_edit', '0');
        }
    }
}
