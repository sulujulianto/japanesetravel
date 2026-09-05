<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import type { AdminInventoryListItem } from '../../types/adminInventory';

defineOptions({ name: 'AdminStockAdjustmentForm' });

type AdjustmentAction = 'add' | 'subtract';

const props = withDefaults(defineProps<{
    addLabel: string;
    amountLabel: string;
    inputId: string;
    item: AdminInventoryListItem;
    showLabel?: boolean;
    subtractLabel: string;
}>(), {
    showLabel: false,
});

const activeAction = ref<AdjustmentAction | null>(null);
const form = useForm({
    adjustment_token: globalThis.crypto.randomUUID(),
    amount: 10,
});

const submit = (action: AdjustmentAction): void => {
    activeAction.value = action;

    form.post(action === 'add' ? props.item.restockUrl : props.item.deductUrl, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('amount');
            form.adjustment_token = globalThis.crypto.randomUUID();
        },
        onFinish: () => {
            activeAction.value = null;
        },
    });
};
</script>

<template>
    <form
        :class="showLabel ? 'min-[360px]:grid-cols-[minmax(0,1fr)_auto]' : 'grid-cols-[minmax(5rem,1fr)_auto]'"
        class="grid gap-2"
        @submit.prevent="submit('add')"
    >
        <div>
            <label :class="showLabel ? 'block text-[11px] font-semibold uppercase tracking-[0.1em] text-[var(--admin-muted)]' : 'sr-only'" :for="inputId">
                {{ showLabel ? amountLabel : item.adjustmentLabel }}
            </label>
            <input
                :id="inputId"
                v-model.number="form.amount"
                :aria-describedby="form.errors.amount ? `${inputId}-error` : undefined"
                :aria-invalid="Boolean(form.errors.amount)"
                class="w-full rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-3 py-2.5 text-sm text-[var(--admin-ink)] outline-none transition focus:border-[var(--admin-accent)] focus:ring-2 focus:ring-[var(--admin-accent)]/20"
                :class="showLabel ? 'mt-2' : ''"
                inputmode="numeric"
                max="10000"
                min="1"
                name="amount"
                required
                type="number"
                @input="form.clearErrors('amount')"
            >
            <p v-if="form.errors.amount" :id="`${inputId}-error`" class="mt-2 text-xs font-medium text-[var(--admin-danger)]">
                {{ form.errors.amount }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-2 self-end">
            <button
                :aria-busy="activeAction === 'add'"
                class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--admin-accent)] px-3 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)] disabled:cursor-wait disabled:opacity-60"
                :disabled="form.processing"
                type="submit"
            >
                + {{ addLabel }}
            </button>
            <button
                :aria-busy="activeAction === 'subtract'"
                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--admin-danger)] px-3 text-sm font-semibold text-[var(--admin-danger)] transition-colors hover:bg-[var(--admin-danger-soft)] disabled:cursor-wait disabled:opacity-60"
                :disabled="form.processing"
                type="button"
                @click="submit('subtract')"
            >
                − {{ subtractLabel }}
            </button>
        </div>
    </form>
</template>
