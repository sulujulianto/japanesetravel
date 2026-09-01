@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold uppercase tracking-wider text-[var(--public-muted)]']) }}>
    {{ $value ?? $slot }}
</label>
