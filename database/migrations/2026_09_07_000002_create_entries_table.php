<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // income | expense
            $table->date('entry_date');
            $table->decimal('amount', 15, 2);
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->string('payment_method', 30);
            $table->string('party_name')->nullable();
            $table->string('reference')->nullable();
            $table->text('details')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['entry_date', 'type']);
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
