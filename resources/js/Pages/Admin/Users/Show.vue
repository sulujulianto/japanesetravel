<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type { AdminUserDetail, AdminUserDetailCopy, AdminUserDetailRoutes } from '../../../types/adminUsers';

defineOptions({ name: 'AdminUserDetailPage' });

defineProps<{
    account: AdminUserDetail;
    copy: AdminUserDetailCopy;
    routes: AdminUserDetailRoutes;
}>();
</script>

<template>
    <Head :title="`${copy.title} · ${account.username}`" />

    <AdminLayout active-navigation="users" :copy="copy" :routes="routes">
        <header class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6">
            <Link :href="routes.users" class="text-sm font-semibold text-[var(--admin-muted)] transition-colors hover:text-[var(--admin-accent)]">← {{ copy.back }}</Link>
            <p class="mt-5 text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ account.username }}</h1>
                    <p class="mt-2 break-all text-sm text-[var(--admin-muted)]">{{ account.email }}</p>
                </div>
                <span :class="account.verification.verified ? 'bg-[var(--admin-success-soft)] text-[var(--admin-success)]' : 'bg-[var(--admin-warning-soft)] text-[var(--admin-warning)]'" class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-semibold">
                    {{ account.verification.label }}
                </span>
            </div>
        </header>

        <div class="mt-6 rounded-xl border border-[var(--admin-warning)]/25 bg-[var(--admin-warning-soft)] px-4 py-3 text-sm leading-6 text-[var(--admin-warning)]" role="note">
            {{ copy.privacyNotice }}
        </div>

        <div class="mt-6 grid items-start gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="user-account-heading">
                    <h2 id="user-account-heading" class="text-lg font-semibold">{{ copy.accountTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.accountDescription }}</p>
                    <dl class="mt-5 grid gap-4 border-t border-[var(--admin-border)] pt-5 sm:grid-cols-2">
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.username }}</dt><dd class="mt-2 font-semibold">{{ account.username }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.email }}</dt><dd class="mt-2 break-all font-semibold">{{ account.email }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.joined }}</dt><dd class="mt-2">{{ account.joinedAt }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.lastSeen }}</dt><dd class="mt-2">{{ account.lastSeenAt }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.verification }}</dt><dd class="mt-2 font-semibold">{{ account.verification.label }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.verifiedAt }}</dt><dd class="mt-2">{{ account.verification.verifiedAt }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="user-profile-heading">
                    <h2 id="user-profile-heading" class="text-lg font-semibold">{{ copy.profileTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.profileDescription }}</p>
                    <dl class="mt-5 grid gap-4 border-t border-[var(--admin-border)] pt-5 sm:grid-cols-2">
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.fullName }}</dt><dd class="mt-2 font-semibold">{{ account.profile.fullName || copy.notProvided }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.phone }}</dt><dd class="mt-2 font-semibold">{{ account.profile.phone || copy.notProvided }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.preferredLocale }}</dt><dd class="mt-2 font-semibold">{{ account.profile.preferredLocale?.label || copy.notProvided }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="user-addresses-heading">
                    <h2 id="user-addresses-heading" class="text-lg font-semibold">{{ copy.addressesTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.addressesDescription }}</p>

                    <div v-if="account.addresses.length" class="mt-5 space-y-4">
                        <article v-for="address in account.addresses" :key="address.id" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4 sm:p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold">{{ address.label }}</h3>
                                <span v-if="address.isDefault" class="rounded-full bg-[var(--admin-success-soft)] px-2.5 py-1 text-xs font-semibold text-[var(--admin-success)]">{{ copy.defaultAddress }}</span>
                            </div>
                            <dl class="mt-4 grid gap-4 border-t border-[var(--admin-border)] pt-4 sm:grid-cols-2">
                                <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.recipient }}</dt><dd class="mt-2 font-semibold">{{ address.recipientName }}</dd><dd class="mt-1 text-sm text-[var(--admin-muted)]">{{ address.recipientPhone }}</dd></div>
                                <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.cityProvince }}</dt><dd class="mt-2 font-semibold">{{ address.city }}, {{ address.province }} {{ address.postalCode }}</dd><dd class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.country }}: {{ address.country }} ({{ address.countryCode }})</dd></div>
                                <div class="sm:col-span-2"><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.addressesTitle }}</dt><dd class="mt-2 whitespace-pre-line leading-6">{{ address.addressLine1 }}<template v-if="address.addressLine2"><br>{{ address.addressLine2 }}</template></dd></div>
                            </dl>
                        </article>
                    </div>
                    <p v-else class="mt-5 rounded-xl border border-dashed border-[var(--admin-border)] p-6 text-center text-sm text-[var(--admin-muted)]">{{ copy.noAddresses }}</p>
                </section>
            </div>

            <aside class="xl:col-span-4">
                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6 xl:sticky xl:top-24" aria-labelledby="user-orders-heading">
                    <h2 id="user-orders-heading" class="text-lg font-semibold">{{ copy.ordersTitle }}</h2>
                    <p class="mt-1 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.ordersDescription }}</p>
                    <dl class="mt-5 grid grid-cols-2 gap-4 border-y border-[var(--admin-border)] py-5">
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.orderCount }}</dt><dd class="mt-2 text-2xl font-semibold">{{ account.orderSummary.count }}</dd></div>
                        <div><dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.spent }}</dt><dd class="mt-2 text-lg font-semibold">{{ account.orderSummary.spent }}</dd></div>
                    </dl>
                    <Link :href="routes.ordersForUser" class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[var(--admin-accent)] px-4 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)]">{{ copy.viewOrders }}</Link>
                </section>
            </aside>
        </div>
    </AdminLayout>
</template>
