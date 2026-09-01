@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $base = 'ui-action inline-flex items-center justify-center rounded-xl font-semibold focus:outline-none focus:ring-2 focus:ring-[var(--public-accent)] focus:ring-offset-2 focus:ring-offset-[var(--public-canvas)] disabled:cursor-not-allowed disabled:opacity-60';
    $variants = [
        'primary' => 'bg-[var(--public-accent)] text-white hover:bg-[var(--public-accent-active)]',
        'secondary' => 'bg-[var(--public-ink)] text-[var(--public-surface)] hover:opacity-90',
        'ghost' => 'border border-[var(--public-border)] bg-[var(--public-surface)] text-[var(--public-ink)] hover:border-[var(--public-accent)] hover:text-[var(--public-accent)]',
        'danger' => 'bg-[var(--public-danger)] text-white hover:opacity-90',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-3 text-base',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$variants[$variant].' '.$sizes[$size]]) }}>
    {{ $slot }}
</button>
