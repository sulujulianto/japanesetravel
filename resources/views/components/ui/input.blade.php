@props(['type' => 'text'])

<input type="{{ $type }}" {{ $attributes->merge(['class' => 'w-full rounded-xl border border-[var(--public-border)] bg-[var(--public-surface)] px-4 py-2.5 text-sm text-[var(--public-ink)] shadow-sm transition placeholder:text-[var(--public-muted)] focus:border-[var(--public-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--public-accent)]/20']) }}>
