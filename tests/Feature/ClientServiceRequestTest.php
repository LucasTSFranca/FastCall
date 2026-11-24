<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\QueueCall;
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

    public function test_position_decreases_when_calls_ahead_are_closed_or_cancelled(): void
    {
        $firstTable = $this->createTable('01');
        $secondTable = $this->createTable('02', $firstTable->empresa_id);
        $thirdTable = $this->createTable('03', $firstTable->empresa_id);

        $firstCall = $this->createWaitingCall($firstTable, 1);
        $secondCall = $this->createWaitingCall($secondTable, 2);
        $thirdCall = $this->createWaitingCall($thirdTable, 3);

        $this->getJson(route('client.call.position', [$thirdTable->token_qr, $thirdCall->id]))
            ->assertOk()
            ->assertJsonPath('position', 3)
            ->assertJsonPath('ahead', 2);

        $firstCall->update([
            'situacao' => 'concluido',
            'encerrado_em' => now(),
        ]);

        $this->getJson(route('client.call.position', [$thirdTable->token_qr, $thirdCall->id]))
            ->assertOk()
            ->assertJsonPath('position', 2)
            ->assertJsonPath('ahead', 1);

        $this->deleteJson(route('client.call.cancel', $secondTable->token_qr))
            ->assertOk();

        $this->getJson(route('client.call.position', [$thirdTable->token_qr, $thirdCall->id]))
            ->assertOk()
            ->assertJsonPath('position', 1)
            ->assertJsonPath('ahead', 0);

        $this->assertDatabaseHas('chamados', [
            'id' => $secondCall->id,
            'situacao' => 'cancelado',
        ]);
    }

    private function createTable(string $identifier = '01', ?int $companyId = null): RestaurantTable
    {
        if ($companyId === null) {
            $company = Company::create([
                'nome' => 'Restaurante de teste',
                'identificador' => 'restaurante-de-teste',
                'tema' => 'dark',
                'ativo' => true,
            ]);
            $companyId = $company->id;
        }

        return RestaurantTable::create([
            'empresa_id' => $companyId,
            'identificador' => $identifier,
            'ativo' => true,
        ]);
    }

    private function createWaitingCall(RestaurantTable $table, int $position): QueueCall
    {
        return QueueCall::create([
            'empresa_id' => $table->empresa_id,
            'mesa_id' => $table->id,
            'posicao' => $position,
            'tipo_servico' => 'atendimento',
            'situacao' => 'aguardando',
            'chamado_em' => now(),
        ]);
    }
}
