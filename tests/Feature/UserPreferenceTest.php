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
            ->postJson(route('theme.update'), ['tema' => 'personalizado'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'tema_preferido' => 'personalizado',
        ]);
    }

    public function test_user_can_save_optional_custom_theme_colors(): void
    {
        $user = User::factory()->create(['ativo' => true]);
        $colors = [
            'background' => '#f2f7f5',
            'buttons' => '#176b5b',
            'text' => '#20352f',
        ];

        $this->actingAs($user)
            ->postJson(route('theme.update'), [
                'tema' => 'personalizado',
                'cores' => $colors,
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('button_text', '#ffffff');

        $this->assertSame($colors, $user->fresh()->cores_tema_personalizado);
    }

    public function test_custom_theme_form_is_available_to_authenticated_users(): void
    {
        $user = User::factory()->create(['ativo' => true]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Personalizar cores')
            ->assertSee('name="cores[background]"', false)
            ->assertSee('name="cores[buttons]"', false)
            ->assertSee('name="cores[text]"', false);
    }

    public function test_custom_theme_colors_are_optional_but_must_be_hex_colors_when_provided(): void
    {
        $user = User::factory()->create(['ativo' => true]);

        $this->actingAs($user)
            ->postJson(route('theme.update'), ['tema' => 'personalizado'])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('theme.update'), [
                'tema' => 'personalizado',
                'cores' => [
                    'background' => '#f2f7f5',
                    'buttons' => '#176b5b',
                    'text' => 'vermelho',
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cores.text');

        $this->actingAs($user)
            ->postJson(route('theme.update'), [
                'tema' => 'personalizado',
                'cores' => [
                    'background' => '#f2f7f5',
                    'buttons' => '#176b5b',
                    'text' => '#ffffff',
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cores.text');
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
            'tema_preferido' => 'dark',
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

    public function test_only_the_three_supported_themes_are_available_in_brazilian_portuguese(): void
    {
        $this->assertSame('pt_BR', config('app.locale'));
        $this->assertSame(['dark', 'light', 'personalizado'], array_keys(config('themes')));
        $this->assertSame(['Escuro', 'Claro', 'Personalizado'], array_column(config('themes'), 'name'));
        $this->assertSame('Administrador', __('ui.roles.administrador'));
    }
}
