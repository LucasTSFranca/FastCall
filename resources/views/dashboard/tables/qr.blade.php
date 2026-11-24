@extends('layouts.app')
@section('title', 'QR Code — ' . $table->display_name)

@section('content')
<div class="page-header">
    <h1 class="page-title">📷 QR Code — {{ $table->display_name }}</h1>
    <a href="{{ route('tables.index') }}" class="btn btn-ghost">← Voltar</a>
</div>

<div class="qr-container">
    <div class="card" style="width:100%;max-width:420px;">
        <div class="card-body" style="text-align:center;">
            <p style="color:var(--text-muted);font-size:0.875rem;margin:0 0 1.5rem;">
                Fixe este QR Code na mesa para que os clientes possam chamar atendimento.
            </p>

            <div class="qr-wrapper">
                {!! $qrCode !!}
            </div>

            <div style="margin-top:1.5rem;padding:0.875rem;background:var(--bg-secondary);border-radius:var(--radius-sm);border:1px solid var(--border);">
                <p style="font-size:0.75rem;color:var(--text-muted);margin:0 0 0.25rem;">URL do QR Code:</p>
                <code style="font-size:0.8rem;color:var(--text-secondary);word-break:break-all;">{{ $url }}</code>
            </div>

            <div style="display:flex;gap:0.75rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap;">
                <button onclick="window.print()" class="btn btn-accent">
                    🖨️ Imprimir
                </button>
                <a href="{{ $url }}" target="_blank" class="btn btn-ghost">
                    🔗 Testar Link
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, .page-header, .btn { display: none !important; }
    .qr-container { margin: 0; }
    .card { border: none; box-shadow: none; }
    .qr-wrapper { background: #fff; padding: 1rem; }
}
</style>
@endsection
