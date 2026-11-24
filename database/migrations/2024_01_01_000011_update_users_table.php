<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('empresa_id')
                ->nullable()
                ->after('id')
                ->constrained('empresas')
                ->nullOnDelete();

            $table->enum('perfil', ['administrador', 'gerente', 'atendente'])
                ->default('atendente')
                ->after('email');

            $table->boolean('ativo')
                ->default(true)
                ->after('perfil');

            $table->enum('tema_preferido', ['safira', 'azul_mineral', 'coral_petroleo', 'marinho_cobre', 'bosque', 'triade'])
                ->default('safira')
                ->after('ativo');

            $table->index(['empresa_id', 'perfil']);
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn(['empresa_id', 'perfil', 'ativo', 'tema_preferido']);
        });
    }
};
