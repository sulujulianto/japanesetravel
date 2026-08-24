<script setup lang="ts">
import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Filler,
    LineController,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
} from 'chart.js';
import { onBeforeUnmount, shallowRef, watch } from 'vue';

import type { Theme } from '../../composables/useTheme';

Chart.register(CategoryScale, LinearScale, LineController, LineElement, PointElement, BarController, BarElement, Tooltip, Filler);

defineOptions({ name: 'DashboardChart' });

const props = defineProps<{
    emptyMessage: string;
    kind: 'bar' | 'line';
    label: string;
    labels: string[];
    locale: string;
    series: number[];
    theme: Theme;
    valueFormat: 'currency' | 'number';
}>();

const canvas = shallowRef<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const formatValue = (value: number): string => new Intl.NumberFormat(props.locale, props.valueFormat === 'currency'
    ? { currency: 'IDR', maximumFractionDigits: 0, style: 'currency' }
    : { maximumFractionDigits: 0 }).format(value || 0);

const renderChart = (): void => {
    chart?.destroy();
    chart = null;

    if (!canvas.value || !props.series.some((value) => Number(value) > 0)) return;

    const dark = props.theme === 'dark';
    const text = dark ? '#AEB8C7' : '#667085';
    const grid = dark ? 'rgba(174,184,199,0.10)' : 'rgba(102,112,133,0.12)';

    chart = new Chart(canvas.value, {
        type: props.kind,
        data: {
            labels: props.labels,
            datasets: [{
                label: props.label,
                data: props.series,
                backgroundColor: props.kind === 'line' ? 'rgba(168, 58, 53, 0.10)' : 'rgba(47, 93, 80, 0.72)',
                borderColor: props.kind === 'line' ? '#A83A35' : '#2F5D50',
                borderRadius: props.kind === 'bar' ? 5 : undefined,
                borderWidth: props.kind === 'line' ? 2 : 0,
                fill: props.kind === 'line',
                pointHoverRadius: props.kind === 'line' ? 4 : undefined,
                pointRadius: props.kind === 'line' ? 2 : undefined,
                tension: props.kind === 'line' ? 0.32 : undefined,
            }],
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const parsed = context.parsed;
                            const value = typeof parsed === 'number' ? parsed : parsed.y;

                            return formatValue(Number(value ?? 0));
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { autoSkip: true, color: text, maxRotation: 0 },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: grid },
                    ticks: { color: text, precision: props.valueFormat === 'number' ? 0 : undefined, callback: (value) => formatValue(Number(value)) },
                },
            },
        },
    });
};

watch(
    [canvas, () => props.labels, () => props.series, () => props.locale, () => props.theme],
    renderChart,
    { deep: true, flush: 'post' },
);

onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div class="h-72 min-w-[36rem] sm:min-w-0">
        <canvas v-show="series.some((value) => Number(value) > 0)" ref="canvas" />
        <div v-if="!series.some((value) => Number(value) > 0)" class="flex h-full items-center justify-center rounded-xl border border-dashed border-[var(--admin-border)] px-4 text-center text-sm text-[var(--admin-muted)]">
            {{ emptyMessage }}
        </div>
    </div>
</template>
