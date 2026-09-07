<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@roznamcha.local',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
            'active' => true,
        ]);

        User::query()->create([
            'name' => 'Editor',
            'email' => 'editor@roznamcha.local',
            'password' => 'password',
            'role' => User::ROLE_EDITOR,
            'active' => true,
        ]);

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
            ExpenseCategory::query()->create([
                'name' => $name,
                'active' => true,
            ]);
        }

        Setting::put('company_name', 'Roznamcha ERP');
        Setting::put('allow_editor_edit', '0');
    }
}
