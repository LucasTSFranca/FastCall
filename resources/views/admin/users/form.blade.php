@extends('layouts.app')
@section('title', isset($user) ? 'Editar Usuário' : 'Novo Usuário')

@section('content')
<div style="max-width:560px; margin: 0 auto;">
    <div class="page-header" style="justify-content:space-between; margin-bottom:1.5rem;">
        <h1 class="page-title">{{ isset($user) ? '✏️ Editar Usuário' : '➕ Novo Usuário' }}</h1>
        <a href="{{ $isAdmin ? route('admin.users.index') : route('queue.index') }}" class="btn btn-ghost">← Voltar</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form
                method="POST"
                action="{{ isset($user) ? route('admin.users.update', $user) : route($isAdmin ? 'admin.users.store' : 'employees.store') }}"
            >
                @csrf
                @if(isset($user)) @method('PUT') @endif

                @if($isAdmin)
                    <div class="form-group">
                        <label class="form-label" for="empresa_id">Empresa vinculada</label>
                        <select id="empresa_id" name="empresa_id" class="form-select">
                            <option value="">— Nenhuma (administrador global) —</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ old('empresa_id', $user?->empresa_id) == $company->id ? 'selected' : '' }}>
                                    {{ $company->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('empresa_id') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div class="form-group">
                        <label class="form-label" for="empresa">Empresa</label>
                        <input id="empresa" class="form-input" value="{{ $company?->nome }}" disabled>
                    </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="nome">Nome *</label>
                    <input id="nome" type="text" name="nome" class="form-input" required
                           value="{{ old('nome', $user?->nome) }}" placeholder="Nome completo">
                    @error('nome') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">E-mail *</label>
                    <input id="email" type="email" name="email" class="form-input" required
                           value="{{ old('email', $user?->email) }}" placeholder="email@exemplo.com">
                    @error('email') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        Senha {{ isset($user) ? '(deixe em branco para manter)' : '*' }}
                    </label>
                    <input id="password" type="password" name="password" class="form-input"
                           {{ isset($user) ? '' : 'required' }} placeholder="Mínimo 8 caracteres" minlength="8">
                    @error('password') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">
                        Confirmar Senha {{ isset($user) ? '(se alterar)' : '*' }}
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-input" placeholder="Repita a senha"
                           {{ isset($user) ? '' : 'required' }}>
                </div>

                @if($isAdmin)
                    <div class="form-group">
                        <label class="form-label" for="perfil">Perfil *</label>
                        <select id="perfil" name="perfil" class="form-select" required>
                            <option value="atendente" {{ old('perfil', $user?->perfil) === 'atendente' ? 'selected' : '' }}>Atendente</option>
                            <option value="gerente" {{ old('perfil', $user?->perfil) === 'gerente' ? 'selected' : '' }}>Gerente</option>
                            <option value="administrador" {{ old('perfil', $user?->perfil) === 'administrador' ? 'selected' : '' }}>Administrador</option>
                        </select>
                        @error('perfil') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                @else
                    <input type="hidden" name="perfil" value="atendente">
                    <p class="form-label">Perfil: Atendente</p>
                @endif

                <div class="form-group">
                    <label class="form-toggle">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1"
                               {{ (bool) old('ativo', $user ? $user->ativo : true) ? 'checked' : '' }}
                               {{ isset($user) && $user->id === auth()->id() ? 'disabled' : '' }}>
                        <span class="form-label" style="margin:0;">
                            Usuário ativo
                            @if(isset($user) && $user->id === auth()->id())
                                <small style="color:var(--text-muted);">(sua própria conta)</small>
                            @endif
                        </span>
                    </label>
                    <small style="color:var(--text-muted);font-size:0.78rem;margin-top:0.25rem;">
                        Usuários desativados são impedidos de fazer login no sistema.
                    </small>
                </div>

                <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.5rem;">
                    <a href="{{ $isAdmin ? route('admin.users.index') : route('queue.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-accent">
                        {{ isset($user) ? 'Salvar Alterações' : 'Criar Usuário' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
