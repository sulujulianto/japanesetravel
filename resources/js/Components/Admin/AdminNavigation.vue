<script setup lang="ts">
import type { NavigationItem, NavigationKey } from '../../types/admin';

defineOptions({ name: 'AdminNavigation' });

defineProps<{
    active: NavigationKey;
    items: NavigationItem[];
    navigationLabel: string;
}>();
</script>

<template>
    <nav :aria-label="navigationLabel" class="space-y-1">
        <a
            v-for="item in items"
            :key="item.key"
            :aria-current="active === item.key ? 'page' : undefined"
            :class="active === item.key
                ? 'bg-[var(--admin-accent-soft)] text-[var(--admin-accent)]'
                : 'text-[var(--admin-muted)] hover:bg-[var(--admin-muted-surface)] hover:text-[var(--admin-ink)]'"
            :href="item.href"
            class="flex min-h-11 items-center rounded-lg px-3 text-sm font-semibold transition-colors"
        >
            {{ item.label }}
        </a>
    </nav>
</template>
