<!DOCTYPE html>
@php
    $savedCustomThemeColors = auth()->user()?->cores_tema_personalizado ?? [];
    $customThemeColors = array_merge(
        config('themes.personalizado.colors'),
        [
            'background' => $savedCustomThemeColors['background'] ?? config('themes.personalizado.colors.background'),
            'buttons' => $savedCustomThemeColors['buttons'] ?? $savedCustomThemeColors['primary'] ?? config('themes.personalizado.colors.buttons'),
            'text' => $savedCustomThemeColors['text'] ?? config('themes.personalizado.colors.text'),
        ],
    );
    $activeTheme = auth()->user()?->active_theme ?? 'dark';
@endphp
<html lang="pt-BR" data-theme="{{ $activeTheme }}" data-theme-update-url="{{ route('theme.update') }}"
      style="--custom-background: {{ $customThemeColors['background'] }}; --custom-buttons: {{ $customThemeColors['buttons'] }}; --custom-text: {{ $customThemeColors['text'] }}; --custom-button-label: {{ \App\Support\ThemeColors::contrastText($customThemeColors['buttons']) }}">
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
                    style="--theme-swatch: {{ $themeKey === 'personalizado' ? $customThemeColors['buttons'] : $theme['swatch'] }}; --theme-secondary-swatch: {{ $themeKey === 'personalizado' ? $customThemeColors['background'] : $theme['secondary'] }}"
                    title="{{ $theme['name'] }} — {{ $theme['harmony'] }}"
                    aria-label="Usar tema {{ $theme['name'] }}, harmonia {{ $theme['harmony'] }}"
                    aria-pressed="{{ auth()->user()->active_theme === $themeKey ? 'true' : 'false' }}"
                    type="button"
                ></button>
            @endforeach
            </div>
            <details class="theme-customizer" id="theme-customizer" {{ $activeTheme !== 'personalizado' ? 'hidden' : '' }}>
                <summary>Personalizar cores</summary>
                <form id="custom-theme-form" class="theme-customizer-panel">
                    <p>As cores personalizadas são opcionais e salvas somente para sua conta.</p>
                    @foreach([
                        'background' => 'Fundo',
                        'buttons' => 'Botões',
                        'text' => 'Textos',
                    ] as $colorKey => $colorLabel)
                        <label class="theme-color-field" for="theme-color-{{ $colorKey }}">
                            <span>{{ $colorLabel }}</span>
                            <input
                                id="theme-color-{{ $colorKey }}"
                                name="cores[{{ $colorKey }}]"
                                type="color"
                                value="{{ $customThemeColors[$colorKey] }}"
                                data-custom-color="{{ $colorKey }}"
                            >
                        </label>
                    @endforeach
                    <button class="btn btn-accent btn-sm" type="submit">Salvar cores</button>
                </form>
            </details>
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
