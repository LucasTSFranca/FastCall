<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\RestaurantTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_the_bill_as_a_distinct_service(): void
    {
        $table = $this->createTable();

        $this->get(route('client.call.show', $table->token_qr))
            ->assertOk()
            ->assertSee('Pedir a Conta')
            ->assertSee('Chamar Atendente');

        $this->postJson(route('client.call.store', $table->token_qr), [
            'tipo_servico' => 'pedir_conta',
        ])->assertOk()
            ->assertJsonPath('tipo_servico', 'pedir_conta');

        $this->assertDatabaseHas('chamados', [
            'mesa_id' => $table->id,
            'tipo_servico' => 'pedir_conta',
            'situacao' => 'aguardando',
        ]);
    }

    public function test_customer_service_type_must_be_supported(): void
    {
        $table = $this->createTable();

        $this->postJson(route('client.call.store', $table->token_qr), [
            'tipo_servico' => 'servico_inexistente',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('tipo_servico');

        $this->assertDatabaseCount('chamados', 0);
    }

    private function createTable(): RestaurantTable
    {
        $company = Company::create([
            'nome' => 'Restaurante de teste',
            'identificador' => 'restaurante-de-teste',
            'tema' => 'safira',
            'ativo' => true,
        ]);

        return RestaurantTable::create([
            'empresa_id' => $company->id,
            'identificador' => '01',
            'ativo' => true,
        ]);
    }
}
