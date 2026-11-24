<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastCall — Acessar</title>
    @vite(['resources/css/app.css'])
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-header">
        <x-brand class="login-logo" />
        <p class="login-subtitle">Acesse o painel de atendimento</p>
    </div>

    <div class="login-card">
        <h2 style="font-size:1.2rem;font-weight:700;margin:0 0 1.5rem;color:var(--text-primary);">
            Acesse sua conta
        </h2>

        @if($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">E-mail</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-input"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="seu@email.com"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Senha</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-input"
                    required
                    placeholder="••••••••"
                >
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
                <label class="form-toggle">
                    <input type="checkbox" name="remember">
                    <span style="font-size:0.85rem;color:var(--text-secondary);">Manter conectado</span>
                </label>
            </div>

            <button type="submit" class="btn btn-accent" style="width:100%;justify-content:center;padding:0.75rem;">
                Entrar
            </button>
        </form>
    </div>
</div>
</body>
</html>
