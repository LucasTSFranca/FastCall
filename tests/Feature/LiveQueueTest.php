<?php

namespace Tests\Feature;

use App\Livewire\Queue\LiveQueue;
use App\Models\Company;
use App\Models\QueueCall;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'tema' => 'safira',
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
}
