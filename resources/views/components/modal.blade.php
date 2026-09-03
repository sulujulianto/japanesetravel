@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<dialog
    data-modal
    data-modal-name="{{ $name }}"
    @if ($show) data-modal-initially-open @endif
    @if ($attributes->has('focusable')) data-modal-focusable @endif
    class="m-auto max-h-[calc(100dvh-3rem)] w-[calc(100%-2rem)] overflow-y-auto border-0 bg-transparent p-0 shadow-none backdrop:bg-gray-500/75 dark:backdrop:bg-gray-900/75 {{ $maxWidth }}"
>
    <div
        data-modal-panel
        class="overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-800"
    >
        {{ $slot }}
    </div>
</dialog>
