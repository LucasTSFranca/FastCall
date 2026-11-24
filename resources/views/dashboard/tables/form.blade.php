@extends('layouts.app')
@section('title', isset($table) ? 'Editar Mesa' : 'Nova Mesa')

@section('content')
<div style="max-width:560px; margin: 0 auto;">
    <div class="page-header" style="justify-content:space-between; margin-bottom:1.5rem;">
        <h1 class="page-title">
            {{ isset($table) ? '✏️ Editar Mesa' : '➕ Nova Mesa' }}
        </h1>
        <a href="{{ route('tables.index') }}" class="btn btn-ghost">← Voltar</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form
                method="POST"
                action="{{ isset($table) ? route('tables.update', $table) : route('tables.store') }}"
            >
                @csrf
                @if(isset($table)) @method('PUT') @endif

                @if(isset($companies) && $companies->isNotEmpty())
                <div class="form-group">
                    <label class="form-label" for="empresa_id">
                        Empresa / Restaurante <span style="color:var(--danger)">*</span>
                    </label>
                    <select id="empresa_id" name="empresa_id" class="form-input" required>
                        <option value="">Selecione a empresa...</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ old('empresa_id', $table?->empresa_id) == $comp->id ? 'selected' : '' }}>
                                {{ $comp->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('empresa_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="identificador">
                        Identificador da Mesa <span style="color:var(--danger)">*</span>
                    </label>
                    <input
                        id="identificador"
                        type="text"
                        name="identificador"
                        class="form-input"
                        value="{{ old('identificador', $table?->identificador) }}"
                        placeholder="Ex: 01, A1, Varanda-3"
                        required
                        maxlength="20"
                    >
                    <small style="color:var(--text-muted);font-size:0.78rem;">
                        Código único para identificar a mesa internamente.
                    </small>
                    @error('identificador')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="nome_exibicao">Nome de exibição (opcional)</label>
                    <input
                        id="nome_exibicao"
                        type="text"
                        name="nome_exibicao"
                        class="form-input"
                        value="{{ old('nome_exibicao', $table?->nome_exibicao) }}"
                        placeholder="Ex: Mesa do Jardim, Área VIP"
                        maxlength="100"
                    >
                    @error('nome_exibicao')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="identificador_etiqueta_nfc">Identificador da etiqueta NFC (opcional)</label>
                    <input
                        id="identificador_etiqueta_nfc"
                        type="text"
                        name="identificador_etiqueta_nfc"
                        class="form-input"
                        value="{{ old('identificador_etiqueta_nfc', $table?->identificador_etiqueta_nfc) }}"
                        placeholder="Ex: 04:A3:B2:C1:D0"
                        maxlength="100"
                    >
                    <small style="color:var(--text-muted);font-size:0.78rem;">
                        Identificador da etiqueta NFC instalada na mesa, se houver.
                    </small>
                    @error('identificador_etiqueta_nfc')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-toggle">
                        <input type="hidden" name="ativo" value="0">
                        <input
                            type="checkbox"
                            name="ativo"
                            value="1"
                            {{ (bool) old('ativo', $table ? $table->ativo : true) ? 'checked' : '' }}
                        >
                        <span class="form-label" style="margin:0;">Mesa ativa</span>
                    </label>
                    <small style="color:var(--text-muted);font-size:0.78rem;margin-top:0.25rem;">
                        Mesas inativas não aceitam novos chamados pelo QR Code.
                    </small>
                </div>

                @if(isset($table))
                <div class="form-group" style="padding:0.75rem;background:var(--bg-secondary);border-radius:var(--radius-sm);border:1px solid var(--border);">
                    <span class="form-label">Token do QR Code</span>
                    <code style="display:block;font-size:0.75rem;color:var(--text-muted);word-break:break-all;margin-top:0.25rem;">
                        {{ $table->token_qr }}
                    </code>
                    <a href="{{ route('tables.qr', $table) }}" class="btn btn-ghost btn-sm" style="margin-top:0.75rem;">
                        📷 Ver/Imprimir QR Code
                    </a>
                </div>
                @endif

                <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.5rem;">
                    <a href="{{ route('tables.index') }}" class="btn btn-ghost">Cancelar</a>
                    <button type="submit" class="btn btn-accent">
                        {{ isset($table) ? 'Salvar Alterações' : 'Criar Mesa' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
