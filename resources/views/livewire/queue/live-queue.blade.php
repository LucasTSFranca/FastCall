<div wire:poll.5s>
    <div class="page-header" style="margin-bottom:1rem;">
        <span style="color:var(--text-muted);">Chamados ativos</span>
        <span class="badge-count" id="queue-count">{{ $calls->count() }}</span>
    </div>

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
                            <th>Status</th>
                            <th>Aguardando há</th>
                            <th>Atendente</th>
                            <th style="width:200px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="queue-tbody">
                        @foreach($calls as $call)
                            <tr class="queue-row" data-call-id="{{ $call->id }}" data-status="{{ $call->status }}" wire:key="queue-call-{{ $call->id }}">
                                <td>
                                    <input
                                        type="checkbox"
                                        name="call_ids[]"
                                        form="batch-form"
                                        value="{{ $call->id }}"
                                        class="row-checkbox"
                                    >
                                </td>
                                <td>
                                    <span class="badge-count">{{ $call->position }}</span>
                                </td>
                                <td>
                                    <strong style="color:var(--text-primary);">
                                        {{ $call->table->display_name }}
                                    </strong>
                                    @if(!auth()->user()->company_id && $call->company)
                                        <span style="display:inline-block;margin-left:0.35rem;padding:0.15rem 0.5rem;border-radius:var(--radius-sm);background:var(--bg-secondary);font-size:0.75rem;color:var(--accent);">
                                            {{ $call->company->name }}
                                        </span>
                                    @endif
                                    @if($call->table->identifier !== $call->table->label)
                                        <br>
                                        <small style="color:var(--text-muted);">ID: {{ $call->table->identifier }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $call->status }}">
                                        {{ __('ui.queue_status.' . $call->status) }}
                                    </span>
                                </td>
                                <td style="color:var(--text-muted);font-size:0.85rem;">
                                    {{ $call->called_at->diffForHumans() }}
                                </td>
                                <td style="color:var(--text-secondary);font-size:0.875rem;">
                                    {{ $call->attendant?->name ?? '—' }}
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
