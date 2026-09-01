<script setup lang="ts">
import ThemeToggle from '../Components/ThemeToggle.vue';
import { useTheme } from '../composables/useTheme';
import type { Locale } from '../types/inertia';

interface LayoutCopy {
    eyebrow: string;
    footer: string;
    theme: string;
    themeDark: string;
    themeLight: string;
    themeToggle: string;
    useDarkTheme: string;
    useLightTheme: string;
}

interface LayoutRoutes {
    home: string;
    localeEn: string;
    localeId: string;
}

defineOptions({ name: 'AdminGuestLayout' });

defineProps<{
    activeLocale: Locale;
    appMark: string;
    appName: string;
    copy: LayoutCopy;
    routes: LayoutRoutes;
}>();

const { theme, toggleTheme } = useTheme();
</script>

<template>
    <div class="auth-page flex min-h-screen flex-col">
        <header class="mx-auto flex w-full max-w-6xl items-center justify-between px-5 py-5 sm:px-8">
            <a
                :href="routes.home"
                class="inline-flex items-center gap-3 text-sm font-semibold tracking-tight text-[var(--auth-ink)]"
            >
                <span
                    aria-hidden="true"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--auth-hairline)] bg-[var(--auth-surface)] font-display text-sm"
                >
                    {{ appMark }}
                </span>
                <span>{{ appName }}</span>
            </a>

            <div class="flex items-center gap-2 text-xs font-semibold">
                <a
                    :aria-current="activeLocale === 'id' ? 'page' : undefined"
                    :class="{ 'auth-control-active': activeLocale === 'id' }"
                    :href="routes.localeId"
                    class="auth-control px-3 py-1.5"
                >
                    ID
                </a>
                <a
                    :aria-current="activeLocale === 'en' ? 'page' : undefined"
                    :class="{ 'auth-control-active': activeLocale === 'en' }"
                    :href="routes.localeEn"
                    class="auth-control px-3 py-1.5"
                >
                    EN
                </a>
                <ThemeToggle
                    :dark-label="copy.themeDark"
                    :light-label="copy.themeLight"
                    :theme="theme"
                    :use-dark-label="copy.useDarkTheme"
                    :use-light-label="copy.useLightTheme"
                    @toggle="toggleTheme"
                />
            </div>
        </header>

        <main class="flex flex-1 items-center justify-center px-5 pb-12 pt-4 sm:px-8">
            <div class="ui-reveal w-full max-w-[440px]">
                <div class="mb-5 text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--auth-helper)]">
                        {{ copy.eyebrow }}
                    </p>
                </div>

                <section class="auth-card px-6 py-7 sm:px-8 sm:py-8">
                    <slot />
                </section>

                <p class="mx-auto mt-5 max-w-sm text-center text-xs font-medium leading-5 text-[var(--auth-helper)]">
                    {{ copy.footer }}
                </p>
            </div>
        </main>
    </div>
</template>
