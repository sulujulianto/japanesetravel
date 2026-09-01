<script setup lang="ts">
import { computed } from 'vue';

import type { Theme } from '../composables/useTheme';

defineOptions({ name: 'ThemeToggle' });

const props = defineProps<{
    darkLabel: string;
    lightLabel: string;
    theme: Theme;
    useDarkLabel: string;
    useLightLabel: string;
}>();

const emit = defineEmits<{
    toggle: [];
}>();

const targetsDark = computed(() => props.theme === 'light');
const actionLabel = computed(() => targetsDark.value ? props.useDarkLabel : props.useLightLabel);
const visibleLabel = computed(() => targetsDark.value ? props.darkLabel : props.lightLabel);
</script>

<template>
    <button
        :aria-label="actionLabel"
        :aria-pressed="theme === 'dark'"
        :title="actionLabel"
        class="ui-action inline-flex min-h-10 items-center gap-2 rounded-full border border-[var(--public-border)] px-3 text-xs font-semibold text-[var(--public-ink)] hover:border-[var(--public-accent)] hover:text-[var(--public-accent)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--public-accent)]"
        type="button"
        @click="emit('toggle')"
    >
        <svg v-if="targetsDark" aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A8.5 8.5 0 1 1 11.2 3a6.8 6.8 0 0 0 9.8 9.8Z" />
        </svg>
        <svg v-else aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="3.5" />
            <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" />
        </svg>
        <span class="hidden sm:inline">{{ visibleLabel }}</span>
    </button>
</template>
