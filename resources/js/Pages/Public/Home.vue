<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';

import PublicLayout from '../../Layouts/PublicLayout.vue';
import type { SharedPageProps } from '../../types/inertia';
import type { FeaturedPlace, PublicHomeCopy, PublicHomeRoutes, PublicHomeSummary } from '../../types/publicHome';

defineOptions({ name: 'PublicHomePage' });

defineProps<{
    copy: PublicHomeCopy;
    featuredPlaces: FeaturedPlace[];
    routes: PublicHomeRoutes;
    summary: PublicHomeSummary;
}>();

const page = usePage<SharedPageProps>();
const formatCount = (value: number): string => new Intl.NumberFormat(page.props.locale).format(value);
</script>

<template>
    <Head :title="copy.pageTitle" />

    <PublicLayout :copy="copy" :routes="routes">
        <section class="page-hero ui-reveal">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 sm:py-16 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,0.85fr)] lg:items-center lg:gap-14 lg:px-8 lg:py-20">
                <div class="min-w-0">
                    <p class="ui-eyebrow">{{ copy.eyebrow }}</p>
                    <h1 class="ui-heading mt-4 max-w-3xl text-4xl leading-[1.08] sm:text-5xl lg:text-[3.5rem]">{{ copy.heroTitle }}</h1>
                    <p class="ui-copy mt-5 max-w-2xl text-base sm:text-lg">{{ copy.heroDescription }}</p>

                    <div class="mt-7 flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                        <a :href="routes.places" class="ui-button-primary px-6 py-3 text-sm">{{ copy.primaryCta }}</a>
                        <a :href="routes.shop" class="ui-button-quiet px-6 py-3 text-sm">{{ copy.secondaryCta }}</a>
                    </div>

                    <ul class="mt-7 flex flex-wrap gap-x-5 gap-y-3 text-sm font-medium text-[var(--public-muted)]">
                        <li v-for="item in copy.proofItems" :key="item" class="inline-flex items-center gap-2">
                            <span aria-hidden="true" class="h-1.5 w-1.5 rounded-full bg-[var(--public-secondary)]" />
                            {{ item }}
                        </li>
                    </ul>
                </div>

                <div class="ui-surface min-w-0 rounded-[22px] p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="ui-eyebrow text-[var(--public-secondary)]">{{ copy.quickLookTitle }}</p>
                            <p class="mt-1 text-sm text-[var(--public-muted)]">{{ copy.quickLookDescription }}</p>
                        </div>
                        <span class="rounded-full bg-[var(--public-secondary-soft)] px-3 py-1 text-xs font-semibold text-[var(--public-secondary)]">{{ formatCount(featuredPlaces.length) }}</span>
                    </div>

                    <div class="mt-5 divide-y divide-[var(--public-border)]">
                        <a v-for="place in featuredPlaces.slice(0, 3)" :key="place.id" :href="place.showUrl" class="flex min-w-0 items-center gap-4 py-4 first:pt-0 last:pb-0">
                            <span aria-hidden="true" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--public-accent-soft)] text-sm font-semibold text-[var(--public-accent)]">{{ place.initial }}</span>
                            <span class="min-w-0">
                                <strong class="block truncate text-sm font-semibold text-[var(--public-ink)]">{{ place.name }}</strong>
                                <span class="mt-1 block truncate text-xs text-[var(--public-muted)]">{{ place.address }}</span>
                            </span>
                        </a>
                        <p v-if="featuredPlaces.length === 0" class="py-5 text-sm text-[var(--public-muted)]">{{ copy.emptyDestinations }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8" :aria-label="copy.summaryTitle">
            <div class="ui-surface grid gap-3 rounded-[20px] p-4 sm:grid-cols-3 sm:p-5">
                <div class="rounded-xl bg-[var(--public-accent-soft)] px-5 py-4">
                    <p class="text-2xl font-semibold text-[var(--public-ink)]">{{ formatCount(summary.places) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--public-muted)]">{{ copy.summaryPlaces }}</p>
                </div>
                <div class="rounded-xl bg-[var(--public-secondary-soft)] px-5 py-4">
                    <p class="text-2xl font-semibold text-[var(--public-ink)]">{{ formatCount(summary.reviews) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--public-muted)]">{{ copy.reviews }}</p>
                </div>
                <div class="rounded-xl bg-[var(--public-surface-muted)] px-5 py-4">
                    <p class="text-2xl font-semibold text-[var(--public-ink)]">{{ formatCount(summary.souvenirs) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--public-muted)]">{{ copy.summarySouvenirs }}</p>
                </div>
            </div>
        </section>

        <section class="mx-auto mt-14 max-w-7xl px-4 sm:mt-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="ui-eyebrow">{{ copy.featuredEyebrow }}</p>
                    <h2 class="ui-heading mt-3 text-3xl sm:text-4xl">{{ copy.featuredTitle }}</h2>
                    <p class="ui-copy mt-3 text-sm">{{ copy.featuredDescription }}</p>
                </div>
                <a :href="routes.places" class="inline-flex w-fit items-center text-sm font-semibold text-[var(--public-accent)] transition hover:text-[var(--public-accent-active)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--public-focus)]">{{ copy.allDestinations }}<span aria-hidden="true" class="ml-2">→</span></a>
            </div>

            <div v-if="featuredPlaces.length" class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <a v-for="place in featuredPlaces" :key="place.id" :href="place.showUrl" class="ui-card ui-surface group flex h-full min-w-0 flex-col overflow-hidden rounded-[18px]">
                    <div class="relative h-44 overflow-hidden bg-[var(--public-surface-muted)]">
                        <img :alt="place.name" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.02]" :src="place.imageUrl" loading="lazy" decoding="async">
                        <div class="absolute left-3 top-3 rounded-lg bg-[var(--public-surface-elevated)] px-3 py-1 text-xs font-semibold text-[var(--public-ink)] shadow-sm">{{ copy.rating }} {{ place.rating }}</div>
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex min-w-0 items-start justify-between gap-3">
                            <h3 class="min-w-0 text-lg font-semibold text-[var(--public-ink)]">{{ place.name }}</h3>
                            <span class="shrink-0 text-xs font-semibold text-[var(--public-muted)]">{{ place.reviewLabel }}</span>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-[var(--public-muted)]">{{ place.excerpt }}</p>
                        <div class="mt-auto flex min-w-0 items-center justify-between gap-3 pt-4 text-xs font-medium text-[var(--public-muted)]">
                            <span class="min-w-0 truncate">{{ place.address }}</span>
                            <span aria-hidden="true" class="shrink-0 text-[var(--public-accent)]">→</span>
                        </div>
                    </div>
                </a>
            </div>
            <div v-else class="ui-surface mt-7 rounded-[20px] p-8 text-center text-sm text-[var(--public-muted)]">{{ copy.emptyDestinations }}</div>
        </section>

        <section class="mx-auto mt-14 max-w-7xl px-4 sm:mt-16 sm:px-6 lg:px-8">
            <div class="grid gap-6 rounded-[22px] border border-[var(--public-border)] bg-[var(--public-secondary-soft)] px-6 py-8 sm:px-8 sm:py-10 lg:grid-cols-[1fr_auto] lg:items-center">
                <div class="max-w-2xl">
                    <p class="ui-eyebrow text-[var(--public-secondary)]">{{ copy.souvenirEyebrow }}</p>
                    <h2 class="ui-heading mt-3 text-2xl sm:text-3xl">{{ copy.souvenirTitle }}</h2>
                    <p class="ui-copy mt-3 text-sm">{{ copy.souvenirDescription }}</p>
                </div>
                <a :href="routes.shop" class="ui-button-secondary w-full px-6 py-3 text-sm sm:w-fit">{{ copy.souvenirCta }}</a>
            </div>
        </section>

        <aside class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="ui-surface-muted rounded-xl px-4 py-3 text-center text-xs leading-5 text-[var(--public-muted)]">{{ copy.portfolioNote }}</p>
        </aside>
    </PublicLayout>
</template>
