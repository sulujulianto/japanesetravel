<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

import StatusBadge from '../../../Components/Admin/StatusBadge.vue';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type {
    AdminOrderDetail,
    AdminOrderDetailCopy,
    AdminOrderDetailRoutes,
    AdminOrderStatusOption,
} from '../../../types/adminOrderDetail';
import type { SharedPageProps } from '../../../types/inertia';

defineOptions({ name: 'AdminOrderDetailPage' });

const props = defineProps<{
    copy: AdminOrderDetailCopy;
    order: AdminOrderDetail;
    routes: AdminOrderDetailRoutes;
    statusOptions: AdminOrderStatusOption[];
}>();

const page = usePage<SharedPageProps>();
const form = useForm({
    admin_note: props.order.adminNote ?? '',
    status: props.order.status.value,
});

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

watch(
    () => [props.order.adminNote, props.order.status.value] as const,
    ([adminNote, status]) => {
        form.defaults({ admin_note: adminNote ?? '', status });
        form.reset();
    },
);

const submit = (): void => {
    form.put(props.routes.updateOrder, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`${copy.title} ${order.reference}`" />

    <AdminLayout active-navigation="orders" :copy="copy" :routes="routes">
        <header class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6">
            <Link :href="routes.orders" class="text-sm font-semibold text-[var(--admin-muted)] transition-colors hover:text-[var(--admin-accent)]">
                ← {{ copy.back }}
            </Link>
            <p class="mt-5 text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">{{ order.reference }}</h1>
                    <p class="mt-2 text-sm text-[var(--admin-muted)]">{{ copy.createdOn }} {{ order.createdAt }}</p>
                </div>
                <p class="text-2xl font-semibold tracking-tight">{{ order.total }}</p>
            </div>
        </header>

        <div v-if="page.props.flash.success" class="mt-6 rounded-xl border border-[var(--admin-success)]/25 bg-[var(--admin-success-soft)] px-4 py-3 text-sm font-medium text-[var(--admin-success)]" role="status">
            {{ page.props.flash.success }}
        </div>
        <div v-if="page.props.flash.error" class="mt-6 rounded-xl border border-[var(--admin-danger)]/25 bg-[var(--admin-danger-soft)] px-4 py-3 text-sm font-medium text-[var(--admin-danger)]" role="alert">
            {{ page.props.flash.error }}
        </div>
        <div v-if="hasErrors" class="mt-6 rounded-xl border border-[var(--admin-danger)]/25 bg-[var(--admin-danger-soft)] px-4 py-3 text-sm text-[var(--admin-danger)]" role="alert">
            <p class="font-semibold">{{ copy.formError }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-4">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <div class="mt-6 grid items-start gap-6 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="order-summary-heading">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 id="order-summary-heading" class="text-lg font-semibold">{{ copy.summaryTitle }}</h2>
                            <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.summaryDescription }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <StatusBadge :label="order.status.label" :status="order.status.value" />
                            <StatusBadge v-if="order.latestPayment" :label="order.latestPayment.label" :status="order.latestPayment.status" />
                            <StatusBadge v-else :label="copy.noPayment" status="unpaid" />
                        </div>
                    </div>

                    <dl class="mt-5 grid gap-4 border-t border-[var(--admin-border)] pt-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.customer }}</dt>
                            <dd class="mt-2 font-semibold">{{ order.customer.username }}</dd>
                            <dd class="mt-1 break-all text-sm text-[var(--admin-muted)]">{{ order.customer.email || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.orderTime }}</dt>
                            <dd class="mt-2 font-semibold">{{ order.createdAt }}</dd>
                            <dd class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.total }}: {{ order.total }}</dd>
                        </div>
                    </dl>

                    <div v-if="order.note || order.adminNote" class="mt-5 grid gap-4 border-t border-[var(--admin-border)] pt-5 sm:grid-cols-2">
                        <div v-if="order.note">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.orderNote }}</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6">{{ order.note }}</p>
                        </div>
                        <div v-if="order.adminNote">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.adminNote }}</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6">{{ order.adminNote }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="shipping-address-heading">
                    <h2 id="shipping-address-heading" class="text-lg font-semibold">{{ copy.shippingTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.shippingDescription }}</p>

                    <div v-if="order.shippingAddress" class="mt-5 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="font-semibold">{{ order.shippingAddress.recipientName }}</p>
                                <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ order.shippingAddress.recipientPhone }}</p>
                            </div>
                            <span class="w-fit rounded-full border border-[var(--admin-border)] bg-[var(--admin-surface)] px-3 py-1 text-xs font-semibold text-[var(--admin-muted)]">{{ order.shippingAddress.label }}</span>
                        </div>
                        <address class="mt-4 text-sm not-italic leading-6">
                            {{ order.shippingAddress.addressLine1 }}<br>
                            <template v-if="order.shippingAddress.addressLine2">{{ order.shippingAddress.addressLine2 }}<br></template>
                            {{ order.shippingAddress.city }}, {{ order.shippingAddress.province }} {{ order.shippingAddress.postalCode }}<br>
                            {{ order.shippingAddress.country }}
                        </address>
                    </div>
                    <p v-else class="mt-5 rounded-xl border border-dashed border-[var(--admin-border)] p-6 text-center text-sm text-[var(--admin-muted)]">{{ copy.shippingMissing }}</p>
                </section>

                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="order-items-heading">
                    <h2 id="order-items-heading" class="text-lg font-semibold">{{ copy.itemsTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.itemsDescription }}</p>

                    <div v-if="order.items.length" class="mt-5 space-y-3">
                        <article v-for="item in order.items" :key="item.id" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                            <div class="flex items-start gap-3 sm:items-center sm:gap-4">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)]">
                                    <img v-if="item.imageUrl" :alt="item.name" class="h-full w-full object-cover" :src="item.imageUrl">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold">{{ item.name }}</p>
                                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ item.quantity }} × {{ item.unitPrice }}</p>
                                </div>
                                <p class="hidden shrink-0 text-sm font-semibold sm:block">{{ item.subtotal }}</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-[var(--admin-border)] pt-3 text-sm sm:hidden">
                                <span class="text-[var(--admin-muted)]">{{ copy.subtotal }}</span>
                                <span class="font-semibold">{{ item.subtotal }}</span>
                            </div>
                        </article>
                    </div>
                    <p v-else class="mt-5 rounded-xl border border-dashed border-[var(--admin-border)] p-6 text-center text-sm text-[var(--admin-muted)]">{{ copy.emptyItems }}</p>
                </section>

                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="payment-history-heading">
                    <h2 id="payment-history-heading" class="text-lg font-semibold">{{ copy.paymentTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.paymentDescription }}</p>

                    <div v-if="order.payments.length" class="mt-5 space-y-3">
                        <article v-for="payment in order.payments" :key="payment.id" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold">{{ payment.provider }}</p>
                                        <StatusBadge :label="payment.status.label" :status="payment.status.value" />
                                    </div>
                                    <p class="mt-2 break-all text-xs text-[var(--admin-muted)]">{{ payment.reference || copy.referenceUnavailable }}</p>
                                </div>
                                <div class="sm:text-right">
                                    <p class="font-semibold">{{ payment.amount }}</p>
                                    <p class="mt-1 text-xs text-[var(--admin-muted)]">{{ payment.paidAt || copy.notPaid }}</p>
                                </div>
                            </div>
                        </article>
                    </div>
                    <div v-else class="mt-5 rounded-xl border border-dashed border-[var(--admin-border)] p-6 text-center">
                        <p class="text-sm font-semibold">{{ copy.emptyPaymentsTitle }}</p>
                        <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.emptyPaymentsDescription }}</p>
                    </div>
                </section>
            </div>

            <aside class="lg:col-span-4">
                <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6 lg:sticky lg:top-24" aria-labelledby="update-status-heading">
                    <h2 id="update-status-heading" class="text-lg font-semibold">{{ copy.updateTitle }}</h2>
                    <p class="mt-1 text-sm leading-6 text-[var(--admin-muted)]">{{ copy.updateDescription }}</p>

                    <form class="mt-5 space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="status" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.status }}</label>
                            <select id="status" v-model="form.status" :aria-describedby="form.errors.status ? 'status-error' : undefined" :aria-invalid="Boolean(form.errors.status)" class="admin-order-field mt-2" name="status">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                            </select>
                            <p v-if="form.errors.status" id="status-error" class="mt-2 text-sm font-medium text-[var(--admin-danger)]">{{ form.errors.status }}</p>
                        </div>
                        <div>
                            <label for="admin-note" class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--admin-muted)]">{{ copy.adminNote }}</label>
                            <textarea id="admin-note" v-model="form.admin_note" :aria-describedby="form.errors.admin_note ? 'admin-note-help admin-note-error' : 'admin-note-help'" :aria-invalid="Boolean(form.errors.admin_note)" class="admin-order-field mt-2 min-h-32 resize-y leading-6" maxlength="500" name="admin_note" />
                            <p id="admin-note-help" class="mt-2 text-xs leading-5 text-[var(--admin-muted)]">{{ copy.internalNoteHelp }}</p>
                            <p v-if="form.errors.admin_note" id="admin-note-error" class="mt-2 text-sm font-medium text-[var(--admin-danger)]">{{ form.errors.admin_note }}</p>
                        </div>
                        <button :aria-busy="form.processing" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[var(--admin-accent)] px-4 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)] disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing" type="submit">
                            {{ form.processing ? `${copy.saving}…` : copy.save }}
                        </button>
                    </form>
                </section>
            </aside>
        </div>
    </AdminLayout>
</template>
