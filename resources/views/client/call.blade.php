<!DOCTYPE html>
<html lang="pt-BR" data-theme="{{ $table->company->theme ?? 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ config('themes')[$table->company->theme]['swatch'] ?? '#315b83' }}">
    <title>FastCall — {{ $table->display_name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="client-page">

<div class="client-container">
    <x-brand class="client-logo" />

    <div class="table-info">
        <p class="table-label">{{ $table->company->name }}</p>
        <h1 class="table-number">{{ $table->display_name }}</h1>
    </div>

    {{-- ── Estado: Sem chamado ────────────────────────────────────────── --}}
    <div id="state-idle" class="{{ $activeCall ? 'hidden' : '' }}" style="width:100%;">
        <div class="client-card">
            <p class="client-hint">
                Toque no botão abaixo para chamar um atendente à sua mesa.
            </p>
            <button id="btn-call" class="btn-call-main" type="button">
                🔔 Chamar Atendente
            </button>
        </div>
    </div>

    {{-- ── Estado: Na Fila ────────────────────────────────────────────── --}}
    <div
        id="state-waiting"
        class="{{ $activeCall ? '' : 'hidden' }}"
        data-call-id="{{ $activeCall?->id }}"
        style="width:100%;"
    >
        <div class="client-card queue-card">
            <p class="queue-label">Sua posição na fila</p>
            <div class="queue-position" id="queue-position">
                {{ $activeCall?->position ?? '—' }}
            </div>
            <p class="queue-subtitle" id="queue-subtitle" aria-live="polite">
                Aguarde, um atendente virá até você em breve.
            </p>
            <div class="queue-animation">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <div style="height:0.75rem;"></div>
        <button id="btn-cancel" class="btn-cancel" type="button">
            ✕ Cancelar e sair da fila
        </button>
    </div>

    {{-- ── Estado: Em Atendimento ─────────────────────────────────────── --}}
    <div id="state-progress" class="hidden" style="width:100%;">
        <div class="client-card progress-card">
            <div class="progress-icon">🙋</div>
            <h2>Atendente a caminho!</h2>
            <p>Um atendente está se dirigindo à sua mesa agora.</p>
        </div>
    </div>

    {{-- ── Estado: Atendimento concluído ──────────────────────────────── --}}
    <div id="state-done" class="hidden" style="width:100%;">
        <div class="client-card" style="text-align:center;">
            <div style="font-size:3.5rem;margin-bottom:1rem;">✅</div>
            <h2 style="font-size:1.3rem;font-weight:800;color:var(--success);margin:0 0 0.5rem;">
                Atendimento encerrado!
            </h2>
            <p style="color:var(--text-secondary);margin:0 0 1.5rem;">
                Obrigado! Esperamos que sua visita tenha sido ótima.
            </p>
            <button
                type="button"
                class="btn-call-main"
                onclick="location.reload()"
            >
                🔔 Chamar Novamente
            </button>
        </div>
    </div>
</div>

<script>
const QR_TOKEN = '{{ $table->qr_token }}';
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
let   CALL_ID  = {{ $activeCall?->id ?? 'null' }};
let   pollInterval = null;

// ── Utilitário de troca de estado ────────────────────────────────────────
function showState(id) {
    ['state-idle', 'state-waiting', 'state-progress', 'state-done'].forEach(s => {
        document.getElementById(s).classList.add('hidden');
    });
    document.getElementById(id).classList.remove('hidden');
}

// ── Chamar Atendente ─────────────────────────────────────────────────────
document.getElementById('btn-call').addEventListener('click', async () => {
    const btn = document.getElementById('btn-call');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    try {
        const res  = await fetch(`/call/${QR_TOKEN}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        });

        const data = await res.json();

        if (data.success) {
            CALL_ID = data.call_id;
            document.getElementById('queue-position').textContent = data.position;
            showState('state-waiting');
            startPolling();
        } else {
            throw new Error('O chamado não foi aceito.');
        }
    } catch (e) {
        console.error('Não foi possível enviar o chamado.', e);
        alert('Não foi possível enviar seu chamado. Tente novamente.');
        btn.disabled = false;
        btn.textContent = '🔔 Chamar Atendente';
    }
});

// ── Cancelar chamado ─────────────────────────────────────────────────────
document.getElementById('btn-cancel').addEventListener('click', async () => {
    try {
        const response = await fetch(`/call/${QR_TOKEN}/cancel`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
        });
        if (!response.ok) {
            throw new Error(`A solicitação falhou com o código ${response.status}.`);
        }
    } catch (e) {
        console.error('Não foi possível cancelar o chamado.', e);
        alert('Não foi possível cancelar seu chamado. Tente novamente.');
        return;
    }

    CALL_ID = null;
    stopPolling();
    showState('state-idle');
});

// ── Polling de posição ────────────────────────────────────────────────────
function startPolling() {
    if (pollInterval) clearInterval(pollInterval);

    pollInterval = setInterval(async () => {
        if (!CALL_ID) return;

        try {
            const res  = await fetch(`/call/${QR_TOKEN}/position/${CALL_ID}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(`A solicitação falhou com o código ${res.status}.`);
            }

            if (data.status === 'in_progress') {
                showState('state-progress');
                stopPolling();
            } else if (data.status === 'done' || data.status === 'closed') {
                CALL_ID = null;
                stopPolling();
                showState('state-done');
            } else if (data.status === 'cancelled') {
                CALL_ID = null;
                stopPolling();
                showState('state-idle');
            } else {
                // Atualiza posição
                document.getElementById('queue-position').textContent = data.position;

                const ahead = data.ahead ?? 0;
                const subtitle = document.getElementById('queue-subtitle');
                if (ahead === 0) {
                    subtitle.textContent = 'Você é o próximo! Um atendente virá em breve.';
                } else {
                    subtitle.textContent = `${ahead} chamado${ahead > 1 ? 's' : ''} à sua frente.`;
                }
            }
        } catch (e) {
            document.getElementById('queue-subtitle').textContent =
                'Não foi possível atualizar a fila. Tentaremos novamente em instantes.';
            console.warn('Não foi possível atualizar a posição na fila.', e);
        }
    }, 4000);
}

function stopPolling() {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

// Iniciar polling se já houver chamado ativo ao carregar a página
if (CALL_ID) startPolling();
</script>

</body>
</html>
