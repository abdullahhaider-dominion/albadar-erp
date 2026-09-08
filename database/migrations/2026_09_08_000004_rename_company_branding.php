<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'company_name')
            ->where('value', 'Roznamcha ERP')
            ->update(['value' => 'Al Badar']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'company_name')
            ->where('value', 'Al Badar')
            ->update(['value' => 'Roznamcha ERP']);
    }
};
