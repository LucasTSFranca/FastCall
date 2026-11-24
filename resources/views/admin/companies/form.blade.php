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
                    <label class="form-label" for="nome">Nome da Empresa *</label>
                    <input id="nome" type="text" name="nome" class="form-input" required
                           value="{{ old('nome', $company?->nome) }}" placeholder="Restaurante Exemplo">
                    @error('nome') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="cnpj">CNPJ</label>
                    <input id="cnpj" type="text" name="cnpj" class="form-input"
                           value="{{ old('cnpj', $company?->cnpj) }}" placeholder="00.000.000/0001-00" maxlength="18">
                    @error('cnpj') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="tema">Tema padrão *</label>
                    <select id="tema" name="tema" class="form-select" required>
                        @foreach(config('themes') as $themeKey => $theme)
                            <option value="{{ $themeKey }}" {{ old('tema', $company?->tema) === $themeKey ? 'selected' : '' }}>
                                {{ $theme['name'] }} — {{ $theme['harmony'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('tema') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-toggle">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" name="ativo" value="1"
                               {{ (bool) old('ativo', $company ? $company->ativo : true) ? 'checked' : '' }}>
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
