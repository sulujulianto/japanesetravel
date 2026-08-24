<script setup lang="ts">
defineOptions({ name: 'MetricCard' });

defineProps<{
    description: string;
    label: string;
    value: string;
}>();
</script>

<template>
    <article class="relative overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6">
        <span aria-hidden="true" class="absolute inset-y-5 left-0 w-1 rounded-r-full bg-[var(--admin-accent)]" />
        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--admin-muted)]">{{ label }}</p>
        <p class="mt-3 break-words text-2xl font-semibold tracking-tight text-[var(--admin-ink)]">{{ value }}</p>
        <p class="mt-2 text-sm leading-6 text-[var(--admin-muted)]">{{ description }}</p>
    </article>
</template>
