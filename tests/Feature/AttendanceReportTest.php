<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\QueueCall;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_filter_and_export_service_counts(): void
    {
        [$company, $attendant] = $this->createAttendedService();
        $admin = User::factory()->create([
            'perfil' => 'administrador',
            'ativo' => true,
        ]);

        $filters = [
            'empresa_id' => $company->id,
            'atendente_id' => $attendant->id,
            'tipo_servico' => 'pedir_conta',
        ];

        $this->actingAs($admin)
            ->get(route('reports.index', $filters))
            ->assertOk()
            ->assertSee('Restaurante Relatório')
            ->assertSee('Atendente Relatório')
            ->assertSee('Pedir a conta')
            ->assertSee('Atendimentos registrados')
            ->assertSee('>1<', false);

        $export = $this->get(route('reports.export', $filters))
            ->assertOk()
            ->assertDownload('relatorio-atendimentos.csv');

        $this->assertStringContainsString('Atendente Relatório', $export->streamedContent());
        $this->assertStringContainsString('Pedir a conta', $export->streamedContent());
        $this->assertStringContainsString('"Total de atendimentos registrados";1', $export->streamedContent());
    }

    public function test_non_admin_report_is_always_scoped_to_their_company(): void
    {
        [$company] = $this->createAttendedService();
        [$otherCompany] = $this->createAttendedService('Outra Empresa', 'Outro Atendente');
        $manager = User::factory()->create([
            'empresa_id' => $company->id,
            'perfil' => 'gerente',
            'ativo' => true,
        ]);

        $this->actingAs($manager)
            ->get(route('reports.index', ['empresa_id' => $otherCompany->id]))
            ->assertOk()
            ->assertSee('Restaurante Relatório')
            ->assertDontSee('Outra Empresa');
    }

    private function createAttendedService(
        string $companyName = 'Restaurante Relatório',
        string $attendantName = 'Atendente Relatório'
    ): array {
        $company = Company::create([
            'nome' => $companyName,
            'identificador' => str($companyName)->slug()->toString(),
            'tema' => 'safira',
            'ativo' => true,
        ]);
        $attendant = User::factory()->create([
            'empresa_id' => $company->id,
            'nome' => $attendantName,
            'perfil' => 'atendente',
            'ativo' => true,
        ]);
        $table = RestaurantTable::create([
            'empresa_id' => $company->id,
            'identificador' => '01',
            'ativo' => true,
        ]);

        QueueCall::create([
            'empresa_id' => $company->id,
            'mesa_id' => $table->id,
            'atendente_id' => $attendant->id,
            'posicao' => 1,
            'tipo_servico' => 'pedir_conta',
            'situacao' => 'concluido',
            'chamado_em' => now(),
            'encerrado_em' => now(),
        ]);

        return [$company, $attendant];
    }
}
