@extends('layouts.app')
@section('title', isset($user) ? 'Editar Usuário' : 'Novo Usuário')

@section('content')
<div style="max-width:560px; margin: 0 auto;">
    <div class="page-header" style="justify-content:space-between; margin-bottom:1.5rem;">
        <h1 class="page-title">{{ isset($user) ? '✏️ Editar Usuário' : '➕ Novo Usuário' }}</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">← Voltar</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form
                method="POST"
                action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}"
            >
                @csrf
                @if(isset($user)) @method('PUT') @endif

                <div class="form-group">
                    <label class="form-label" for="company_id">Empresa vinculada</label>
                    <select id="company_id" name="company_id" class="form-select">
                        <option value="">— Nenhuma (administrador global) —</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ old('company_id', $user?->company_id) == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">Nome *</label>
                    <input id="name" type="text" name="name" class="form-input" required
                           value="{{ old('name', $user?->name) }}" placeholder="Nome completo">
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
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

                @if(isset($user))
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirmar Senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation"
                           class="form-input" placeholder="Repita a senha">
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="role">Perfil *</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="attendant" {{ old('role', $user?->role) === 'attendant' ? 'selected' : '' }}>Atendente</option>
                        <option value="manager"   {{ old('role', $user?->role) === 'manager'   ? 'selected' : '' }}>Gerente</option>
                        <option value="admin"     {{ old('role', $user?->role) === 'admin'     ? 'selected' : '' }}>Administrador</option>
                    </select>
                    @error('role') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-toggle">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ (bool) old('is_active', $user ? $user->is_active : true) ? 'checked' : '' }}
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
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-accent">
                        {{ isset($user) ? 'Salvar Alterações' : 'Criar Usuário' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
