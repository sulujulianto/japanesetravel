<script setup lang="ts">
import { computed } from 'vue';

defineOptions({ name: 'StatusBadge' });

const props = defineProps<{ label: string; status: string }>();

const classes = computed(() => {
    if (['completed', 'paid'].includes(props.status)) return 'bg-[var(--admin-success-soft)] text-[var(--admin-success)]';
    if (['pending'].includes(props.status)) return 'bg-[var(--admin-warning-soft)] text-[var(--admin-warning)]';
    if (['cancelled', 'expired', 'failed'].includes(props.status)) return 'bg-[var(--admin-danger-soft)] text-[var(--admin-danger)]';
    if (['processing', 'refunded'].includes(props.status)) return 'bg-[var(--admin-info-soft)] text-[var(--admin-info)]';

    return 'bg-[var(--admin-muted-surface)] text-[var(--admin-muted)]';
});
</script>

<template>
    <span :class="classes" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
        {{ label }}
    </span>
</template>
