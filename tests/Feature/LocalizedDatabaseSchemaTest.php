<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\QueueCall;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LocalizedDatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_schema_and_relationships_use_portuguese_names(): void
    {
        $this->assertTrue(Schema::hasColumns('empresas', ['nome', 'identificador', 'tema', 'ativo']));
        $this->assertTrue(Schema::hasColumns('users', ['nome', 'empresa_id', 'perfil', 'ativo', 'tema_preferido']));
        $this->assertTrue(Schema::hasColumns('mesas', [
            'empresa_id',
            'identificador',
            'nome_exibicao',
            'token_qr',
            'ativo',
        ]));
        $this->assertTrue(Schema::hasColumns('chamados', [
            'empresa_id',
            'mesa_id',
            'atendente_id',
            'posicao',
            'tipo_servico',
            'situacao',
            'chamado_em',
            'encerrado_em',
        ]));

        $empresa = Company::create([
            'nome' => 'Restaurante de teste',
            'identificador' => 'restaurante-de-teste',
            'tema' => 'safira',
            'ativo' => true,
        ]);
        $usuario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'perfil' => 'gerente',
            'ativo' => true,
        ]);
        $mesa = RestaurantTable::create([
            'empresa_id' => $empresa->id,
            'identificador' => '01',
            'ativo' => true,
        ]);
        $chamado = QueueCall::create([
            'empresa_id' => $empresa->id,
            'mesa_id' => $mesa->id,
            'atendente_id' => $usuario->id,
            'posicao' => 1,
            'situacao' => 'aguardando',
            'chamado_em' => now(),
        ]);

        $this->assertSame($empresa->id, $usuario->company->id);
        $this->assertSame($empresa->id, $mesa->company->id);
        $this->assertSame($mesa->id, $chamado->table->id);
        $this->assertSame($usuario->id, $chamado->attendant->id);
    }
}
