<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

import DashboardChart from '../../../Components/Admin/DashboardChart.vue';
import MetricCard from '../../../Components/Admin/MetricCard.vue';
import StatusBadge from '../../../Components/Admin/StatusBadge.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type {
    AdminDashboardCopy,
    AdminRoutes,
    DashboardCharts,
    DashboardMetric,
    LowStockItem,
    RecentOrder,
} from '../../../types/admin';
import type { SharedPageProps } from '../../../types/inertia';

defineOptions({ name: 'AdminDashboardPage' });

const props = defineProps<{
    copy: AdminDashboardCopy;
    lowStockItems: LowStockItem[];
    metrics: DashboardMetric[];
    recentOrders: RecentOrder[];
    routes: AdminRoutes;
}>();

const page = usePage<SharedPageProps>();
const charts = ref<DashboardCharts | null>(null);
const chartState = ref<'error' | 'loading' | 'ready'>('loading');
const abortController = new AbortController();

const isStringArray = (value: unknown): value is string[] => Array.isArray(value) && value.every((item) => typeof item === 'string');
const isNumberArray = (value: unknown): value is number[] => Array.isArray(value) && value.every((item) => typeof item === 'number');

const isChartSeries = (value: unknown): value is DashboardCharts['revenue'] => {
    if (typeof value !== 'object' || value === null) return false;
    const candidate = value as Record<string, unknown>;

    return isStringArray(candidate.labels) && isNumberArray(candidate.series) && candidate.labels.length === candidate.series.length;
};

const isChartsPayload = (value: unknown): value is DashboardCharts => {
    if (typeof value !== 'object' || value === null) return false;
    const candidate = value as Record<string, unknown>;

    return isChartSeries(candidate.revenue)
        && isChartSeries(candidate.orders)
        && Array.isArray(candidate.topSouvenirs)
        && candidate.topSouvenirs.every((item) => {
            if (typeof item !== 'object' || item === null) return false;
            const souvenir = item as Record<string, unknown>;

            return typeof souvenir.name === 'string' && typeof souvenir.total === 'number';
        });
};

const loadCharts = async (): Promise<void> => {
    try {
        const response = await fetch(props.routes.charts, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: abortController.signal,
        });

        if (!response.ok) throw new Error('Dashboard chart request failed.');

        const payload: unknown = await response.json();
        if (!isChartsPayload(payload)) throw new Error('Dashboard chart payload is invalid.');

        charts.value = payload;
        chartState.value = 'ready';
    } catch (error) {
        if (error instanceof DOMException && error.name === 'AbortError') return;
        chartState.value = 'error';
    }
};

onMounted(loadCharts);
onBeforeUnmount(() => abortController.abort());
</script>

