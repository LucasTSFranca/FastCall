@extends('layouts.app')
@section('title', 'Empresas')

@section('content')
<div class="page-header">
    <h1 class="page-title">🏢 Empresas Cadastradas</h1>
    <a href="{{ route('admin.companies.create') }}" class="btn btn-accent">+ Nova Empresa</a>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        @if($companies->isEmpty())
            <div class="empty-state">
                <span class="empty-icon">🏢</span>
                <p>Nenhuma empresa cadastrada.</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Endereço curto</th>
                        <th>CNPJ</th>
                        <th>Tema</th>
                        <th>Usuários</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $company)
                    <tr>
                        <td><strong>{{ $company->name }}</strong></td>
                        <td style="font-family:monospace;font-size:0.82rem;color:var(--text-muted);">{{ $company->slug }}</td>
                        <td style="color:var(--text-secondary);">{{ $company->cnpj ?? '—' }}</td>
                        <td>
                            <span class="status-badge" style="background:var(--accent-light);color:var(--accent);">
                                {{ config('themes')[$company->theme]['name'] ?? ucfirst($company->theme) }}
                            </span>
                        </td>
                        <td style="color:var(--text-secondary);">{{ $company->users_count }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.companies.toggle', $company) }}" style="display:inline;">
                                @csrf
                                <button type="submit"
                                        class="status-badge {{ $company->is_active ? 'status-done' : 'status-cancelled' }}"
                                        style="cursor:pointer;border:none;font:inherit;padding:0.25rem 0.6rem;"
                                        title="Clique para {{ $company->is_active ? 'desativar' : 'ativar' }} esta empresa">
                                    {{ $company->is_active ? '✅ Ativa' : '❌ Inativa' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="actions-cell">
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-outline btn-sm">Editar</a>
                                <form method="POST" action="{{ route('admin.companies.destroy', $company) }}"
                                      onsubmit="return confirm('Remover {{ $company->name }}? Isso removerá todos os dados associados.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding:1rem 1.5rem;">{{ $companies->links() }}</div>
        @endif
    </div>
</div>
@endsection
