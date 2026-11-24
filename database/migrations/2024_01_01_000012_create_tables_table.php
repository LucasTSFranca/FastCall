<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();
            $table->string('identificador', 20);
            $table->string('nome_exibicao', 100)->nullable();
            $table->string('token_qr', 80)->unique();
            $table->string('identificador_etiqueta_nfc', 100)->nullable()->index();
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['empresa_id', 'identificador']);
            $table->index(['empresa_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
