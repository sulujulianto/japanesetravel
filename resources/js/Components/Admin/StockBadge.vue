<script setup lang="ts">
import { computed } from 'vue';

import type { StockStatus } from '../../types/adminSouvenirs';

defineOptions({ name: 'StockBadge' });

const props = defineProps<{
    count: string;
    label: string;
    status: StockStatus;
}>();

const classes = computed(() => {
    if (props.status === 'available') return 'bg-[var(--admin-success-soft)] text-[var(--admin-success)]';
    if (props.status === 'low') return 'bg-[var(--admin-warning-soft)] text-[var(--admin-warning)]';

    return 'bg-[var(--admin-danger-soft)] text-[var(--admin-danger)]';
});
</script>

<template>
    <span :class="classes" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
        {{ label }} · {{ count }}
    </span>
</template>
