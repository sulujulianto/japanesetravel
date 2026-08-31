<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type {
    AdminSouvenirFormCopy,
    AdminSouvenirFormInitialValues,
    AdminSouvenirFormMode,
    AdminSouvenirFormRoutes,
} from '../../../types/adminSouvenirForm';

defineOptions({ name: 'AdminSouvenirFormPage' });

const props = defineProps<{
    copy: AdminSouvenirFormCopy;
    initialValues: AdminSouvenirFormInitialValues;
    mode: AdminSouvenirFormMode;
    routes: AdminSouvenirFormRoutes;
}>();

const form = useForm({
    _method: props.mode === 'edit' ? 'put' : 'post',
    description_en: props.initialValues.descriptionEn,
    description_id: props.initialValues.descriptionId,
    image: null as File | null,
    name_en: props.initialValues.nameEn,
    name_id: props.initialValues.nameId,
    price: props.initialValues.price,
    stock: props.initialValues.stock,
});

const hasErrors = computed(() => Object.keys(form.errors).length > 0);
const labelClass = 'block text-sm font-semibold';
const inputClass = 'mt-2 w-full rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-2.5 text-sm text-[var(--admin-ink)] outline-none transition placeholder:text-[var(--admin-muted)] focus:border-[var(--admin-accent)] focus:ring-2 focus:ring-[var(--admin-accent)]/20 disabled:cursor-not-allowed disabled:opacity-60';
const errorClass = 'mt-2 text-sm font-medium text-[var(--admin-danger)]';
const helpClass = 'mt-2 text-xs leading-5 text-[var(--admin-muted)]';

const selectImage = (event: Event): void => {
    const input = event.currentTarget as HTMLInputElement;
    form.image = input.files?.[0] ?? null;
};

