<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import type { PaginationPage } from '../../types/pagination';

defineOptions({ name: 'PaginationControls' });

const props = defineProps<{
    nextLabel: string;
    nextUrl: string | null;
    pages: PaginationPage[];
    previousLabel: string;
    previousUrl: string | null;
    summary: string;
}>();

type PaginationToken =
    | { key: string; type: 'gap' }
    | { key: string; page: PaginationPage; type: 'page' };

const tokens = computed<PaginationToken[]>(() => {
    const result: PaginationToken[] = [];

    props.pages.forEach((page, index) => {
        const previousPage = props.pages[index - 1];
        if (previousPage && page.page - previousPage.page > 1) {
            result.push({ key: `gap-${previousPage.page}-${page.page}`, type: 'gap' });
        }

        result.push({ key: `page-${page.page}`, page, type: 'page' });
    });

    return result;
});
</script>

<template>
    <nav class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" :aria-label="summary">
        <p class="text-sm text-[var(--admin-muted)]">{{ summary }}</p>

        <div class="flex flex-wrap items-center gap-1">
            <Link
                v-if="previousUrl"
                :aria-label="previousLabel"
                class="inline-flex min-h-10 items-center justify-center rounded-lg border border-[var(--admin-border)] px-3 text-sm font-semibold transition-colors hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]"
                :href="previousUrl"
                preserve-scroll
                preserve-state
            >
                ←
            </Link>
            <span v-else aria-hidden="true" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-[var(--admin-border)] px-3 text-[var(--admin-muted)] opacity-50">←</span>

            <template v-for="token in tokens" :key="token.key">
                <span v-if="token.type === 'gap'" class="px-1 text-sm text-[var(--admin-muted)]">…</span>
                <Link
                    v-else
                    :aria-current="token.page.active ? 'page' : undefined"
                    :class="token.page.active ? 'border-[var(--admin-ink)] bg-[var(--admin-ink)] text-[var(--admin-surface)]' : 'border-[var(--admin-border)] hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]'"
                    class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg border px-2 text-sm font-semibold transition-colors"
                    :href="token.page.url"
                    preserve-scroll
                    preserve-state
                >
                    {{ token.page.page }}
                </Link>
            </template>

            <Link
                v-if="nextUrl"
                :aria-label="nextLabel"
                class="inline-flex min-h-10 items-center justify-center rounded-lg border border-[var(--admin-border)] px-3 text-sm font-semibold transition-colors hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]"
                :href="nextUrl"
                preserve-scroll
                preserve-state
            >
                →
            </Link>
            <span v-else aria-hidden="true" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-[var(--admin-border)] px-3 text-[var(--admin-muted)] opacity-50">→</span>
        </div>
    </nav>
</template>
