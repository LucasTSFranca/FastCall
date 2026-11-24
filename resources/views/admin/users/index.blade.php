@extends('layouts.app')
@section('title', 'Usuários')

@section('content')
<div class="page-header">
    <h1 class="page-title">👥 Usuários do Sistema</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-accent">+ Novo Usuário</a>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        @if($users->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">👥</span>
                <p>Nenhum usuário cadastrado.</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Empresa</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td style="color:var(--text-secondary);font-size:0.875rem;">{{ $user->email }}</td>
                        <td style="color:var(--text-muted);font-size:0.875rem;">{{ $user->company?->name ?? '—' }}</td>
                        <td>
                            <span class="role-pill">{{ __('ui.roles.' . $user->role) }}</span>
                        </td>
                        <td>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display:inline;">
                                @csrf
                                <button type="submit"
                                        class="status-badge {{ $user->is_active ? 'status-done' : 'status-cancelled' }}"
                                        style="cursor:pointer;border:none;font:inherit;padding:0.25rem 0.6rem;"
                                        title="Clique para {{ $user->is_active ? 'desativar' : 'ativar' }} este usuário">
                                    {{ $user->is_active ? '✅ Ativo' : '❌ Inativo' }}
                                </button>
                            </form>
                            @else
                                <span class="status-badge status-done" title="Você não pode desativar seu próprio perfil">✅ Ativo (você)</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-sm">Editar</a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      onsubmit="return confirm('Remover {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:1rem 1.5rem;">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
