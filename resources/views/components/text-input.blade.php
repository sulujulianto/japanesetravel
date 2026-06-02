@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'auth-input w-full px-4 py-2.5 text-sm disabled:opacity-60']) }}>
