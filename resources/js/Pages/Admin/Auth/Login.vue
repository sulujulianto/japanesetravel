<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';

import AdminGuestLayout from '../../../Layouts/AdminGuestLayout.vue';
import type { SharedPageProps } from '../../../types/inertia';

interface LoginCopy {
    backToUserLogin: string;
    description: string;
    email: string;
    emailPlaceholder: string;
    eyebrow: string;
    footer: string;
    password: string;
    remember: string;
    submit: string;
    theme: string;
    themeToggle: string;
    title: string;
}

interface LoginRoutes {
    home: string;
    localeEn: string;
    localeId: string;
    submit: string;
    userLogin: string;
}

defineOptions({ name: 'AdminLoginPage' });

const props = defineProps<{
    copy: LoginCopy;
    routes: LoginRoutes;
}>();

const page = usePage<SharedPageProps>();
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = (): void => {
    form.post(props.routes.submit, {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="copy.title" />

    <AdminGuestLayout
        :active-locale="page.props.locale"
        :app-name="page.props.app.name"
        :copy="copy"
        :routes="routes"
    >
        <div class="mb-8">
            <h1 class="font-display text-3xl font-semibold text-slate-900 dark:text-white">
                {{ copy.title }}
            </h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">
                {{ copy.description }}
            </p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <label class="auth-label block text-sm font-medium" for="email">
                    {{ copy.email }}
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    :aria-describedby="form.errors.email ? 'email-error' : undefined"
                    :aria-invalid="Boolean(form.errors.email)"
                    :placeholder="copy.emailPlaceholder"
                    autocomplete="username"
                    autofocus
                    class="auth-input mt-2 block w-full"
                    name="email"
                    required
                    type="email"
                >
                <p v-if="form.errors.email" id="email-error" class="mt-2 text-sm text-[var(--auth-danger)]" role="alert">
                    {{ form.errors.email }}
                </p>
            </div>

            <div class="mt-4">
                <label class="auth-label block text-sm font-medium" for="password">
                    {{ copy.password }}
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    :aria-describedby="form.errors.password ? 'password-error' : undefined"
                    :aria-invalid="Boolean(form.errors.password)"
                    autocomplete="current-password"
                    class="auth-input mt-2 block w-full"
                    name="password"
                    placeholder="••••••••"
                    required
                    type="password"
                >
                <p
                    v-if="form.errors.password"
                    id="password-error"
                    class="mt-2 text-sm text-[var(--auth-danger)]"
                    role="alert"
                >
                    {{ form.errors.password }}
                </p>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <label class="inline-flex items-center" for="remember">
                    <input
                        id="remember"
                        v-model="form.remember"
                        class="rounded border-slate-300 text-rose-500 focus:ring-rose-400"
                        name="remember"
                        type="checkbox"
                    >
                    <span class="ms-2 text-sm text-slate-600 dark:text-slate-300">{{ copy.remember }}</span>
                </label>
            </div>

            <div class="mt-6">
                <button
                    :aria-busy="form.processing"
                    :disabled="form.processing"
                    class="auth-primary inline-flex w-full items-center justify-center px-5 py-3 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-60"
                    type="submit"
                >
                    {{ form.processing ? `${copy.submit}…` : copy.submit }}
                </button>
            </div>

            <div class="mt-6 text-center">
                <p class="text-sm text-slate-600 dark:text-slate-300">
                    <a :href="routes.userLogin" class="auth-link font-semibold hover:underline">
                        {{ copy.backToUserLogin }}
                    </a>
                </p>
            </div>
        </form>
    </AdminGuestLayout>
</template>
