<div wire:poll.5s>
    <div class="page-header" style="margin-bottom:1rem;">
        <span style="color:var(--text-muted);">Chamados ativos</span>
        <span class="badge-count" id="queue-count">{{ $calls->count() }}</span>
    </div>

    @if($overdueCalls->isNotEmpty())
        <div class="alert alert-warning overdue-notification" role="status" aria-live="polite">
            <div>
                <strong>
                    ⚠️ {{ $overdueCalls->count() === 1 ? 'Há uma mesa aguardando há mais de 5 minutos.' : "Há {$overdueCalls->count()} mesas aguardando há mais de 5 minutos." }}
                </strong>
                <ul>
                    @foreach($overdueCalls as $overdueCall)
                        <li>
                            {{ $overdueCall->table->display_name }} —
                            {{ \App\Models\QueueCall::SERVICE_TYPES[$overdueCall->tipo_servico] ?? $overdueCall->tipo_servico }}
                            ({{ $overdueCall->chamado_em->diffForHumans() }})
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body" style="padding:0;">
            @if($calls->isEmpty())
                <div class="empty-state">
                    <span class="empty-icon">✅</span>
                    <p style="font-size:1rem;font-weight:600;color:var(--text-secondary);">
                        Nenhuma mesa aguardando atendimento.
                    </p>
                    <p style="color:var(--text-muted);font-size:0.875rem;">
                        Os chamados aparecerão aqui em tempo real conforme as mesas escanearem o QR Code.
                    </p>
                </div>
            @else
                <table class="data-table" id="queue-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="select-all" title="Selecionar todos os chamados" aria-label="Selecionar todos os chamados">
                            </th>
                            <th style="width:60px;">#</th>
                            <th>Mesa</th>
                            <th>Serviço</th>
                            <th>Situação</th>
                            <th>Aguardando há</th>
                            <th>Atendente</th>
                            <th style="width:200px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="queue-tbody">
                        @foreach($calls as $call)
                            <tr class="queue-row {{ $overdueCalls->contains('id', $call->id) ? 'queue-row-overdue' : '' }}" data-call-id="{{ $call->id }}" data-status="{{ $call->situacao }}" wire:key="queue-call-{{ $call->id }}">
                                <td>
                                    <input
                                        type="checkbox"
                                        name="chamado_ids[]"
                                        form="batch-form"
                                        value="{{ $call->id }}"
                                        class="row-checkbox"
                                    >
                                </td>
                                <td>
                                    <span class="badge-count">{{ $call->posicao }}</span>
                                </td>
                                <td>
                                    <strong style="color:var(--text-primary);">
                                        {{ $call->table->display_name }}
                                    </strong>
                                    @if(!auth()->user()->empresa_id && $call->company)
                                        <span style="display:inline-block;margin-left:0.35rem;padding:0.15rem 0.5rem;border-radius:var(--radius-sm);background:var(--bg-secondary);font-size:0.75rem;color:var(--accent);">
                                            {{ $call->company->nome }}
                                        </span>
                                    @endif
                                    @if($call->table->identificador !== $call->table->nome_exibicao)
                                        <br>
                                        <small style="color:var(--text-muted);">Identificador: {{ $call->table->identificador }}</small>
                                    @endif
                                </td>
                                <td>{{ \App\Models\QueueCall::SERVICE_TYPES[$call->tipo_servico] ?? $call->tipo_servico }}</td>
                                <td>
                                    <span class="status-badge status-{{ $call->situacao }}">
                                        {{ __('ui.queue_status.' . $call->situacao) }}
                                    </span>
                                </td>
                                <td style="color:var(--text-muted);font-size:0.85rem;">
                                    {{ $call->chamado_em->diffForHumans() }}
                                </td>
                                <td style="color:var(--text-secondary);font-size:0.875rem;">
                                    {{ $call->attendant?->nome ?? '—' }}
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        @if($call->isWaiting())
                                            <form method="POST" action="{{ route('queue.attend', $call) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-accent btn-sm">
                                                    Atender
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('queue.close', $call) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                ✓ Fechar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
