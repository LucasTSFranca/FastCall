<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();
            $table->foreignId('mesa_id')
                ->constrained('mesas')
                ->cascadeOnDelete();
            $table->foreignId('atendente_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedInteger('posicao');
            $table->enum('tipo_servico', ['atendimento', 'pedir_conta'])->default('atendimento');
            $table->enum('situacao', ['aguardando', 'em_atendimento', 'concluido', 'cancelado'])->default('aguardando');
            $table->text('observacoes')->nullable();
            $table->timestamp('chamado_em');
            $table->timestamp('atendimento_iniciado_em')->nullable();
            $table->timestamp('encerrado_em')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'situacao']);
            $table->index(['empresa_id', 'situacao', 'posicao']);
            $table->index('mesa_id');
            $table->index('atendente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamados');
    }
};
