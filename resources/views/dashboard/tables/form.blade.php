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
                    <label class="form-label" for="company_id">
                        Empresa / Restaurante <span style="color:var(--danger)">*</span>
                    </label>
                    <select id="company_id" name="company_id" class="form-input" required>
                        <option value="">Selecione a empresa...</option>
                        @foreach($companies as $comp)
                            <option value="{{ $comp->id }}" {{ old('company_id', $table?->company_id) == $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="identifier">
                        Identificador da Mesa <span style="color:var(--danger)">*</span>
                    </label>
                    <input
                        id="identifier"
                        type="text"
                        name="identifier"
                        class="form-input"
                        value="{{ old('identifier', $table?->identifier) }}"
                        placeholder="Ex: 01, A1, Varanda-3"
                        required
                        maxlength="20"
                    >
                    <small style="color:var(--text-muted);font-size:0.78rem;">
                        Código único para identificar a mesa internamente.
                    </small>
                    @error('identifier')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="label">Nome de exibição (opcional)</label>
                    <input
                        id="label"
                        type="text"
                        name="label"
                        class="form-input"
                        value="{{ old('label', $table?->label) }}"
                        placeholder="Ex: Mesa do Jardim, Área VIP"
                        maxlength="100"
                    >
                    @error('label')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="nfc_tag_id">Identificador da etiqueta NFC (opcional)</label>
                    <input
                        id="nfc_tag_id"
                        type="text"
                        name="nfc_tag_id"
                        class="form-input"
                        value="{{ old('nfc_tag_id', $table?->nfc_tag_id) }}"
                        placeholder="Ex: 04:A3:B2:C1:D0"
                        maxlength="100"
                    >
                    <small style="color:var(--text-muted);font-size:0.78rem;">
                        Identificador da etiqueta NFC instalada na mesa, se houver.
                    </small>
                    @error('nfc_tag_id')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-toggle">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ (bool) old('is_active', $table ? $table->is_active : true) ? 'checked' : '' }}
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
                        {{ $table->qr_token }}
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
