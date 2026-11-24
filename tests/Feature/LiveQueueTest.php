<?php

namespace Tests\Feature;

use App\Livewire\Queue\LiveQueue;
use App\Models\Company;
use App\Models\QueueCall;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class LiveQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_component_renders_and_is_configured_to_poll(): void
    {
        Livewire::actingAs(User::factory()->create(['ativo' => true]))
            ->test(LiveQueue::class)
            ->assertSee('Nenhuma mesa aguardando atendimento.')
            ->assertSee('wire:poll.5s', false);
    }

    public function test_queue_page_loads_livewire_assets_and_component(): void
    {
        $this->actingAs(User::factory()->create(['ativo' => true]))
            ->get(route('queue.index'))
            ->assertOk()
            ->assertSee('wire:poll.5s', false)
            ->assertSee('livewire.js', false);
    }

    public function test_queue_component_renders_calls_from_the_localized_schema(): void
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
        $table = RestaurantTable::create([
            'empresa_id' => $company->id,
            'identificador' => '07',
            'ativo' => true,
        ]);
        QueueCall::create([
            'empresa_id' => $company->id,
            'mesa_id' => $table->id,
            'posicao' => 1,
            'tipo_servico' => 'pedir_conta',
            'situacao' => 'aguardando',
            'chamado_em' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(LiveQueue::class)
            ->assertSee('Mesa 07')
            ->assertSee('Pedir a conta')
            ->assertSee('Aguardando');
    }

    public function test_queue_notifies_staff_when_a_waiting_call_exceeds_five_minutes(): void
    {
        Carbon::setTestNow(now());

        $company = Company::create([
            'nome' => 'Restaurante de teste',
            'identificador' => 'restaurante-de-teste',
            'tema' => 'dark',
            'ativo' => true,
        ]);
        $user = User::factory()->create([
            'empresa_id' => $company->id,
            'perfil' => 'atendente',
            'ativo' => true,
        ]);
        $table = RestaurantTable::create([
            'empresa_id' => $company->id,
            'identificador' => '08',
            'ativo' => true,
        ]);
        QueueCall::create([
            'empresa_id' => $company->id,
            'mesa_id' => $table->id,
            'posicao' => 1,
            'tipo_servico' => 'atendimento',
            'situacao' => 'aguardando',
            'chamado_em' => now()->subMinutes(6),
        ]);
        QueueCall::create([
            'empresa_id' => $company->id,
            'mesa_id' => $table->id,
            'posicao' => 2,
            'tipo_servico' => 'pedir_conta',
            'situacao' => 'aguardando',
            'chamado_em' => now()->subMinutes(4),
        ]);

        Livewire::actingAs($user)
            ->test(LiveQueue::class)
            ->assertSee('Há uma mesa aguardando há mais de 5 minutos.')
            ->assertSee('Mesa 08')
            ->assertSee('Chamar atendente')
            ->assertDontSee('Pedir a conta (há 4 minutos)');

        Carbon::setTestNow();
    }

    public function test_live_queue_displays_current_position_after_an_earlier_call_closes(): void
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
        $closedTable = RestaurantTable::create([
            'empresa_id' => $company->id,
            'identificador' => '09',
            'ativo' => true,
        ]);
        $waitingTable = RestaurantTable::create([
            'empresa_id' => $company->id,
            'identificador' => '10',
            'ativo' => true,
        ]);
        QueueCall::create([
            'empresa_id' => $company->id,
            'mesa_id' => $closedTable->id,
            'posicao' => 1,
            'tipo_servico' => 'atendimento',
            'situacao' => 'concluido',
            'chamado_em' => now()->subMinutes(10),
            'encerrado_em' => now(),
        ]);
        $waitingCall = QueueCall::create([
            'empresa_id' => $company->id,
            'mesa_id' => $waitingTable->id,
            'posicao' => 2,
            'tipo_servico' => 'atendimento',
            'situacao' => 'aguardando',
            'chamado_em' => now(),
        ]);

        $html = Livewire::actingAs($user)
            ->test(LiveQueue::class)
            ->assertSee('Mesa 10')
            ->html();

        $this->assertMatchesRegularExpression(
            '/<tr class="queue-row[^"]*" data-call-id="'.$waitingCall->id.'".*?<span class="badge-count">1<\/span>/s',
            $html
        );
    }
}
