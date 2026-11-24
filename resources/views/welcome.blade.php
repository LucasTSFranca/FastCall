<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastCall — Sistema de Chamados e Filas por QR Code e NFC</title>
    @vite(['resources/css/app.css'])
</head>
<body class="client-page">
    <div class="client-container" style="max-width: 640px; text-align: center;">
        <x-brand class="client-logo" />
        <p style="color: var(--text-secondary); font-size: 1.05rem; margin-bottom: 2rem;">
            Sistema de Gestão de Filas e Chamados por QR Code / NFC para Restaurantes
        </p>

        <div class="client-card" style="padding: 2.5rem 2rem; margin-bottom: 1.5rem; text-align: left;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
                <span style="font-size: 2rem;">🍽️</span>
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                        Experiência Sem Atrito na Mesa
                    </h2>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">
                        O cliente escaneia o QR Code ou aproxima o celular da tag NFC para entrar na fila de atendimento.
                    </p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem;">
                <div style="background: var(--bg-secondary); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">📲</div>
                    <strong style="display: block; font-size: 0.95rem; color: var(--text-primary);">Feito para celular</strong>
                    <span style="font-size: 0.8rem; color: var(--text-secondary);">Acesse pelo navegador, sem instalar aplicativo.</span>
                </div>
                <div style="background: var(--bg-secondary); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">⚡</div>
                    <strong style="display: block; font-size: 0.95rem; color: var(--text-primary);">Fila atualizada</strong>
                    <span style="font-size: 0.8rem; color: var(--text-secondary);">Acompanhe o status sem precisar recarregar a página.</span>
                </div>
                <div style="background: var(--bg-secondary); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🎨</div>
                    <strong style="display: block; font-size: 0.95rem; color: var(--text-primary);">Seis paletas</strong>
                    <span style="font-size: 0.8rem; color: var(--text-secondary);">                                        Safira, Azul mineral, Coral e petróleo, Marinho e cobre, Bosque e Tríade.</span>
                </div>
                <div style="background: var(--bg-secondary); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                    <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">🛡️</div>
                    <strong style="display: block; font-size: 0.95rem; color: var(--text-primary);">Acesso por perfil</strong>
                    <span style="font-size: 0.8rem; color: var(--text-secondary);">Recursos adequados para administradores, gerentes e atendentes.</span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; justify-content: center;">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-accent btn-lg" style="text-decoration: none; padding: 0.85rem 2rem;">
                    🏠 Acessar painel
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-accent btn-lg" style="text-decoration: none; padding: 0.85rem 2rem;">
                    🔐 Acessar o sistema
                </a>
            @endauth
        </div>

        <div style="margin-top: 2.5rem; color: var(--text-muted); font-size: 0.8rem;">
            FastCall &copy; {{ date('Y') }} — Tecnologia em atendimento gastronômico
        </div>
    </div>
</body>
</html>
