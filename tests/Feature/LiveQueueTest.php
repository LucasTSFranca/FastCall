<?php

namespace Tests\Feature;

use App\Livewire\Queue\LiveQueue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LiveQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_component_renders_and_is_configured_to_poll(): void
    {
        Livewire::actingAs(User::factory()->create(['is_active' => true]))
            ->test(LiveQueue::class)
            ->assertSee('Nenhuma mesa aguardando atendimento.')
            ->assertSee('wire:poll.5s', false);
    }

    public function test_queue_page_loads_livewire_assets_and_component(): void
    {
        $this->actingAs(User::factory()->create(['is_active' => true]))
            ->get(route('queue.index'))
            ->assertOk()
            ->assertSee('wire:poll.5s', false)
            ->assertSee('livewire.js', false);
    }
}
