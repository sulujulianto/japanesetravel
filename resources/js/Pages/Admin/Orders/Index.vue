<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

import PaginationControls from '../../../Components/Admin/PaginationControls.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type {
    AdminOrderCopy,
    AdminOrderFilters,
    AdminOrderListItem,
    AdminOrderOptions,
    AdminOrderPagination,
    AdminOrderRoutes,
} from '../../../types/adminOrders';

defineOptions({ name: 'AdminOrdersPage' });

const props = defineProps<{
    copy: AdminOrderCopy;
    filters: AdminOrderFilters;
    options: AdminOrderOptions;
    orders: { data: AdminOrderListItem[]; pagination: AdminOrderPagination };
    routes: AdminOrderRoutes;
}>();

const form = reactive<AdminOrderFilters>({ ...props.filters });

watch(
    () => props.filters,
    (filters) => Object.assign(form, filters),
    { deep: true },
);

const query = (): Record<string, string> => Object.fromEntries(
    Object.entries({
        q: form.search,
        status: form.status,
        payment_status: form.paymentStatus,
        date_from: form.dateFrom,
        date_to: form.dateTo,
    }).filter(([, value]) => value !== ''),
);

const submit = (): void => {
    router.get(props.routes.orders, query(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const reset = (): void => {
    Object.assign(form, {
        dateFrom: '',
        dateTo: '',
        paymentStatus: '',
        search: '',
        status: '',
    });
    router.get(props.routes.orders, {}, { preserveState: true, replace: true });
};
</script>

<template>
    <Head :title="copy.title" />

    <AdminLayout active-navigation="orders" :copy="copy" :routes="routes">
        <header class="relative overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-5 py-6 sm:px-7">
            <span aria-hidden="true" class="absolute inset-y-0 left-0 w-1.5 bg-[var(--admin-accent)]" />
            <div class="max-w-3xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ copy.title }}</h1>
                <p class="mt-2 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.description }}</p>
            </div>
        </header>

        <section class="mt-6 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="order-filters-heading">
            <h2 id="order-filters-heading" class="text-base font-semibold">{{ copy.filtersTitle }}</h2>
            <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.filtersDescription }}</p>

            <form class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-[minmax(14rem,2fr)_minmax(8rem,1fr)_minmax(10rem,1.2fr)_minmax(8.5rem,1fr)_minmax(8.5rem,1fr)]" @submit.prevent="submit">
                <div class="sm:col-span-2 xl:col-span-1">
                    <label for="order-search" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.search }}</label>
                    <input id="order-search" v-model="form.search" class="admin-order-field" name="q" :placeholder="copy.searchPlaceholder">
                </div>

                <div>
                    <label for="order-status" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.status }}</label>
                    <select id="order-status" v-model="form.status" class="admin-order-field" name="status">
                        <option value="">{{ copy.all }}</option>
                        <option v-for="option in options.orderStatuses" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>

                <div>
                    <label for="payment-status" class="block whitespace-nowrap text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.paymentStatus }}</label>
                    <select id="payment-status" v-model="form.paymentStatus" class="admin-order-field" name="payment_status">
                        <option value="">{{ copy.all }}</option>
                        <option v-for="option in options.paymentStatuses" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>

                <div>
                    <label for="date-from" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.dateFrom }}</label>
                    <input id="date-from" v-model="form.dateFrom" class="admin-order-field" name="date_from" type="date">
                </div>

                <div>
                    <label for="date-to" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.dateTo }}</label>
                    <input id="date-to" v-model="form.dateTo" class="admin-order-field" name="date_to" type="date">
                </div>

                <div class="flex flex-col gap-2 border-t border-[var(--admin-border)] pt-4 sm:col-span-2 sm:flex-row sm:items-center xl:col-span-5">
                    <button class="inline-flex min-h-10 items-center justify-center rounded-lg bg-[var(--admin-accent)] px-4 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)]" type="submit">{{ copy.applyFilters }}</button>
                    <button class="inline-flex min-h-10 items-center justify-center rounded-lg border border-[var(--admin-border)] px-4 text-sm font-semibold text-[var(--admin-muted)] transition-colors hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]" type="button" @click="reset">{{ copy.reset }}</button>
                </div>
            </form>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)]" aria-labelledby="orders-list-heading">
            <div class="border-b border-[var(--admin-border)] px-5 py-4 sm:px-6">
                <h2 id="orders-list-heading" class="text-base font-semibold">{{ copy.resultsTitle }}</h2>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.resultsDescription }}</p>
            </div>

            <div v-if="orders.data.length" class="hidden overflow-x-auto md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[var(--admin-border)] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">
                            <th class="whitespace-nowrap px-6 py-3">{{ copy.order }}</th>
                            <th class="px-4 py-3">{{ copy.customer }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ copy.date }}</th>
                            <th class="whitespace-nowrap px-4 py-3">{{ copy.total }}</th>
                            <th class="px-4 py-3">{{ copy.payment }}</th>
                            <th class="px-4 py-3">{{ copy.status }}</th>
                            <th class="px-6 py-3 text-right">{{ copy.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--admin-border)]">
                        <tr v-for="order in orders.data" :key="order.id">
                            <td class="whitespace-nowrap px-6 py-4 font-semibold">{{ order.reference }}</td>
                            <td class="px-4 py-4">
                                <p class="font-medium">{{ order.customer.username }}</p>
                                <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ order.customer.email || '—' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-[var(--admin-muted)]">{{ order.date }}</td>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold">{{ order.total }}</td>
                            <td class="px-4 py-4">
                                <StatusBadge v-if="order.payment" :label="order.payment.label" :status="order.payment.status" />
                                <span v-else class="text-xs text-[var(--admin-muted)]">{{ copy.noPayment }}</span>
                            </td>
                            <td class="px-4 py-4"><StatusBadge :label="order.status.label" :status="order.status.value" /></td>
                            <td class="px-6 py-4 text-right"><Link :href="order.url" class="text-sm font-semibold text-[var(--admin-accent)] transition-colors hover:text-[var(--admin-accent-active)]">{{ copy.detail }}</Link></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="orders.data.length" class="space-y-3 p-4 md:hidden">
                <article v-for="order in orders.data" :key="order.id" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold">{{ order.reference }}</p>
                            <p class="mt-1 truncate text-sm text-[var(--admin-muted)]">{{ order.customer.username }}</p>
                        </div>
                        <Link :href="order.url" class="shrink-0 text-sm font-semibold text-[var(--admin-accent)]">{{ copy.detail }}</Link>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-y border-[var(--admin-border)] py-3">
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[var(--admin-muted)]">{{ copy.date }}</dt><dd class="mt-1 text-sm">{{ order.date }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[var(--admin-muted)]">{{ copy.total }}</dt><dd class="mt-1 text-sm font-semibold">{{ order.total }}</dd></div>
                    </dl>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <StatusBadge v-if="order.payment" :label="order.payment.label" :status="order.payment.status" />
                        <StatusBadge v-else :label="copy.noPayment" status="unpaid" />
                        <StatusBadge :label="order.status.label" :status="order.status.value" />
                    </div>
                </article>
            </div>

            <div v-if="!orders.data.length" class="px-6 py-12 text-center">
                <p class="text-sm font-semibold">{{ copy.emptyTitle }}</p>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.emptyDescription }}</p>
            </div>

            <div v-if="orders.pagination.total > 0" class="border-t border-[var(--admin-border)] px-4 py-4 sm:px-6">
                <PaginationControls
                    :next-label="copy.next"
                    :next-url="orders.pagination.nextUrl"
                    :pages="orders.pagination.pages"
                    :previous-label="copy.previous"
                    :previous-url="orders.pagination.previousUrl"
                    :summary="orders.pagination.summary"
                />
            </div>
        </section>
    </AdminLayout>
</template>
