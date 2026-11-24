<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\QueueCall;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $baseQuery = $this->filteredCalls($filters);
        $total = (clone $baseQuery)->count('chamados.id');
        $rows = $this->groupedCalls($baseQuery);

        $companies = auth()->user()->isAdmin()
            ? Company::query()->orderBy('nome')->get(['id', 'nome'])
            : Company::query()->whereKey(auth()->user()->empresa_id)->get(['id', 'nome']);

        $attendants = User::query()
            ->whereHas('attendedCalls')
            ->when(
                auth()->user()->isAdmin() && ! empty($filters['empresa_id']),
                fn ($query) => $query->where('empresa_id', $filters['empresa_id'])
            )
            ->when(
                ! auth()->user()->isAdmin(),
                fn ($query) => $query->where('empresa_id', auth()->user()->empresa_id)
            )
            ->orderBy('nome')
            ->get(['id', 'nome']);

        return view('dashboard.reports.index', [
            'filters' => $filters,
            'companies' => $companies,
            'attendants' => $attendants,
            'rows' => $rows,
            'total' => $total,
            'serviceTypes' => QueueCall::SERVICE_TYPES,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $rows = $this->groupedCalls($this->filteredCalls($filters));
        $total = $rows->sum('quantidade');

        return response()->streamDownload(function () use ($rows, $total) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, ['Total de atendimentos registrados', $total], ';');
            fputcsv($output, []);
            fputcsv($output, ['Empresa', 'Atendente', 'Serviço', 'Situação', 'Quantidade'], ';');

            foreach ($rows as $row) {
                fputcsv($output, [
                    $row->empresa,
                    $row->atendente ?? 'Não atribuído',
                    QueueCall::SERVICE_TYPES[$row->tipo_servico] ?? $row->tipo_servico,
                    __('ui.queue_status.'.$row->situacao),
                    $row->quantidade,
                ], ';');
            }

            fclose($output);
        }, 'relatorio-atendimentos.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'empresa_id' => ['nullable', 'integer', Rule::exists('empresas', 'id')],
            'atendente_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'tipo_servico' => ['nullable', Rule::in(array_keys(QueueCall::SERVICE_TYPES))],
        ]);
    }

    private function filteredCalls(array $filters): Builder
    {
        $query = DB::table('chamados')
            ->join('empresas', 'empresas.id', '=', 'chamados.empresa_id')
            ->leftJoin('users as atendentes', 'atendentes.id', '=', 'chamados.atendente_id');

        if (auth()->user()->isAdmin()) {
            if (! empty($filters['empresa_id'])) {
                $query->where('chamados.empresa_id', $filters['empresa_id']);
            }
        } else {
            $query->where('chamados.empresa_id', auth()->user()->empresa_id);
        }

        return $query
            ->when(
                ! empty($filters['atendente_id']),
                fn (Builder $query) => $query->where('chamados.atendente_id', $filters['atendente_id'])
            )
            ->when(
                ! empty($filters['tipo_servico']),
                fn (Builder $query) => $query->where('chamados.tipo_servico', $filters['tipo_servico'])
            );
    }

    private function groupedCalls(Builder $query)
    {
        return $query
            ->select([
                'empresas.nome as empresa',
                'atendentes.nome as atendente',
                'chamados.tipo_servico',
                'chamados.situacao',
            ])
            ->selectRaw('COUNT(chamados.id) as quantidade')
            ->groupBy([
                'empresas.id',
                'empresas.nome',
                'atendentes.id',
                'atendentes.nome',
                'chamados.tipo_servico',
                'chamados.situacao',
            ])
            ->orderBy('empresas.nome')
            ->orderBy('atendentes.nome')
            ->orderBy('chamados.tipo_servico')
            ->orderBy('chamados.situacao')
            ->get();
    }
}
