@props(['variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
        'info' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
        'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
        'danger' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.$variants[$variant]]) }}>
    {{ $slot }}
</span>
