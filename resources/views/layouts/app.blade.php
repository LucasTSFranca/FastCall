<!DOCTYPE html>
<html lang="pt-BR" data-theme="{{ auth()->user()?->active_theme ?? 'safira' }}" data-theme-update-url="{{ route('theme.update') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FastCall') — Gestão de Filas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

{{-- ── Navbar ──────────────────────────────────────────────────────────── --}}
<nav class="navbar" aria-label="Navegação principal">
    <a href="{{ route('dashboard') }}" class="navbar-brand" aria-label="FastCall — Painel principal">
        <x-brand />
    </a>

    <div style="display:flex;align-items:center;gap:0.5rem;">
        {{-- Nav Links --}}
        <div class="nav-links" style="margin-right:0.5rem;">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                🏠 Painel
            </a>
            <a href="{{ route('queue.index') }}"
               class="nav-link {{ request()->routeIs('queue.*') ? 'active' : '' }}">
                📋 Fila
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isManager())
                <a href="{{ route('reports.index') }}"
                   class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    📊 Relatórios
                </a>
            @endif
            @if(auth()->user()->canManageTables())
            <a href="{{ route('tables.index') }}"
               class="nav-link {{ request()->routeIs('tables.*') ? 'active' : '' }}">
                🍽️ Mesas
            </a>
            @endif
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.companies.index') }}"
               class="nav-link {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                🏢 Empresas
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                👥 Usuários
            </a>
            @elseif(auth()->user()->isManager())
            <a href="{{ route('employees.create') }}"
               class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                👥 Adicionar funcionário
            </a>
            @endif
        </div>
    </div>

    <div class="navbar-actions">
        {{-- Seletor de Tema --}}
        <div class="theme-control">
            <span class="theme-control-label" id="theme-control-label">Aparência</span>
            <div class="theme-switcher" role="group" aria-labelledby="theme-control-label">
            @foreach(config('themes') as $themeKey => $theme)
                <button
                    class="theme-dot theme-{{ $themeKey }} {{ auth()->user()->active_theme === $themeKey ? 'active' : '' }}"
                    data-theme="{{ $themeKey }}"
                    style="--theme-swatch: {{ $theme['swatch'] }}; --theme-secondary-swatch: {{ $theme['secondary'] }}"
                    title="{{ $theme['name'] }} — {{ $theme['harmony'] }}"
                    aria-label="Usar tema {{ $theme['name'] }}, harmonia {{ $theme['harmony'] }}"
                    aria-pressed="{{ auth()->user()->active_theme === $themeKey ? 'true' : 'false' }}"
                    type="button"
                ></button>
            @endforeach
            </div>
        </div>
        <span id="theme-status" class="theme-status" role="status" aria-live="polite"></span>

        <div class="user-badge">
            {{ auth()->user()->nome }}
            <span class="role-pill">{{ __('ui.roles.' . auth()->user()->perfil) }}</span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm">Sair</button>
        </form>
    </div>
</nav>

{{-- ── Content ─────────────────────────────────────────────────────────── --}}
<div class="main-content">
    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">⚠️ {{ session('error') }}</div>
    @endif

    @yield('content')
</div>

@stack('scripts')
</body>
</html>
