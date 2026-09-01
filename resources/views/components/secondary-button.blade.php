<button {{ $attributes->merge(['type' => 'button', 'class' => 'ui-action inline-flex items-center justify-center rounded-xl border border-[var(--public-border)] bg-[var(--public-surface)] px-4 py-2 text-sm font-semibold text-[var(--public-ink)] hover:border-[var(--public-accent)] hover:text-[var(--public-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--public-accent)] focus:ring-offset-2 focus:ring-offset-[var(--public-canvas)] disabled:opacity-50']) }}>
    {{ $slot }}
</button>
