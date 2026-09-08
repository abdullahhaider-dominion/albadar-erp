<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entry_id')->nullable()->index();
            $table->string('entry_type', 20)->nullable();
            $table->string('action', 30);
            $table->json('old_data_json')->nullable();
            $table->json('new_data_json')->nullable();
            $table->decimal('old_amount', 15, 2)->nullable();
            $table->decimal('new_amount', 15, 2)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_name')->nullable();
            $table->timestamp('performed_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_audit_logs');
    }
};
