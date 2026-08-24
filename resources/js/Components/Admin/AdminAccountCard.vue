<script setup lang="ts">
defineOptions({ name: 'AdminAccountCard' });

defineProps<{
    email: string;
    logoutLabel: string;
    processing: boolean;
    username: string;
}>();

defineEmits<{ logout: [] }>();
</script>

<template>
    <section class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-3">
        <p class="truncate text-sm font-semibold text-[var(--admin-ink)]">{{ username }}</p>
        <p class="mt-1 truncate text-xs text-[var(--admin-muted)]">{{ email }}</p>
        <button
            :aria-busy="processing"
            :disabled="processing"
            class="mt-3 w-full border-t border-[var(--admin-border)] px-1 pt-3 text-left text-sm font-semibold text-[var(--admin-danger)] disabled:cursor-not-allowed disabled:opacity-60"
            type="button"
            @click="$emit('logout')"
        >
            {{ logoutLabel }}
        </button>
    </section>
</template>
