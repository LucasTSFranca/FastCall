<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Acesso Não Autorizado | FastCall</title>
    @vite(['resources/css/app.css'])
</head>
<body class="client-page">
    <div class="client-container" style="max-width:480px;text-align:center;">
        <x-brand class="client-logo" />
        <div class="client-card" style="padding:2.5rem 1.5rem;">
            <div style="font-size:3.5rem;margin-bottom:1rem;">🛡️</div>
            <h1 style="font-size:1.8rem;font-weight:800;color:var(--danger);margin-bottom:0.5rem;">403</h1>
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;">Acesso Não Autorizado</h2>
            <p style="color:var(--text-secondary);font-size:0.95rem;line-height:1.6;margin-bottom:2rem;">
                Você não possui permissão para acessar este recurso ou seu perfil de usuário não tem os privilégios necessários.
            </p>
            <div style="display:flex;gap:0.75rem;justify-content:center;">
                <a href="{{ route('dashboard') }}" class="btn btn-accent" style="text-decoration:none;">
                    🏠 Voltar ao Início
                </a>
                <a href="javascript:history.back()" class="btn btn-outline" style="text-decoration:none;">
                    ← Voltar
                </a>
            </div>
        </div>
    </div>
</body>
</html>
