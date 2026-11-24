@props(['showName' => true])

<span {{ $attributes->class(['brand-lockup']) }}>
    <svg
        class="brand-mark"
        viewBox="0 0 48 48"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <rect x="3" y="3" width="42" height="42" rx="14" fill="var(--palette-secondary)" />
        <path d="M14 16H28M14 24H23M14 32H28" stroke="white" stroke-width="3" stroke-linecap="round" />
        <circle cx="33" cy="24" r="4" fill="var(--palette-highlight)" />
    </svg>
    @if($showName)
        <span class="brand-wordmark">FastCall</span>
    @else
        <span class="visually-hidden">FastCall</span>
    @endif
</span>
