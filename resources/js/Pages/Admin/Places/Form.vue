<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import AdminLayout from '../../../Layouts/AdminLayout.vue';
import type {
    AdminPlaceFormCopy,
    AdminPlaceFormInitialValues,
    AdminPlaceFormMode,
    AdminPlaceFormRoutes,
    AdminPlaceScheduleOptions,
    AdminPlaceScheduleValues,
} from '../../../types/adminPlaceForm';

defineOptions({ name: 'AdminPlaceFormPage' });

const props = defineProps<{
    copy: AdminPlaceFormCopy;
    initialValues: AdminPlaceFormInitialValues;
    mode: AdminPlaceFormMode;
    routes: AdminPlaceFormRoutes;
    scheduleOptions: AdminPlaceScheduleOptions;
    scheduleValues: AdminPlaceScheduleValues;
}>();

const form = useForm({
    _method: props.mode === 'edit' ? 'put' : 'post',
    address: props.initialValues.address,
    clear_schedule: false,
    description_en: props.initialValues.descriptionEn,
    description_id: props.initialValues.descriptionId,
    facilities: props.initialValues.facilities,
    image: null as File | null,
    name_en: props.initialValues.nameEn,
    name_id: props.initialValues.nameId,
    ...props.scheduleValues,
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

const formatHour = (hour: number): string => String(hour).padStart(2, '0');

const submit = (): void => {
    form.post(props.routes.submitPlace, {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="copy.title" />

    <AdminLayout active-navigation="places" :copy="copy" :routes="routes">
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

        <form class="mt-6 space-y-6" @submit.prevent="submit">
            <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="place-id-content">
                <div class="border-b border-[var(--admin-border)] pb-4">
                    <h2 id="place-id-content" class="text-lg font-semibold">{{ copy.idContentTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.idContentDescription }}</p>
                </div>
                <div class="mt-5 grid gap-5">
                    <div>
                        <label for="name-id" :class="labelClass">{{ copy.nameId }}</label>
                        <input id="name-id" v-model="form.name_id" :aria-describedby="form.errors.name_id ? 'name-id-error' : undefined" :aria-invalid="Boolean(form.errors.name_id)" :class="inputClass" maxlength="150" name="name_id" required>
                        <p v-if="form.errors.name_id" id="name-id-error" :class="errorClass">{{ form.errors.name_id }}</p>
                    </div>
                    <div>
                        <label for="description-id" :class="labelClass">{{ copy.descriptionId }}</label>
                        <textarea id="description-id" v-model="form.description_id" :aria-describedby="form.errors.description_id ? 'description-id-help description-id-error' : 'description-id-help'" :aria-invalid="Boolean(form.errors.description_id)" :class="inputClass" name="description_id" rows="6" />
                        <p id="description-id-help" :class="helpClass">{{ copy.descriptionHelp }}</p>
                        <p v-if="form.errors.description_id" id="description-id-error" :class="errorClass">{{ form.errors.description_id }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="place-en-content">
                <div class="border-b border-[var(--admin-border)] pb-4">
                    <h2 id="place-en-content" class="text-lg font-semibold">{{ copy.enContentTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.enContentDescription }}</p>
                </div>
                <div class="mt-5 grid gap-5">
                    <div>
                        <label for="name-en" :class="labelClass">{{ copy.nameEn }}</label>
                        <input id="name-en" v-model="form.name_en" :aria-describedby="form.errors.name_en ? 'name-en-error' : undefined" :aria-invalid="Boolean(form.errors.name_en)" :class="inputClass" maxlength="150" name="name_en" required>
                        <p v-if="form.errors.name_en" id="name-en-error" :class="errorClass">{{ form.errors.name_en }}</p>
                    </div>
                    <div>
                        <label for="description-en" :class="labelClass">{{ copy.descriptionEn }}</label>
                        <textarea id="description-en" v-model="form.description_en" :aria-describedby="form.errors.description_en ? 'description-en-error' : undefined" :aria-invalid="Boolean(form.errors.description_en)" :class="inputClass" name="description_en" rows="6" />
                        <p v-if="form.errors.description_en" id="description-en-error" :class="errorClass">{{ form.errors.description_en }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="place-visit-information">
                <div class="border-b border-[var(--admin-border)] pb-4">
                    <h2 id="place-visit-information" class="text-lg font-semibold">{{ copy.visitTitle }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.visitDescription }}</p>
                </div>
                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="address" :class="labelClass">{{ copy.address }}</label>
                        <input id="address" v-model="form.address" :aria-describedby="form.errors.address ? 'address-error' : undefined" :aria-invalid="Boolean(form.errors.address)" :class="inputClass" autocomplete="off" maxlength="255" name="address">
                        <p v-if="form.errors.address" id="address-error" :class="errorClass">{{ form.errors.address }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="facilities" :class="labelClass">{{ copy.facilities }}</label>
                        <input id="facilities" v-model="form.facilities" :aria-describedby="form.errors.facilities ? 'facilities-help facilities-error' : 'facilities-help'" :aria-invalid="Boolean(form.errors.facilities)" :class="inputClass" name="facilities">
                        <p id="facilities-help" :class="helpClass">{{ copy.facilitiesHelp }}</p>
                        <p v-if="form.errors.facilities" id="facilities-error" :class="errorClass">{{ form.errors.facilities }}</p>
                    </div>

                    <fieldset class="md:col-span-2" :disabled="form.clear_schedule">
                        <legend :class="labelClass">{{ copy.openDays }}</legend>
                        <div class="mt-2 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="open-day-start" class="block text-xs font-semibold text-[var(--admin-muted)]">{{ copy.fromDay }}</label>
                                <select id="open-day-start" v-model="form.open_day_start" :aria-describedby="form.errors.open_day_start ? 'open-day-start-error' : undefined" :aria-invalid="Boolean(form.errors.open_day_start)" :class="inputClass" name="open_day_start">
                                    <option value="">{{ copy.selectDay }}</option>
                                    <option v-for="day in scheduleOptions.days" :key="day.value" :value="day.value">{{ day.label }}</option>
                                </select>
                                <p v-if="form.errors.open_day_start" id="open-day-start-error" :class="errorClass">{{ form.errors.open_day_start }}</p>
                            </div>
                            <div>
                                <label for="open-day-end" class="block text-xs font-semibold text-[var(--admin-muted)]">{{ copy.toDay }}</label>
                                <select id="open-day-end" v-model="form.open_day_end" :aria-describedby="form.errors.open_day_end ? 'open-day-end-error' : undefined" :aria-invalid="Boolean(form.errors.open_day_end)" :class="inputClass" name="open_day_end">
                                    <option value="">{{ copy.selectDay }}</option>
                                    <option v-for="day in scheduleOptions.days" :key="day.value" :value="day.value">{{ day.label }}</option>
                                </select>
                                <p v-if="form.errors.open_day_end" id="open-day-end-error" :class="errorClass">{{ form.errors.open_day_end }}</p>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset :disabled="form.clear_schedule">
                        <legend :class="labelClass">{{ copy.openingTime }}</legend>
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <div>
                                <label for="open-time-start-hour" class="sr-only">{{ copy.hour }}</label>
                                <select id="open-time-start-hour" v-model="form.open_time_start_hour" :aria-label="copy.hour" :aria-invalid="Boolean(form.errors.open_time_start_hour)" :class="inputClass" name="open_time_start_hour">
                                    <option value="">{{ copy.hour }}</option>
                                    <option v-for="hour in scheduleOptions.hours" :key="hour" :value="String(hour)">{{ formatHour(hour) }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="open-time-start-minute" class="sr-only">{{ copy.minute }}</label>
                                <select id="open-time-start-minute" v-model="form.open_time_start_minute" :aria-label="copy.minute" :aria-invalid="Boolean(form.errors.open_time_start_minute)" :class="inputClass" name="open_time_start_minute">
                                    <option value="">{{ copy.minute }}</option>
                                    <option v-for="minute in scheduleOptions.minutes" :key="minute" :value="minute">{{ minute }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="open-time-start-period" class="sr-only">{{ copy.period }}</label>
                                <select id="open-time-start-period" v-model="form.open_time_start_period" :aria-label="copy.period" :aria-invalid="Boolean(form.errors.open_time_start_period)" :class="inputClass" name="open_time_start_period">
                                    <option value="">{{ copy.amPm }}</option>
                                    <option v-for="period in scheduleOptions.periods" :key="period" :value="period">{{ period }}</option>
                                </select>
                            </div>
                        </div>
                        <p v-if="form.errors.open_time_start_hour" :class="errorClass">{{ form.errors.open_time_start_hour }}</p>
                        <p v-if="form.errors.open_time_start_minute" :class="errorClass">{{ form.errors.open_time_start_minute }}</p>
                        <p v-if="form.errors.open_time_start_period" :class="errorClass">{{ form.errors.open_time_start_period }}</p>
                    </fieldset>

                    <fieldset :disabled="form.clear_schedule">
                        <legend :class="labelClass">{{ copy.closingTime }}</legend>
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <div>
                                <label for="open-time-end-hour" class="sr-only">{{ copy.hour }}</label>
                                <select id="open-time-end-hour" v-model="form.open_time_end_hour" :aria-label="copy.hour" :aria-invalid="Boolean(form.errors.open_time_end_hour)" :class="inputClass" name="open_time_end_hour">
                                    <option value="">{{ copy.hour }}</option>
                                    <option v-for="hour in scheduleOptions.hours" :key="hour" :value="String(hour)">{{ formatHour(hour) }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="open-time-end-minute" class="sr-only">{{ copy.minute }}</label>
                                <select id="open-time-end-minute" v-model="form.open_time_end_minute" :aria-label="copy.minute" :aria-invalid="Boolean(form.errors.open_time_end_minute)" :class="inputClass" name="open_time_end_minute">
                                    <option value="">{{ copy.minute }}</option>
                                    <option v-for="minute in scheduleOptions.minutes" :key="minute" :value="minute">{{ minute }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="open-time-end-period" class="sr-only">{{ copy.period }}</label>
                                <select id="open-time-end-period" v-model="form.open_time_end_period" :aria-label="copy.period" :aria-invalid="Boolean(form.errors.open_time_end_period)" :class="inputClass" name="open_time_end_period">
                                    <option value="">{{ copy.amPm }}</option>
                                    <option v-for="period in scheduleOptions.periods" :key="period" :value="period">{{ period }}</option>
                                </select>
                            </div>
                        </div>
                        <p v-if="form.errors.open_time_end_hour" :class="errorClass">{{ form.errors.open_time_end_hour }}</p>
                        <p v-if="form.errors.open_time_end_minute" :class="errorClass">{{ form.errors.open_time_end_minute }}</p>
                        <p v-if="form.errors.open_time_end_period" :class="errorClass">{{ form.errors.open_time_end_period }}</p>
                    </fieldset>

                    <div class="md:col-span-2">
                        <p :class="helpClass">{{ copy.scheduleHelp }}</p>
                        <div v-if="initialValues.legacyScheduleLabel" class="mt-3 rounded-xl border border-[var(--admin-warning)]/30 bg-[var(--admin-warning-soft)] p-3 text-sm text-[var(--admin-warning)]">
                            <p>{{ initialValues.legacyScheduleLabel }}</p>
                            <p class="mt-1 text-xs">{{ copy.legacyScheduleHelp }}</p>
                        </div>
                        <label v-if="initialValues.hasSchedule" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-[var(--admin-muted)]">
                            <input v-model="form.clear_schedule" class="rounded border-[var(--admin-border)] text-[var(--admin-accent)] focus:ring-[var(--admin-accent)]" name="clear_schedule" type="checkbox">
                            {{ copy.clearSchedule }}
                        </label>
                        <p v-if="form.errors.clear_schedule" :class="errorClass">{{ form.errors.clear_schedule }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 sm:p-6" aria-labelledby="place-media">
                <div class="border-b border-[var(--admin-border)] pb-4">
                    <h2 id="place-media" class="text-lg font-semibold">{{ copy.media }}</h2>
                    <p class="mt-1 text-sm text-[var(--admin-muted)]">{{ copy.mediaDescription }}</p>
                </div>
                <div class="mt-5 grid gap-5" :class="initialValues.currentImageUrl ? 'lg:grid-cols-[12rem_minmax(0,1fr)] lg:items-start' : ''">
                    <div v-if="initialValues.currentImageUrl">
                        <p :class="labelClass">{{ copy.currentImage }}</p>
                        <div class="mt-2 aspect-[4/3] overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-muted-surface)]">
                            <img :alt="initialValues.currentImageAlt || ''" class="h-full w-full object-cover" :src="initialValues.currentImageUrl">
                        </div>
                    </div>
                    <div class="rounded-xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-4">
                        <label for="image" :class="labelClass">{{ copy.uploadImage }}</label>
                        <input id="image" :aria-describedby="form.errors.image ? 'image-help image-error' : 'image-help'" :aria-invalid="Boolean(form.errors.image)" :class="inputClass" accept="image/jpeg,image/png,image/gif,image/webp" name="image" type="file" @change="selectImage">
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
                <Link :href="routes.places" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--admin-border)] px-4 text-sm font-semibold text-[var(--admin-muted)] transition-colors hover:border-[var(--admin-accent)] hover:text-[var(--admin-accent)]">{{ copy.cancel }}</Link>
                <button :aria-busy="form.processing" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--admin-accent)] px-5 text-sm font-semibold text-white transition-colors hover:bg-[var(--admin-accent-active)] disabled:cursor-not-allowed disabled:opacity-60" :disabled="form.processing" type="submit">
                    {{ form.processing ? `${copy.saving}…` : copy.submit }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>