<template>
    <Head :title="copy.title" />

    <AdminLayout active-navigation="dashboard" :copy="copy" :routes="routes">
        <template #default="{ theme }">
            <header class="relative overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-5 py-6 sm:px-7 lg:flex lg:items-center lg:justify-between lg:gap-8">
                <span aria-hidden="true" class="absolute inset-y-0 left-0 w-1.5 bg-[var(--admin-accent)]" />
                <div class="max-w-2xl">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ copy.title }}</h1>
                    <p class="mt-2 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.description }}</p>
                </div>
                <div class="mt-5 flex flex-col gap-2 sm:flex-row lg:mt-0">
                    <a :href="routes.orders" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-[var(--admin-accent)] px-4 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)]">{{ copy.manageOrders }}</a>
                    <a :href="routes.lowStock" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-[var(--admin-border)] px-4 text-sm font-semibold transition-colors hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]">{{ copy.checkStock }}</a>
                </div>
            </header>

            <section class="mt-7" aria-labelledby="metrics-heading">
                <div class="mb-4">
                    <h2 id="metrics-heading" class="text-base font-semibold">{{ copy.metricsTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.metricsDescription }}</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <MetricCard v-for="metric in metrics" :key="metric.key" :description="metric.description" :label="metric.label" :value="metric.value" />
                </div>
            </section>

            <div v-if="chartState !== 'ready'" class="mt-6 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-8 text-center text-sm text-[var(--admin-muted)]" :role="chartState === 'error' ? 'alert' : 'status'">
                {{ chartState === 'error' ? copy.chartsError : copy.loadingCharts }}
            </div>

            <template v-else-if="charts">
                <section class="mt-6 grid gap-6 xl:grid-cols-3" :aria-label="copy.revenueChartTitle">
                    <article class="min-w-0 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6 xl:col-span-2">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold">{{ copy.revenueChartTitle }}</h2>
                                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.revenueChartDescription }}</p>
                            </div>
                            <span class="rounded-full bg-[var(--admin-muted-surface)] px-2.5 py-1 text-xs font-semibold text-[var(--admin-muted)]">IDR</span>
                        </div>
                        <div class="mt-6 overflow-x-auto pb-1">
                            <DashboardChart :empty-message="copy.noRevenueChart" kind="line" :label="copy.revenueChartTitle" :labels="charts.revenue.labels" :locale="page.props.locale" :series="charts.revenue.series" :theme="theme" value-format="currency" />
                        </div>
                    </article>

                    <article class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6">
                        <h2 class="text-lg font-semibold">{{ copy.topSouvenirsTitle }}</h2>
                        <p class="mt-1 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.topSouvenirsDescription }}</p>
                        <div v-if="charts.topSouvenirs.length" class="mt-5 space-y-2">
                            <div v-for="(item, index) in charts.topSouvenirs" :key="item.name" class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] px-3 py-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--admin-surface)] text-xs font-semibold text-[var(--admin-accent)]">{{ index + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold">{{ item.name }}</p>
                                    <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ item.total.toLocaleString(page.props.locale) }} {{ copy.sold }}</p>
                                </div>
                                <strong class="text-sm text-[var(--admin-success)]">{{ item.total.toLocaleString(page.props.locale) }}</strong>
                            </div>
                        </div>
                        <div v-else class="mt-5 rounded-xl border border-dashed border-[var(--admin-border)] p-5 text-center text-sm text-[var(--admin-muted)]">{{ copy.noSales }}</div>
                    </article>
                </section>

                <section class="mt-6 grid gap-6 xl:grid-cols-3" :aria-label="copy.ordersChartTitle">
                    <article class="min-w-0 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6 xl:col-span-2">
                        <h2 class="text-lg font-semibold">{{ copy.ordersChartTitle }}</h2>
                        <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.ordersChartDescription }}</p>
                        <div class="mt-6 overflow-x-auto pb-1">
                            <DashboardChart :empty-message="copy.noOrdersChart" kind="bar" :label="copy.ordersChartTitle" :labels="charts.orders.labels" :locale="page.props.locale" :series="charts.orders.series" :theme="theme" value-format="number" />
                        </div>
                    </article>

                    <article class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold">{{ copy.criticalStockTitle }}</h2>
                                <p class="mt-1 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.criticalStockDescription }}</p>
                            </div>
                            <a :href="routes.lowStock" class="text-xs font-semibold text-[var(--admin-accent)]">{{ copy.view }}</a>
                        </div>
                        <div v-if="lowStockItems.length" class="mt-5 space-y-2">
                            <div v-for="item in lowStockItems" :key="item.id" class="flex items-center justify-between gap-4 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ item.name }}</p>
                                    <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ copy.remainingStock }}: {{ item.stockLabel }}</p>
                                </div>
                                <StatusBadge :label="item.stockLabel" status="pending" />
                            </div>
                        </div>
                        <div v-else class="mt-5 rounded-xl border border-dashed border-[var(--admin-border)] p-5 text-center">
                            <p class="text-sm font-semibold text-[var(--admin-success)]">{{ copy.allStockSafe }}</p>
                            <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ copy.allStockSafeDescription }}</p>
                        </div>
                    </article>
                </section>
            </template>

            <section class="mt-6 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="recent-orders-heading">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 id="recent-orders-heading" class="text-lg font-semibold">{{ copy.recentOrdersTitle }}</h2>
                        <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.recentOrdersDescription }}</p>
                    </div>
                    <a :href="routes.orders" class="text-sm font-semibold text-[var(--admin-accent)]">{{ copy.viewAllOrders }}</a>
                </div>

                <div v-if="recentOrders.length" class="mt-5 hidden overflow-x-auto md:block">
                    <table class="min-w-full text-sm">
                        <thead><tr class="border-b border-[var(--admin-border)] text-left text-[11px] uppercase tracking-[0.12em] text-[var(--admin-muted)]"><th class="px-2 pb-3">{{ copy.order }}</th><th class="px-2 pb-3">{{ copy.customer }}</th><th class="px-2 pb-3">{{ copy.total }}</th><th class="px-2 pb-3">{{ copy.payment }}</th><th class="px-2 pb-3">{{ copy.status }}</th><th class="px-2 pb-3"><span class="sr-only">{{ copy.detail }}</span></th></tr></thead>
                        <tbody class="divide-y divide-[var(--admin-border)]">
                            <tr v-for="order in recentOrders" :key="order.id">
                                <td class="whitespace-nowrap px-2 py-4 font-semibold">#ORDER-{{ order.id }}</td>
                                <td class="px-2 py-4"><p class="font-medium">{{ order.customer.username }}</p><p class="mt-1 text-xs text-[var(--admin-muted)]">{{ order.customer.email }}</p></td>
                                <td class="whitespace-nowrap px-2 py-4 font-semibold">{{ order.total }}</td>
                                <td class="px-2 py-4"><StatusBadge v-if="order.payment" :label="order.payment.label" :status="order.payment.status" /><span v-else class="text-xs text-[var(--admin-muted)]">—</span></td>
                                <td class="px-2 py-4"><StatusBadge :label="order.status.label" :status="order.status.value" /></td>
                                <td class="px-2 py-4 text-right"><a :href="order.url" class="font-semibold text-[var(--admin-accent)]">{{ copy.detail }}</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="recentOrders.length" class="mt-5 space-y-3 md:hidden">
                    <article v-for="order in recentOrders" :key="order.id" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                        <div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="font-semibold">#ORDER-{{ order.id }}</p><p class="mt-1 truncate text-sm text-[var(--admin-muted)]">{{ order.customer.username }} · {{ order.customer.email }}</p></div><a :href="order.url" class="shrink-0 text-sm font-semibold text-[var(--admin-accent)]">{{ copy.detail }}</a></div>
                        <p class="mt-4 text-lg font-semibold">{{ order.total }}</p>
                        <div class="mt-3 flex flex-wrap gap-2"><StatusBadge v-if="order.payment" :label="order.payment.label" :status="order.payment.status" /><StatusBadge :label="order.status.label" :status="order.status.value" /></div>
                    </article>
                </div>

                <div v-else class="mt-5 rounded-xl border border-dashed border-[var(--admin-border)] p-6 text-center text-sm text-[var(--admin-muted)]">{{ copy.noRecentOrders }}</div>
            </section>
        </template>
    </AdminLayout>
</template>
