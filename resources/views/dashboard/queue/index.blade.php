@extends('layouts.app')
@section('title', 'Fila de Chamados')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <span class="pulse-dot"></span>
        Fila de Chamados
    </h1>

    <div class="header-actions">
        {{-- Botão de fechar em lote (aparece ao selecionar) --}}
        <span id="selected-count" class="hidden" style="color:var(--text-muted);font-size:0.875rem;">
            <span id="sel-num">0</span> selecionados
        </span>

        <button
            type="button"
            id="batch-btn"
            class="btn btn-warning hidden"
            onclick="document.getElementById('batch-form').submit()"
        >
            ✓ Fechar Selecionados
        </button>

        @if(auth()->user()->canManageTables())
        <button
            type="button"
            class="btn btn-danger"
            onclick="document.getElementById('clear-modal').showModal()"
        >
            🗑 Limpar Fila
        </button>
        @endif
    </div>
</div>

{{-- Formulário oculto do batch --}}
<form method="POST" action="{{ route('queue.close.batch') }}" id="batch-form">
    @csrf
</form>

<livewire:queue.live-queue />

{{-- Modal: Limpar Toda a Fila --}}
@if(auth()->user()->canManageTables())
<dialog id="clear-modal" class="modal">
    <div class="modal-box">
        <h3>⚠️ Limpar Toda a Fila</h3>
        <p>
            Esta ação encerrará <strong>todos os chamados ativos</strong> da empresa.
            Use apenas em situações de emergência ou fim de expediente.
            <br><br>
            Para confirmar, digite <code style="background:var(--bg-hover);padding:0.1rem 0.4rem;border-radius:4px;">CONFIRMAR</code> abaixo:
        </p>
        <form method="POST" action="{{ route('queue.clear.all') }}">
            @csrf
            @method('DELETE')
            <div class="form-group">
                <input
                    type="text"
                    name="confirm"
                    class="form-input"
                    placeholder="Digite: CONFIRMAR"
                    required
                    autocomplete="off"
                >
                @error('confirm')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="modal-actions">
                <button
                    type="button"
                    class="btn btn-ghost"
                    onclick="document.getElementById('clear-modal').close()"
                >
                    Cancelar
                </button>
                <button type="submit" class="btn btn-danger">
                    🗑 Limpar Fila Agora
                </button>
            </div>
        </form>
    </div>
</dialog>
@endif
@endsection

@push('scripts')
<script>
function updateBatchUI() {
    const selectAll = document.getElementById('select-all');
    const total   = document.querySelectorAll('.row-checkbox').length;
    const checked = document.querySelectorAll('.row-checkbox:checked').length;

    document.getElementById('batch-btn').classList.toggle('hidden', checked === 0);
    document.getElementById('selected-count').classList.toggle('hidden', checked === 0);
    document.getElementById('sel-num').textContent = checked;

    if (selectAll) {
        selectAll.indeterminate = checked > 0 && checked < total;
        selectAll.checked       = checked === total && total > 0;
    }
}

document.addEventListener('change', event => {
    if (event.target.id === 'select-all') {
        document.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.checked = event.target.checked;
        });
        updateBatchUI();
    } else if (event.target.classList.contains('row-checkbox')) {
        updateBatchUI();
    }
});
</script>
@endpush
