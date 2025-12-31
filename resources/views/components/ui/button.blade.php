@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center rounded-xl font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white disabled:cursor-not-allowed disabled:opacity-60 dark:focus:ring-offset-slate-950';
    $variants = [
        'primary' => 'bg-rose-500 text-white hover:bg-rose-400 focus:ring-rose-400',
        'secondary' => 'bg-slate-900 text-white hover:bg-slate-800 focus:ring-slate-400 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100',
        'ghost' => 'border border-slate-200 text-slate-700 hover:border-slate-300 hover:bg-slate-50 focus:ring-slate-300 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800',
        'danger' => 'bg-red-500 text-white hover:bg-red-400 focus:ring-red-400',
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
