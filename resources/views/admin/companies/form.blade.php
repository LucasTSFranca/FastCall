@extends('layouts.app')
@section('title', isset($company) ? 'Editar Empresa' : 'Nova Empresa')

@section('content')
<div style="max-width:560px; margin: 0 auto;">
    <div class="page-header" style="justify-content:space-between; margin-bottom:1.5rem;">
        <h1 class="page-title">{{ isset($company) ? '✏️ Editar Empresa' : '➕ Nova Empresa' }}</h1>
        <a href="{{ route('admin.companies.index') }}" class="btn btn-ghost">← Voltar</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form
                method="POST"
                action="{{ isset($company) ? route('admin.companies.update', $company) : route('admin.companies.store') }}"
            >
                @csrf
                @if(isset($company)) @method('PUT') @endif

                <div class="form-group">
                    <label class="form-label" for="name">Nome da Empresa *</label>
                    <input id="name" type="text" name="name" class="form-input" required
                           value="{{ old('name', $company?->name) }}" placeholder="Restaurante Exemplo">
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="cnpj">CNPJ</label>
                    <input id="cnpj" type="text" name="cnpj" class="form-input"
                           value="{{ old('cnpj', $company?->cnpj) }}" placeholder="00.000.000/0001-00" maxlength="18">
                    @error('cnpj') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="theme">Tema padrão *</label>
                    <select id="theme" name="theme" class="form-select" required>
                        @foreach(config('themes') as $themeKey => $theme)
                            <option value="{{ $themeKey }}" {{ old('theme', $company?->theme) === $themeKey ? 'selected' : '' }}>
                                {{ $theme['name'] }} — {{ $theme['harmony'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('theme') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-toggle">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ (bool) old('is_active', $company ? $company->is_active : true) ? 'checked' : '' }}>
                        <span class="form-label" style="margin:0;">Empresa ativa</span>
                    </label>
                    <small style="color:var(--text-muted);font-size:0.78rem;margin-top:0.25rem;">
                        Empresas inativas não permitem novos atendimentos.
                    </small>
                </div>

                <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.5rem;">
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-accent">
                        {{ isset($company) ? 'Salvar Alterações' : 'Criar Empresa' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
