<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\QueueCall;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_are_redirected_to_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_dashboard_counts_calls_in_one_company_for_the_correct_statuses_and_day(): void
    {
        $company = Company::create([
            'nome' => 'Restaurante de teste',
            'identificador' => 'restaurante-de-teste',
            'tema' => 'dark',
            'ativo' => true,
        ]);
        $user = User::factory()->create([
            'empresa_id' => $company->id,
            'ativo' => true,
        ]);
        $activeTable = RestaurantTable::create([
            'empresa_id' => $company->id,
            'identificador' => '01',
            'ativo' => true,
        ]);
        RestaurantTable::create([
            'empresa_id' => $company->id,
            'identificador' => '02',
            'ativo' => false,
        ]);

        foreach ([
            ['situacao' => 'aguardando', 'chamado_em' => now()],
            ['situacao' => 'aguardando', 'chamado_em' => now()->subDay()],
            ['situacao' => 'em_atendimento', 'chamado_em' => now()],
            ['situacao' => 'concluido', 'chamado_em' => now()],
        ] as $index => $call) {
            QueueCall::create([
                'empresa_id' => $company->id,
                'mesa_id' => $activeTable->id,
                'posicao' => $index + 1,
                'tipo_servico' => 'atendimento',
                ...$call,
            ]);
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('waitingCount', 2)
            ->assertViewHas('inProgressCount', 1)
            ->assertViewHas('totalToday', 3)
            ->assertViewHas('tablesCount', 1);
    }
}
