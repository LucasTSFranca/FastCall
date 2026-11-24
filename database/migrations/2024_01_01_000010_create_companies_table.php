<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('identificador')->unique();
            $table->string('caminho_logo')->nullable();
            $table->string('cnpj', 18)->nullable()->unique();
            $table->enum('tema', ['dark', 'light', 'personalizado'])->default('dark');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('identificador');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
