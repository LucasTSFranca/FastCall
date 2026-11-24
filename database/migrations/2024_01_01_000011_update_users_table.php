<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('companies')
                  ->nullOnDelete();

            $table->enum('role', ['admin', 'manager', 'attendant'])
                  ->default('attendant')
                  ->after('email');

            $table->boolean('is_active')
                  ->default(true)
                  ->after('role');

            $table->enum('theme_preference', ['dark', 'white', 'pink', 'blue', 'green', 'yellow'])
                  ->default('dark')
                  ->after('is_active');

            $table->index(['company_id', 'role']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id', 'role', 'is_active', 'theme_preference']);
        });
    }
};
