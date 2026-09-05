<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import PaginationControls from '../../../Components/Admin/PaginationControls.vue';
import StockAdjustmentForm from '../../../Components/Admin/StockAdjustmentForm.vue';
import StockBadge from '../../../Components/Admin/StockBadge.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type {
    AdminInventoryCopy,
    AdminInventoryFilters,
    AdminInventoryMovement,
    AdminInventoryResult,
    AdminInventoryRoutes,
} from '../../../types/adminInventory';
import type { SharedPageProps } from '../../../types/inertia';

defineOptions({ name: 'AdminLowStockPage' });

const props = defineProps<{
    copy: AdminInventoryCopy;
    filters: AdminInventoryFilters;
    inventory: AdminInventoryResult;
    movements: AdminInventoryMovement[];
    routes: AdminInventoryRoutes;
}>();

const page = usePage<SharedPageProps>();
const threshold = ref<number | string>(props.filters.threshold);

watch(() => props.filters.threshold, (value) => {
    threshold.value = value;
});

const applyFilter = (): void => {
    const normalizedThreshold = Math.max(1, Math.trunc(Number(threshold.value) || 1));
    threshold.value = normalizedThreshold;

    router.get(props.routes.lowStock, {
        threshold: normalizedThreshold,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const resetFilter = (): void => {
    threshold.value = 5;

    router.get(props.routes.lowStock, {}, {
        preserveScroll: true,
        preserveState: false,
        replace: true,
    });
};
</script>

<template>
    <Head :title="copy.title" />

    <AdminLayout active-navigation="low-stock" :copy="copy" :routes="routes">
        <header class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ copy.title }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--admin-muted)]">{{ copy.description }}</p>
        </header>

        <div v-if="page.props.flash.success" class="mt-6 rounded-xl border border-[var(--admin-success)]/25 bg-[var(--admin-success-soft)] px-4 py-3 text-sm font-medium text-[var(--admin-success)]" role="status">
            {{ page.props.flash.success }}
        </div>

        <section class="mt-6 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="stock-filter-heading">
            <div>
                <h2 id="stock-filter-heading" class="text-base font-semibold">{{ copy.filterTitle }}</h2>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.filterDescription }}</p>
            </div>

            <form class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="applyFilter">
                <div class="sm:w-56">
                    <label for="threshold" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.filterLabel }}</label>
                    <input
                        id="threshold"
                        v-model.number="threshold"
                        class="mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-3.5 py-2.5 text-sm text-[var(--admin-ink)] outline-none transition focus:border-[var(--admin-accent)] focus:ring-2 focus:ring-[var(--admin-accent)]/20"
                        inputmode="numeric"
                        min="1"
                        name="threshold"
                        required
                        type="number"
                    >
                </div>
                <button class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--admin-accent)] px-4 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)]" type="submit">
                    {{ copy.show }}
                </button>
                <button class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--admin-border)] px-4 text-sm font-semibold text-[var(--admin-muted)] transition-colors hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]" type="button" @click="resetFilter">
                    {{ copy.reset }}
                </button>
            </form>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)]" aria-labelledby="low-stock-list-heading">
            <div class="border-b border-[var(--admin-border)] px-5 py-4 sm:px-6">
                <h2 id="low-stock-list-heading" class="text-base font-semibold">{{ copy.resultsTitle }}</h2>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.resultsDescription }}</p>
            </div>

            <div v-if="inventory.data.length" class="hidden overflow-x-auto md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[var(--admin-border)] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">
                            <th class="px-6 py-3">{{ copy.product }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ copy.price }}</th>
                            <th class="px-4 py-3">{{ copy.remaining }}</th>
                            <th class="px-6 py-3">{{ copy.adjustment }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--admin-border)]">
                        <tr v-for="item in inventory.data" :key="item.id">
                            <td class="px-6 py-4">
                                <p class="font-semibold">{{ item.name }}</p>
                                <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ item.reference }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold">{{ item.price }}</td>
                            <td class="px-4 py-4">
                                <StockBadge :count="item.stockCount" :label="item.stockLabel" :status="item.stockStatus" />
                            </td>
                            <td class="w-96 px-6 py-4">
                                <StockAdjustmentForm
                                    :add-label="copy.add"
                                    :amount-label="copy.amount"
                                    :input-id="`adjustment-desktop-${item.id}`"
                                    :item="item"
                                    :subtract-label="copy.subtract"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="inventory.data.length" class="space-y-3 p-4 md:hidden">
                <article v-for="item in inventory.data" :key="item.id" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold">{{ item.name }}</p>
                            <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ item.reference }}</p>
                        </div>
                        <StockBadge :count="item.stockCount" :label="item.stockLabel" :status="item.stockStatus" />
                    </div>
                    <p class="mt-4 border-t border-[var(--admin-border)] pt-3 text-sm font-semibold">{{ item.price }}</p>
                    <StockAdjustmentForm
                        :add-label="copy.add"
                        class="mt-4"
                        :amount-label="copy.amount"
                        :input-id="`adjustment-mobile-${item.id}`"
                        :item="item"
                        show-label
                        :subtract-label="copy.subtract"
                    />
                </article>
            </div>

            <div v-else class="px-6 py-12 text-center">
                <p class="text-sm font-semibold text-[var(--admin-success)]">{{ copy.emptyTitle }}</p>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.emptyDescription }}</p>
            </div>

            <div v-if="inventory.pagination.total > 0" class="border-t border-[var(--admin-border)] px-4 py-4 sm:px-6">
                <PaginationControls
                    :next-label="copy.next"
                    :next-url="inventory.pagination.nextUrl"
                    :pages="inventory.pagination.pages"
                    :previous-label="copy.previous"
                    :previous-url="inventory.pagination.previousUrl"
                    :summary="inventory.pagination.summary"
                />
            </div>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)]" aria-labelledby="inventory-history-heading">
            <div class="border-b border-[var(--admin-border)] px-5 py-4 sm:px-6">
                <h2 id="inventory-history-heading" class="text-base font-semibold">{{ copy.historyTitle }}</h2>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.historyDescription }}</p>
            </div>

            <div v-if="movements.length" class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[var(--admin-border)] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">
                            <th class="px-6 py-3">{{ copy.recordedAt }}</th>
                            <th class="px-4 py-3">{{ copy.product }}</th>
                            <th class="px-4 py-3">{{ copy.type }}</th>
                            <th class="px-4 py-3">{{ copy.quantityChange }}</th>
                            <th class="px-4 py-3">{{ copy.stockChange }}</th>
                            <th class="px-4 py-3">{{ copy.actor }}</th>
                            <th class="px-6 py-3">{{ copy.reference }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--admin-border)]">
                        <tr v-for="movement in movements" :key="movement.id">
                            <td class="whitespace-nowrap px-6 py-4 text-[var(--admin-muted)]">{{ movement.createdAt }}</td>
                            <td class="px-4 py-4 font-semibold">{{ movement.productName }}</td>
                            <td class="whitespace-nowrap px-4 py-4">{{ movement.typeLabel }}</td>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold" :class="movement.quantityDelta > 0 ? 'text-[var(--admin-success)]' : 'text-[var(--admin-danger)]'">
                                {{ movement.quantityDeltaLabel }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">{{ movement.stockBefore }} → {{ movement.stockAfter }}</td>
                            <td class="whitespace-nowrap px-4 py-4">{{ movement.actor }}</td>
                            <td class="px-6 py-4">
                                <p class="font-mono text-xs">{{ movement.reference }}</p>
                                <p v-if="movement.orderReference !== '—'" class="mt-1 text-xs text-[var(--admin-muted)]">{{ copy.order }} {{ movement.orderReference }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="movements.length" class="space-y-3 p-4 lg:hidden">
                <article v-for="movement in movements" :key="movement.id" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ movement.productName }}</p>
                            <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ movement.typeLabel }} · {{ movement.createdAt }}</p>
                        </div>
                        <p class="font-semibold" :class="movement.quantityDelta > 0 ? 'text-[var(--admin-success)]' : 'text-[var(--admin-danger)]'">
                            {{ movement.quantityDeltaLabel }}
                        </p>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-[var(--admin-border)] pt-3 text-sm">
                        <div>
                            <dt class="text-xs text-[var(--admin-muted)]">{{ copy.stockChange }}</dt>
                            <dd class="mt-1 font-semibold">{{ movement.stockBefore }} → {{ movement.stockAfter }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-[var(--admin-muted)]">{{ copy.actor }}</dt>
                            <dd class="mt-1 font-semibold">{{ movement.actor }}</dd>
                        </div>
                    </dl>
                    <p class="mt-3 break-all font-mono text-xs text-[var(--admin-muted)]">{{ movement.reference }}</p>
                    <p v-if="movement.orderReference !== '—'" class="mt-1 text-xs text-[var(--admin-muted)]">{{ copy.order }} {{ movement.orderReference }}</p>
                </article>
            </div>

            <p v-else class="px-6 py-10 text-center text-sm text-[var(--admin-muted)]">{{ copy.historyEmpty }}</p>
        </section>
    </AdminLayout>
</template>
