@extends('layouts.app')
@section('title', 'Mesas')

@section('content')
<div class="page-header">
    <h1 class="page-title">🍽️ Gerenciar Mesas</h1>
    <a href="{{ route('tables.create') }}" class="btn btn-accent">
        + Nova Mesa
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        @if($tables->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">🍽️</span>
                <p>Nenhuma mesa cadastrada.</p>
                <a href="{{ route('tables.create') }}" class="btn btn-accent">Criar primeira mesa</a>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        @if(!auth()->user()->company_id)
                            <th>Empresa</th>
                        @endif
                        <th>Identificador</th>
                        <th>Nome de exibição</th>
                        <th>Etiqueta NFC</th>
                        <th>Status</th>
                        <th>Chamados Ativos</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tables as $table)
                    <tr>
                        @if(!auth()->user()->company_id)
                        <td style="color:var(--accent);font-size:0.875rem;">
                            {{ $table->company?->name ?? '—' }}
                        </td>
                        @endif
                        <td>
                            <strong>{{ $table->identifier }}</strong>
                        </td>
                        <td style="color:var(--text-secondary);">
                            {{ $table->label ?? '—' }}
                        </td>
                        <td style="font-size:0.78rem;color:var(--text-muted);font-family:monospace;">
                            {{ $table->nfc_tag_id ?? '—' }}
                        </td>
                        <td>
                            <form method="POST" action="{{ route('tables.toggle', $table) }}" style="display:inline;">
                                @csrf
                                <button type="submit"
                                        class="status-badge {{ $table->is_active ? 'status-done' : 'status-cancelled' }}"
                                        style="cursor:pointer;border:none;font:inherit;padding:0.25rem 0.6rem;"
                                        title="Clique para {{ $table->is_active ? 'desativar' : 'ativar' }} esta mesa">
                                    {{ $table->is_active ? '✅ Ativa' : '❌ Inativa' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            @if($table->active_calls_count > 0)
                                <span class="badge-count">{{ $table->active_calls_count }}</span>
                            @else
                                <span style="color:var(--text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('tables.qr', $table) }}" class="btn btn-ghost btn-sm" title="Ver QR Code">
                                    📷 QR
                                </a>
                                <a href="{{ route('tables.edit', $table) }}" class="btn btn-outline btn-sm">
                                    Editar
                                </a>
                                <form method="POST" action="{{ route('tables.destroy', $table) }}"
                                      onsubmit="return confirm('Remover a mesa {{ $table->display_name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrapper" style="padding:1rem 1.5rem;">
                {{ $tables->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