const submit = (): void => {
    form.post(props.routes.submitSouvenir, {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="copy.title" />

    <AdminLayout active-navigation="souvenirs" :copy="copy" :routes="routes">
        <header class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[var(--admin-muted)]">{{ copy.eyebrow }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ copy.title }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[var(--admin-muted)]">{{ copy.description }}</p>
        </header>

        <div v-if="hasErrors" class="mt-6 rounded-xl border border-[var(--admin-danger)]/25 bg-[var(--admin-danger-soft)] px-4 py-3 text-sm text-[var(--admin-danger)]" role="alert">
            <p class="font-semibold">{{ copy.formError }}</p>
            <ul class="mt-2 list-disc space-y-1 pl-4">
                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
            </ul>
        </div>

        <form class="mt-6 space-y-6" enctype="multipart/form-data" @submit.prevent="submit">
            <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="souvenir-id-content">
                <div class="border-b border-[var(--admin-border)] pb-4">
                    <h2 id="souvenir-id-content" class="text-lg font-semibold">{{ copy.idContentTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.idContentDescription }}</p>
                </div>
                <div class="mt-5 grid gap-5">
                    <div>
                        <label for="name-id" :class="labelClass">{{ copy.nameId }}</label>
                        <input id="name-id" v-model="form.name_id" :aria-describedby="form.errors.name_id ? 'name-id-error' : undefined" :aria-invalid="Boolean(form.errors.name_id)" autocomplete="off" :class="inputClass" maxlength="255" name="name_id" required>
                        <p v-if="form.errors.name_id" id="name-id-error" :class="errorClass">{{ form.errors.name_id }}</p>
                    </div>
                    <div>
                        <label for="description-id" :class="labelClass">{{ copy.descriptionId }}</label>
                        <textarea id="description-id" v-model="form.description_id" :aria-describedby="form.errors.description_id ? 'description-id-help description-id-error' : 'description-id-help'" :aria-invalid="Boolean(form.errors.description_id)" autocomplete="off" :class="inputClass" name="description_id" required rows="6"></textarea>
                        <p id="description-id-help" :class="helpClass">{{ copy.descriptionHelp }}</p>
                        <p v-if="form.errors.description_id" id="description-id-error" :class="errorClass">{{ form.errors.description_id }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="souvenir-en-content">
                <div class="border-b border-[var(--admin-border)] pb-4">
                    <h2 id="souvenir-en-content" class="text-lg font-semibold">{{ copy.enContentTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.enContentDescription }}</p>
                </div>
                <div class="mt-5 grid gap-5">
                    <div>
                        <label for="name-en" :class="labelClass">{{ copy.nameEn }}</label>
                        <input id="name-en" v-model="form.name_en" :aria-describedby="form.errors.name_en ? 'name-en-error' : undefined" :aria-invalid="Boolean(form.errors.name_en)" autocomplete="off" :class="inputClass" maxlength="255" name="name_en" required>
                        <p v-if="form.errors.name_en" id="name-en-error" :class="errorClass">{{ form.errors.name_en }}</p>
                    </div>
                    <div>
                        <label for="description-en" :class="labelClass">{{ copy.descriptionEn }}</label>
                        <textarea id="description-en" v-model="form.description_en" :aria-describedby="form.errors.description_en ? 'description-en-error' : undefined" :aria-invalid="Boolean(form.errors.description_en)" autocomplete="off" :class="inputClass" name="description_en" required rows="6"></textarea>
                        <p v-if="form.errors.description_en" id="description-en-error" :class="errorClass">{{ form.errors.description_en }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="souvenir-sales-information">
                <div class="border-b border-[var(--admin-border)] pb-4">
                    <h2 id="souvenir-sales-information" class="text-lg font-semibold">{{ copy.salesTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.salesDescription }}</p>
                </div>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="price" :class="labelClass">{{ copy.price }}</label>
                        <input id="price" v-model="form.price" :aria-describedby="form.errors.price ? 'price-help price-error' : 'price-help'" :aria-invalid="Boolean(form.errors.price)" autocomplete="off" :class="inputClass" min="0" name="price" required step="0.01" type="number">
                        <p id="price-help" :class="helpClass">{{ copy.priceHelp }}</p>
                        <p v-if="form.errors.price" id="price-error" :class="errorClass">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <label for="stock" :class="labelClass">{{ copy.stock }}</label>
                        <input id="stock" v-model="form.stock" :aria-describedby="form.errors.stock ? 'stock-help stock-error' : 'stock-help'" :aria-invalid="Boolean(form.errors.stock)" autocomplete="off" :class="inputClass" min="0" name="stock" required step="1" type="number">
                        <p id="stock-help" :class="helpClass">{{ copy.stockHelp }}</p>
                        <p v-if="form.errors.stock" id="stock-error" :class="errorClass">{{ form.errors.stock }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="souvenir-media">
                <div class="border-b border-[var(--admin-border)] pb-4">
                    <h2 id="souvenir-media" class="text-lg font-semibold">{{ copy.media }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.mediaDescription }}</p>
                </div>
                <div class="mt-5 grid gap-5" :class="initialValues.currentImageUrl ? 'lg:grid-cols-[12rem_minmax(0,1fr)] lg:items-start' : ''">
                    <div v-if="initialValues.currentImageUrl">
                        <p :class="labelClass">{{ copy.currentImage }}</p>
                        <div class="mt-2 aspect-square overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)]">
                            <img :alt="initialValues.currentImageAlt || ''" class="h-full w-full object-cover" :src="initialValues.currentImageUrl">
                        </div>
                    </div>
                    <div class="rounded-xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                        <label for="image" :class="labelClass">{{ copy.uploadImage }}</label>
                        <input id="image" :aria-describedby="form.errors.image ? 'image-help image-error' : 'image-help'" :aria-invalid="Boolean(form.errors.image)" :aria-required="initialValues.imageRequired" :class="inputClass" accept="image/jpeg,image/png,image/gif,image/webp" name="image" :required="initialValues.imageRequired" type="file" @change="selectImage">
                        <p v-if="form.image" class="mt-2 break-all text-xs font-medium">{{ form.image.name }}</p>
                        <p id="image-help" :class="helpClass">{{ copy.mediaHelp }}</p>
                        <p v-if="form.errors.image" id="image-error" :class="errorClass">{{ form.errors.image }}</p>
                        <div v-if="form.progress" class="mt-3" role="progressbar" :aria-valuenow="form.progress.percentage ?? 0" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-2 overflow-hidden rounded-full bg-[var(--admin-border)]">
                                <div class="h-full bg-[var(--admin-accent)] transition-[width]" :style="{ width: `${form.progress.percentage ?? 0}%` }" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-4 sm:flex-row sm:items-center sm:justify-end">
                <Link :href="routes.souvenirs" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--admin-border)] px-4 text-sm font-semibold text-[var(--admin-muted)] transition-colors hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]">{{ copy.cancel }}</Link>
                <button :aria-busy="form.processing" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--admin-accent)] px-5 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)] disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing" type="submit">
                    {{ form.processing ? `${copy.saving}…` : copy.submit }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
