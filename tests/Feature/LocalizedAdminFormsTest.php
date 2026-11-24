<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedAdminFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_companies_and_users_with_localized_fields(): void
    {
        $admin = User::factory()->create([
            'perfil' => 'administrador',
            'ativo' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.companies.create'))
            ->assertOk()
            ->assertSee('name="nome"', false)
            ->assertSee('name="tema"', false);

        $this->post(route('admin.companies.store'), [
            'nome' => 'Restaurante Central',
            'cnpj' => '12.345.678/0001-90',
            'tema' => 'bosque',
            'ativo' => '1',
        ])->assertRedirect(route('admin.companies.index'));

        $company = Company::where('identificador', 'restaurante-central')->firstOrFail();

        $this->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('name="empresa_id"', false)
            ->assertSee('name="perfil"', false);

        $this->post(route('admin.users.store'), [
            'empresa_id' => $company->id,
            'nome' => 'Joana Atendente',
            'email' => 'joana@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'perfil' => 'atendente',
            'ativo' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'empresa_id' => $company->id,
            'nome' => 'Joana Atendente',
            'perfil' => 'atendente',
        ]);
    }

    public function test_manager_can_create_a_table_using_localized_fields(): void
    {
        $company = Company::create([
            'nome' => 'Restaurante da Praça',
            'identificador' => 'restaurante-da-praca',
            'tema' => 'safira',
            'ativo' => true,
        ]);
        $manager = User::factory()->create([
            'empresa_id' => $company->id,
            'perfil' => 'gerente',
            'ativo' => true,
        ]);

        $this->actingAs($manager)
            ->get(route('tables.create'))
            ->assertOk()
            ->assertSee('name="identificador"', false)
            ->assertSee('name="nome_exibicao"', false);

        $this->post(route('tables.store'), [
            'identificador' => '12',
            'nome_exibicao' => 'Varanda',
            'identificador_etiqueta_nfc' => 'NFC-12',
            'ativo' => '1',
        ])->assertRedirect(route('tables.index'));

        $this->assertDatabaseHas('mesas', [
            'empresa_id' => $company->id,
            'identificador' => '12',
            'nome_exibicao' => 'Varanda',
            'identificador_etiqueta_nfc' => 'NFC-12',
        ]);
    }

    public function test_manager_can_only_add_attendants_to_their_own_company(): void
    {
        $company = Company::create([
            'nome' => 'Empresa do gerente',
            'identificador' => 'empresa-do-gerente',
            'tema' => 'safira',
            'ativo' => true,
        ]);
        $otherCompany = Company::create([
            'nome' => 'Outra empresa',
            'identificador' => 'outra-empresa',
            'tema' => 'safira',
            'ativo' => true,
        ]);
        $manager = User::factory()->create([
            'empresa_id' => $company->id,
            'perfil' => 'gerente',
            'ativo' => true,
        ]);

        $this->actingAs($manager)
            ->get(route('employees.create'))
            ->assertOk()
            ->assertSee($company->nome)
            ->assertDontSee('name="empresa_id"', false)
            ->assertSee('name="perfil" value="atendente"', false);

        $this->post(route('employees.store'), [
            'empresa_id' => $otherCompany->id,
            'nome' => 'Funcionário da empresa',
            'email' => 'funcionario@empresa.test',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'perfil' => 'atendente',
            'ativo' => '1',
        ])->assertRedirect(route('queue.index'));

        $this->assertDatabaseHas('users', [
            'empresa_id' => $company->id,
            'nome' => 'Funcionário da empresa',
            'perfil' => 'atendente',
        ]);

        $this->post(route('employees.store'), [
            'nome' => 'Tentativa de promoção',
            'email' => 'promocao@empresa.test',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'perfil' => 'administrador',
            'ativo' => '1',
        ])->assertSessionHasErrors('perfil');

        $this->assertDatabaseMissing('users', [
            'email' => 'promocao@empresa.test',
        ]);
    }
}
