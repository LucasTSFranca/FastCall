@extends('layouts.app')
@section('title', 'Relatório de Atendimentos')

@section('content')
<div class="page-header">
    <h1 class="page-title">📊 Relatório de Atendimentos</h1>
    <a href="{{ route('reports.export', array_filter($filters)) }}" class="btn btn-accent">
        ⬇ Exportar CSV
    </a>
</div>

<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.index') }}" class="report-filters">
            @if(auth()->user()->isAdmin())
                <div class="form-group">
                    <label class="form-label" for="empresa_id">Empresa</label>
                    <select class="form-select" id="empresa_id" name="empresa_id">
                        <option value="">Todas as empresas</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" @selected(($filters['empresa_id'] ?? '') == $company->id)>
                                {{ $company->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="form-group">
                    <label class="form-label">Empresa</label>
                    <input class="form-input" value="{{ $companies->first()?->nome ?? 'Empresa não vinculada' }}" disabled>
                </div>
            @endif

            <div class="form-group">
                <label class="form-label" for="atendente_id">Quem atendeu</label>
                <select class="form-select" id="atendente_id" name="atendente_id">
                    <option value="">Todas as pessoas</option>
                    @foreach($attendants as $attendant)
                        <option value="{{ $attendant->id }}" @selected(($filters['atendente_id'] ?? '') == $attendant->id)>
                            {{ $attendant->nome }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="tipo_servico">Serviço</label>
                <select class="form-select" id="tipo_servico" name="tipo_servico">
                    <option value="">Todos os serviços</option>
                    @foreach($serviceTypes as $serviceType => $serviceLabel)
                        <option value="{{ $serviceType }}" @selected(($filters['tipo_servico'] ?? '') === $serviceType)>
                            {{ $serviceLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group report-filter-actions">
                <button type="submit" class="btn btn-accent">Aplicar filtros</button>
                <a href="{{ route('reports.index') }}" class="btn btn-ghost">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
    <div class="stat-card">
        <span class="stat-icon">🧾</span>
        <span class="stat-label">Atendimentos registrados</span>
        <span class="stat-value">{{ $total }}</span>
    </div>
</div>

<div class="card">
    <div class="card-header">Resumo de atendimentos</div>
    <div class="card-body" style="padding:0;">
        @if($rows->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">📊</span>
                <p>Nenhum atendimento encontrado com os filtros selecionados.</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Atendente</th>
                        <th>Serviço</th>
                        <th>Situação</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $row->empresa }}</td>
                            <td>{{ $row->atendente ?? 'Não atribuído' }}</td>
                            <td>{{ $serviceTypes[$row->tipo_servico] ?? $row->tipo_servico }}</td>
                            <td>{{ __('ui.queue_status.' . $row->situacao) }}</td>
                            <td><strong>{{ $row->quantidade }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
