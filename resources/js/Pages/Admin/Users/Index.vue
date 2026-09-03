<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

import PaginationControls from '../../../Components/Admin/PaginationControls.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type {
    AdminUserFilters,
    AdminUserListCopy,
    AdminUserListRoutes,
    AdminUserOptions,
    AdminUsersResult,
} from '../../../types/adminUsers';

defineOptions({ name: 'AdminUsersPage' });

const props = defineProps<{
    copy: AdminUserListCopy;
    filters: AdminUserFilters;
    options: AdminUserOptions;
    routes: AdminUserListRoutes;
    users: AdminUsersResult;
}>();

const form = reactive({
    search: props.filters.search,
    verification: props.filters.verification,
});

const submit = (): void => {
    router.get(props.routes.users, {
        q: form.search || undefined,
        verification: form.verification || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const reset = (): void => {
    Object.assign(form, {
        search: '',
        verification: '',
    });
    router.get(props.routes.users, {}, { preserveState: true, replace: true });
};
</script>

<template>
    <Head :title="copy.title" />

    <AdminLayout active-navigation="users" :copy="copy" :routes="routes">
        <header class="relative overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-5 py-6 sm:px-7">
            <span aria-hidden="true" class="absolute inset-y-0 left-0 w-1.5 bg-[var(--admin-accent)]" />
            <div class="max-w-3xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
                <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ copy.title }}</h1>
                <p class="mt-2 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.description }}</p>
            </div>
        </header>

        <section class="mt-6 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="user-filters-heading">
            <h2 id="user-filters-heading" class="text-base font-semibold">{{ copy.filtersTitle }}</h2>
            <p class="mt-1 max-w-3xl text-sm leading-6 text-[var(--admin-muted)]">{{ copy.filtersDescription }}</p>

            <form class="mt-5 grid gap-4 md:grid-cols-[minmax(14rem,2fr)_minmax(11rem,1fr)_auto] md:items-end" @submit.prevent="submit">
                <div>
                    <label for="user-search" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.search }}</label>
                    <input id="user-search" v-model="form.search" class="admin-order-field mt-2" name="q" :placeholder="copy.searchPlaceholder">
                </div>
                <div>
                    <label for="user-verification" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.verification }}</label>
                    <select id="user-verification" v-model="form.verification" class="admin-order-field mt-2" name="verification">
                        <option value="">{{ copy.all }}</option>
                        <option v-for="option in options.verificationStatuses" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg bg-[var(--admin-accent)] px-4 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)]" type="submit">{{ copy.applyFilters }}</button>
                    <button class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg border border-[var(--admin-border)] px-4 text-sm font-semibold text-[var(--admin-muted)] transition-colors hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]" type="button" @click="reset">{{ copy.reset }}</button>
                </div>
            </form>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)]" aria-labelledby="users-list-heading">
            <div class="border-b border-[var(--admin-border)] px-5 py-4 sm:px-6">
                <h2 id="users-list-heading" class="text-base font-semibold">{{ copy.resultsTitle }}</h2>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.resultsDescription }}</p>
            </div>

            <div v-if="users.data.length" class="hidden overflow-x-auto lg:block">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[var(--admin-border)] text-left text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">
                            <th class="px-6 py-3">{{ copy.username }}</th>
                            <th class="px-4 py-3">{{ copy.fullName }}</th>
                            <th class="px-4 py-3">{{ copy.verification }}</th>
                            <th class="px-4 py-3">{{ copy.addresses }}</th>
                            <th class="px-4 py-3">{{ copy.ordersCount }}</th>
                            <th class="px-4 py-3">{{ copy.joined }}</th>
                            <th class="px-6 py-3 text-right">{{ copy.actions }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--admin-border)]">
                        <tr v-for="user in users.data" :key="user.id">
                            <td class="px-6 py-4">
                                <p class="font-semibold">{{ user.username }}</p>
                                <p class="mt-1 break-all text-xs text-[var(--admin-muted)]">{{ user.email }}</p>
                            </td>
                            <td class="px-4 py-4 text-[var(--admin-muted)]">{{ user.fullName || '—' }}</td>
                            <td class="px-4 py-4">
                                <span :class="user.verification.verified ? 'bg-[var(--admin-success-soft)] text-[var(--admin-success)]' : 'bg-[var(--admin-warning-soft)] text-[var(--admin-warning)]'" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                                    {{ user.verification.label }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-semibold">{{ user.addressCount }}</td>
                            <td class="px-4 py-4 font-semibold">{{ user.orderCount }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-[var(--admin-muted)]">{{ user.joinedAt }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="user.url" class="text-sm font-semibold text-[var(--admin-accent)] transition-colors hover:text-[var(--admin-accent-active)]">{{ copy.detail }}</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="users.data.length" class="space-y-3 p-4 lg:hidden">
                <article v-for="user in users.data" :key="user.id" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold">{{ user.username }}</p>
                            <p class="mt-1 break-all text-xs text-[var(--admin-muted)]">{{ user.email }}</p>
                        </div>
                        <Link :href="user.url" class="shrink-0 text-sm font-semibold text-[var(--admin-accent)]">{{ copy.detail }}</Link>
                    </div>
                    <p class="mt-3 text-sm text-[var(--admin-muted)]">{{ user.fullName || '—' }}</p>
                    <dl class="mt-4 grid grid-cols-3 gap-3 border-t border-[var(--admin-border)] pt-3">
                        <div><dt class="text-[10px] font-semibold uppercase tracking-[0.1em] text-[var(--admin-muted)]">{{ copy.addresses }}</dt><dd class="mt-1 font-semibold">{{ user.addressCount }}</dd></div>
                        <div><dt class="text-[10px] font-semibold uppercase tracking-[0.1em] text-[var(--admin-muted)]">{{ copy.ordersCount }}</dt><dd class="mt-1 font-semibold">{{ user.orderCount }}</dd></div>
                        <div><dt class="text-[10px] font-semibold uppercase tracking-[0.1em] text-[var(--admin-muted)]">{{ copy.joined }}</dt><dd class="mt-1 text-xs">{{ user.joinedAt }}</dd></div>
                    </dl>
                    <span :class="user.verification.verified ? 'bg-[var(--admin-success-soft)] text-[var(--admin-success)]' : 'bg-[var(--admin-warning-soft)] text-[var(--admin-warning)]'" class="mt-4 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">{{ user.verification.label }}</span>
                </article>
            </div>

            <div v-if="!users.data.length" class="px-6 py-12 text-center">
                <p class="text-sm font-semibold">{{ copy.emptyTitle }}</p>
                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.emptyDescription }}</p>
            </div>

            <div v-if="users.pagination.total > 0" class="border-t border-[var(--admin-border)] px-4 py-4 sm:px-6">
                <PaginationControls
                    :next-label="copy.next"
                    :next-url="users.pagination.nextUrl"
                    :pages="users.pagination.pages"
                    :previous-label="copy.previous"
                    :previous-url="users.pagination.previousUrl"
                    :summary="users.pagination.summary"
                />
            </div>
        </section>
    </AdminLayout>
</template>
