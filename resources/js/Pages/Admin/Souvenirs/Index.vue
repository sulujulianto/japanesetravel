<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import PaginationControls from '../../../Components/Admin/PaginationControls.vue';
import StockBadge from '../../../Components/Admin/StockBadge.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type {
    AdminSouvenirListItem,
    AdminSouvenirsCopy,
    AdminSouvenirsResult,
    AdminSouvenirsRoutes,
} from '../../../types/adminSouvenirs';
import type { SharedPageProps } from '../../../types/inertia';

defineOptions({ name: 'AdminSouvenirsPage' });

defineProps<{
    copy: AdminSouvenirsCopy;
    routes: AdminSouvenirsRoutes;
    souvenirs: AdminSouvenirsResult;
}>();

const page = usePage<SharedPageProps>();
const deleteForm = useForm({});
const deletingId = ref<number | null>(null);

const destroy = (souvenir: AdminSouvenirListItem): void => {
    if (!window.confirm(souvenir.deleteConfirmation)) return;

    deletingId.value = souvenir.id;
    deleteForm.delete(souvenir.deleteUrl, {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
        },
    });
};
</script>

<template>
    <Head :title="copy.title" />

    <AdminLayout active-navigation="souvenirs" :copy="copy" :routes="routes">
        <header class="flex flex-col gap-5 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ copy.title }}</h1>
                <p class="mt-2 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.description }}</p>
            </div>
            <Link :href="routes.createSouvenir" class="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-accent)] px-4 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)]">
                + {{ copy.add }}
            </Link>
        </header>

        <div v-if="page.props.flash.success" class="mt-6 rounded-xl border border-[var(--admin-success)]/25 bg-[var(--admin-success-soft)] px-4 py-3 text-sm font-medium text-[var(--admin-success)]" role="status">
            {{ page.props.flash.success }}
        </div>

        <section class="mt-6 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)]" aria-labelledby="souvenirs-list-heading">
            <div class="border-b border-[var(--admin-border)] px-5 py-4 sm:px-6">
                <h2 id="souvenirs-list-heading" class="text-base font-semibold">{{ copy.resultsTitle }}</h2>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.resultsDescription }}</p>
            </div>

            <div v-if="souvenirs.data.length" class="hidden overflow-x-auto md:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[var(--admin-border)] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">
                            <th class="px-6 py-3">{{ copy.image }}</th>
                            <th class="px-4 py-3">{{ copy.name }}</th>
                            <th class="px-4 py-3">{{ copy.price }}</th>
                            <th class="px-4 py-3">{{ copy.stock }}</th>
                            <th class="px-6 py-3 text-right">{{ copy.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--admin-border)]">
                        <tr v-for="souvenir in souvenirs.data" :key="souvenir.id">
                            <td class="px-6 py-4">
                                <div class="h-16 w-16 overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)]">
                                    <img :alt="souvenir.name" class="h-full w-full object-cover" :src="souvenir.imageUrl">
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold">{{ souvenir.name }}</p>
                                <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ souvenir.reference }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 font-semibold">{{ souvenir.price }}</td>
                            <td class="px-4 py-4">
                                <StockBadge :count="souvenir.stockCount" :label="souvenir.stockLabel" :status="souvenir.stockStatus" />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3 text-sm font-semibold">
                                    <Link :href="souvenir.editUrl" class="text-[var(--admin-muted)] transition-colors hover:text-[var(--admin-accent)]">{{ copy.edit }}</Link>
                                    <button
                                        :aria-busy="deletingId === souvenir.id"
                                        class="text-[var(--admin-danger)] transition-colors hover:opacity-75 disabled:cursor-wait disabled:opacity-60"
                                        :disabled="deleteForm.processing"
                                        type="button"
                                        @click="destroy(souvenir)"
                                    >
                                        {{ deletingId === souvenir.id ? copy.deleting : copy.delete }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="souvenirs.data.length" class="space-y-3 p-4 md:hidden">
                <article v-for="souvenir in souvenirs.data" :key="souvenir.id" class="min-w-0 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)]">
                            <img :alt="souvenir.name" class="h-full w-full object-cover" :src="souvenir.imageUrl">
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ souvenir.name }}</p>
                            <p class="mt-1 text-sm font-semibold">{{ souvenir.price }}</p>
                            <div class="mt-2">
                                <StockBadge :count="souvenir.stockCount" :label="souvenir.stockLabel" :status="souvenir.stockStatus" />
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-end gap-4 border-t border-[var(--admin-border)] pt-3 text-sm font-semibold">
                        <Link :href="souvenir.editUrl" class="text-[var(--admin-muted)]">{{ copy.edit }}</Link>
                        <button
                            :aria-busy="deletingId === souvenir.id"
                            class="text-[var(--admin-danger)] disabled:cursor-wait disabled:opacity-60"
                            :disabled="deleteForm.processing"
                            type="button"
                            @click="destroy(souvenir)"
                        >
                            {{ deletingId === souvenir.id ? copy.deleting : copy.delete }}
                        </button>
                    </div>
                </article>
            </div>

            <div v-else class="px-6 py-12 text-center">
                <p class="text-sm font-semibold">{{ copy.emptyTitle }}</p>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.emptyDescription }}</p>
            </div>

            <div v-if="souvenirs.pagination.total > 0" class="border-t border-[var(--admin-border)] px-4 py-4 sm:px-6">
                <PaginationControls
                    :next-label="copy.next"
                    :next-url="souvenirs.pagination.nextUrl"
                    :pages="souvenirs.pagination.pages"
                    :previous-label="copy.previous"
                    :previous-url="souvenirs.pagination.previousUrl"
                    :summary="souvenirs.pagination.summary"
                />
            </div>
        </section>
    </AdminLayout>
</template>
