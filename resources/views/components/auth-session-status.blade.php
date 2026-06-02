@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-[10px] border border-[var(--auth-hairline)] bg-[var(--auth-muted-surface)] px-4 py-3 text-sm font-medium text-[var(--auth-ink)]']) }}>
        {{ $status }}
    </div>
@endif
