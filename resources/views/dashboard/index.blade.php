@extends('layouts.app')
@section('title', 'Painel')

@section('content')
<div class="page-header">
    <h1 class="page-title">🏠 Painel principal</h1>
    <a href="{{ route('queue.index') }}" class="btn btn-accent">
        📋 Ver Fila Completa
    </a>
</div>

{{-- Cards de estatísticas --}}
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon">⏳</span>
        <span class="stat-label">Aguardando</span>
        <span class="stat-value" style="color:var(--waiting-badge)">{{ $waitingCount }}</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🔄</span>
        <span class="stat-label">Em Atendimento</span>
        <span class="stat-value" style="color:var(--progress-badge)">{{ $inProgressCount }}</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon">📊</span>
        <span class="stat-label">Total Hoje</span>
        <span class="stat-value">{{ $totalToday }}</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🍽️</span>
        <span class="stat-label">Mesas Ativas</span>
        <span class="stat-value">{{ $tablesCount }}</span>
    </div>
</div>

{{-- Últimos chamados --}}
<div class="card">
    <div class="card-header">Fila Atual — Próximos da vez</div>
    <div class="card-body" style="padding:0;">
        @if($recentCalls->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">✅</span>
                <p>Nenhuma mesa aguardando atendimento.</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mesa</th>
                        <th>Situação</th>
                        <th>Aguardando</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentCalls as $call)
                    <tr>
                        <td>
                            <span class="badge-count">{{ $call->posicao }}</span>
                        </td>
                        <td>
                            <strong>{{ $call->table->display_name }}</strong>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $call->situacao }}">
                                {{ __('ui.queue_status.' . $call->situacao) }}
                            </span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.85rem;">
                            {{ $call->chamado_em->diffForHumans() }}
                        </td>
                        <td>
                            <a href="{{ route('queue.index') }}" class="btn btn-ghost btn-sm">
                                Gerenciar
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh do dashboard a cada 10s
setTimeout(() => location.reload(), 10000);
</script>
@endpush
