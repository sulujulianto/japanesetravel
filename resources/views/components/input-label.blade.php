@props(['value'])

<label {{ $attributes->merge(['class' => 'auth-label block text-xs font-semibold uppercase tracking-wider']) }}>
    {{ $value ?? $slot }}
</label>
