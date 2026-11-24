<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();
            $table->foreignId('table_id')
                  ->constrained('tables')
                  ->cascadeOnDelete();
            $table->foreignId('attended_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->unsignedInteger('position');
            $table->enum('status', ['waiting', 'in_progress', 'done', 'cancelled'])->default('waiting');
            $table->text('notes')->nullable();
            $table->timestamp('called_at');
            $table->timestamp('attended_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'status', 'position']);
            $table->index('table_id');
            $table->index('attended_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_calls');
    }
};
