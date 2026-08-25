<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import PaginationControls from '../../../Components/Admin/PaginationControls.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type { AdminPlaceListItem, AdminPlacesCopy, AdminPlacesResult, AdminPlacesRoutes } from '../../../types/adminPlaces';
import type { SharedPageProps } from '../../../types/inertia';

defineOptions({ name: 'AdminPlacesPage' });

defineProps<{
    copy: AdminPlacesCopy;
    places: AdminPlacesResult;
    routes: AdminPlacesRoutes;
}>();

const page = usePage<SharedPageProps>();
const deleteForm = useForm({});
const deletingId = ref<number | null>(null);

const destroy = (place: AdminPlaceListItem): void => {
    if (!window.confirm(place.deleteConfirmation)) return;

    deletingId.value = place.id;
    deleteForm.delete(place.deleteUrl, {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
        },
    });
};
</script>

<template>
    <Head :title="copy.title" />

    <AdminLayout active-navigation="places" :copy="copy" :routes="routes">
        <header class="flex flex-col gap-5 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ copy.title }}</h1>
                <p class="mt-2 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.description }}</p>
            </div>
            <a :href="routes.createPlace" class="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-accent)] px-4 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)]">
                + {{ copy.add }}
            </a>
        </header>

        <div v-if="page.props.flash.success" class="mt-6 rounded-xl border border-[var(--admin-success)]/25 bg-[var(--admin-success-soft)] px-4 py-3 text-sm font-medium text-[var(--admin-success)]" role="status">
            {{ page.props.flash.success }}
        </div>

        <section class="mt-6 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)]" aria-labelledby="places-list-heading">
            <div class="border-b border-[var(--admin-border)] px-5 py-4 sm:px-6">
                <h2 id="places-list-heading" class="text-base font-semibold">{{ copy.resultsTitle }}</h2>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.resultsDescription }}</p>
            </div>

            <div v-if="places.data.length" class="hidden overflow-x-auto md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[var(--admin-border)] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">
                            <th class="px-6 py-3">{{ copy.image }}</th>
                            <th class="px-4 py-3">{{ copy.name }}</th>
                            <th class="px-4 py-3">{{ copy.address }}</th>
                            <th class="px-6 py-3 text-right">{{ copy.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--admin-border)]">
                        <tr v-for="place in places.data" :key="place.id">
                            <td class="px-6 py-4">
                                <div class="h-16 w-20 overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)]">
                                    <img :alt="place.name" class="h-full w-full object-cover" :src="place.imageUrl">
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold">{{ place.name }}</p>
                                <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ place.reference }}</p>
                            </td>
                            <td class="max-w-md px-4 py-4 text-[var(--admin-muted)]">{{ place.address }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3 text-sm font-semibold">
                                    <a :href="place.editUrl" class="text-[var(--admin-muted)] transition-colors hover:text-[var(--admin-accent)]">{{ copy.edit }}</a>
                                    <button
                                        :aria-busy="deletingId === place.id"
                                        class="text-[var(--admin-danger)] transition-colors hover:opacity-75 disabled:cursor-wait disabled:opacity-60"
                                        :disabled="deleteForm.processing"
                                        type="button"
                                        @click="destroy(place)"
                                    >
                                        {{ deletingId === place.id ? copy.deleting : copy.delete }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="places.data.length" class="space-y-3 p-4 md:hidden">
                <article v-for="place in places.data" :key="place.id" class="min-w-0 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="h-20 w-24 shrink-0 overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)]">
                            <img :alt="place.name" class="h-full w-full object-cover" :src="place.imageUrl">
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ place.name }}</p>
                            <p class="mt-1 line-clamp-2 break-words text-sm leading-5 text-[var(--admin-muted)]">{{ place.address }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-4 border-t border-[var(--admin-border)] pt-3 text-sm font-semibold">
                        <a :href="place.editUrl" class="text-[var(--admin-muted)]">{{ copy.edit }}</a>
                        <button
                            :aria-busy="deletingId === place.id"
                            class="text-[var(--admin-danger)] disabled:cursor-wait disabled:opacity-60"
                            :disabled="deleteForm.processing"
                            type="button"
                            @click="destroy(place)"
                        >
                            {{ deletingId === place.id ? copy.deleting : copy.delete }}
                        </button>
                    </div>
                </article>
            </div>

            <div v-else class="px-6 py-12 text-center">
                <p class="text-sm font-semibold">{{ copy.emptyTitle }}</p>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.emptyDescription }}</p>
            </div>

            <div v-if="places.pagination.total > 0" class="border-t border-[var(--admin-border)] px-4 py-4 sm:px-6">
                <PaginationControls
                    :next-label="copy.next"
                    :next-url="places.pagination.nextUrl"
                    :pages="places.pagination.pages"
                    :previous-label="copy.previous"
                    :previous-url="places.pagination.previousUrl"
                    :summary="places.pagination.summary"
                />
            </div>
        </section>
    </AdminLayout>
</template>
