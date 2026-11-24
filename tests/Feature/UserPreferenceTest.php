<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_their_theme_preference(): void
    {
        $user = User::factory()->create(['ativo' => true]);

        $this->actingAs($user)
            ->postJson(route('theme.update'), ['tema' => 'coral_petroleo'])
            ->assertOk()
            ->assertExactJson(['ok' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'tema_preferido' => 'coral_petroleo',
        ]);
    }

    public function test_invalid_theme_preference_is_rejected(): void
    {
        $user = User::factory()->create(['ativo' => true]);

        $this->actingAs($user)
            ->postJson(route('theme.update'), ['tema' => 'roxo'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.tema.0', 'O valor selecionado para tema é inválido.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'tema_preferido' => 'safira',
        ]);
    }

    public function test_all_available_themes_can_be_saved(): void
    {
        $user = User::factory()->create(['ativo' => true]);

        foreach (array_keys(config('themes')) as $theme) {
            $this->actingAs($user)
                ->postJson(route('theme.update'), ['tema' => $theme])
                ->assertOk();

            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'tema_preferido' => $theme,
            ]);
        }
    }

    public function test_theme_options_are_named_in_brazilian_portuguese_and_identify_their_harmony(): void
    {
        $this->assertSame('pt_BR', config('app.locale'));
        $this->assertSame('Complementar dividida', config('themes.coral_petroleo.harmony'));
        $this->assertSame('Monocromática', config('themes.safira.harmony'));
        $this->assertSame('Administrador', __('ui.roles.administrador'));
    }
}
