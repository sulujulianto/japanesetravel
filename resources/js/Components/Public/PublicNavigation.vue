<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

import { useTheme } from '../../composables/useTheme';
import type { SharedPageProps } from '../../types/inertia';
import type { PublicShellCopy, PublicShellRoutes } from '../../types/public';
import ThemeToggle from '../ThemeToggle.vue';

defineOptions({ name: 'PublicNavigation' });

const props = defineProps<{
    copy: PublicShellCopy;
    routes: PublicShellRoutes;
}>();

const page = usePage<SharedPageProps>();
const clientReady = ref(false);
const mobileOpen = ref(false);
const { theme, toggleTheme } = useTheme();
const user = computed(() => page.props.auth.user);
const cartCount = computed(() => page.props.cart.count);

const closeMobileMenu = (): void => {
    mobileOpen.value = false;
};

const handleEscape = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') closeMobileMenu();
};

watch(mobileOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

onMounted(() => {
    clientReady.value = true;
    window.addEventListener('keydown', handleEscape);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <nav data-public-navigation :aria-label="copy.navigation" class="public-navigation site-navbar sticky top-0 z-40 border-b">
        <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a :href="routes.home" class="inline-flex min-w-0 items-center gap-2.5 transition hover:text-[var(--public-accent)]">
                <span aria-hidden="true" class="brand-mark">{{ page.props.app.mark }}</span>
                <span class="truncate text-base font-semibold tracking-tight">{{ page.props.app.name }}</span>
            </a>

            <div class="hidden items-center gap-7 lg:flex">
                <a :href="routes.places" class="text-sm font-medium text-[var(--public-muted)] transition hover:text-[var(--public-accent)]">{{ copy.destinations }}</a>
                <a :href="routes.shop" class="text-sm font-medium text-[var(--public-muted)] transition hover:text-[var(--public-accent)]">{{ copy.souvenirs }}</a>
                <a v-if="user" :href="routes.orders" class="text-sm font-medium text-[var(--public-muted)] transition hover:text-[var(--public-accent)]">{{ copy.orders }}</a>
            </div>

            <div class="hidden items-center justify-end gap-2 lg:flex">
                <a :href="routes.cart" class="ui-action inline-flex min-h-10 items-center rounded-full border border-[var(--public-border)] px-3 text-xs font-semibold hover:border-[var(--public-accent)] hover:text-[var(--public-accent)]">
                    <span>{{ copy.cart }}</span>
                    <span v-if="cartCount > 0" class="ml-2 rounded-full bg-[var(--public-accent)] px-2 py-0.5 text-[10px] font-semibold text-white">{{ cartCount }}</span>
                </a>

                <div class="inline-flex items-center rounded-full border border-[var(--public-border)] bg-[var(--public-surface)] p-1 text-xs font-semibold">
                    <a :aria-current="page.props.locale === 'id' ? 'page' : undefined" :class="page.props.locale === 'id' ? 'bg-[var(--public-ink)] text-[var(--public-surface)]' : 'text-[var(--public-muted)] hover:text-[var(--public-accent)]'" :href="routes.localeId" class="rounded-full px-2.5 py-1">ID</a>
                    <a :aria-current="page.props.locale === 'en' ? 'page' : undefined" :class="page.props.locale === 'en' ? 'bg-[var(--public-ink)] text-[var(--public-surface)]' : 'text-[var(--public-muted)] hover:text-[var(--public-accent)]'" :href="routes.localeEn" class="rounded-full px-2.5 py-1">EN</a>
                </div>

                <ThemeToggle
                    :dark-label="copy.themeDark"
                    :light-label="copy.themeLight"
                    :theme="theme"
                    :use-dark-label="copy.useDarkTheme"
                    :use-light-label="copy.useLightTheme"
                    @toggle="toggleTheme"
                />

                <a v-if="user" :href="routes.dashboard" class="ui-action inline-flex min-h-10 items-center rounded-full bg-[var(--public-accent)] px-4 text-xs font-semibold text-white hover:bg-[var(--public-accent-active)]">{{ copy.dashboard }}</a>
                <template v-else>
                    <a :href="routes.login" class="text-sm font-medium text-[var(--public-muted)] transition hover:text-[var(--public-accent)]">{{ copy.login }}</a>
                    <a :href="routes.register" class="ui-action inline-flex min-h-10 items-center rounded-full bg-[var(--public-accent)] px-4 text-xs font-semibold text-white hover:bg-[var(--public-accent-active)]">{{ copy.register }}</a>
                </template>
            </div>

            <div class="flex shrink-0 items-center gap-2 lg:hidden">
                <ThemeToggle
                    :dark-label="copy.themeDark"
                    :light-label="copy.themeLight"
                    :theme="theme"
                    :use-dark-label="copy.useDarkTheme"
                    :use-light-label="copy.useLightTheme"
                    @toggle="toggleTheme"
                />
                <button :aria-expanded="mobileOpen" :aria-label="copy.menu" aria-controls="public-mobile-navigation" class="ui-action inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--public-border)] text-[var(--public-ink)]" type="button" @click="mobileOpen = true">
                    <span class="sr-only">{{ copy.menu }}</span>
                    <span aria-hidden="true" class="space-y-1">
                        <span class="block h-0.5 w-4 bg-current" />
                        <span class="block h-0.5 w-4 bg-current" />
                        <span class="block h-0.5 w-4 bg-current" />
                    </span>
                </button>
            </div>
        </div>

        <Teleport v-if="clientReady" to="body">
            <div v-if="mobileOpen" class="fixed inset-0 z-50 font-sans text-[var(--public-ink)] lg:hidden">
                <button :aria-label="copy.closeMenu" class="absolute inset-0 bg-black/45" type="button" @click="closeMobileMenu" />
                <div id="public-mobile-navigation" class="absolute inset-y-0 right-0 flex w-[min(22rem,88vw)] flex-col border-l border-[var(--public-border)] bg-[var(--public-surface)] shadow-2xl">
                <div class="flex items-center justify-between border-b border-[var(--public-border)] px-5 py-5">
                    <strong class="text-sm">{{ props.copy.brand }}</strong>
                    <button :aria-label="copy.closeMenu" class="h-10 w-10 rounded-lg border border-[var(--public-border)] text-lg" type="button" @click="closeMobileMenu">×</button>
                </div>
                <div class="flex-1 space-y-2 overflow-y-auto px-4 py-5">
                    <a :href="routes.home" class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">{{ copy.brand }}</a>
                    <a :href="routes.places" class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">{{ copy.destinations }}</a>
                    <a :href="routes.shop" class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">{{ copy.souvenirs }}</a>
                    <a :href="routes.cart" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">
                        <span>{{ copy.cart }}</span>
                        <span v-if="cartCount > 0" class="rounded-full bg-[var(--public-accent)] px-2 py-0.5 text-[10px] font-semibold text-white">{{ cartCount }}</span>
                    </a>
                    <template v-if="user">
                        <a :href="routes.orders" class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">{{ copy.orders }}</a>
                        <a :href="routes.dashboard" class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">{{ copy.dashboard }}</a>
                    </template>
                    <template v-else>
                        <a :href="routes.login" class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">{{ copy.login }}</a>
                        <a :href="routes.register" class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-[var(--public-surface-muted)] hover:text-[var(--public-accent)]">{{ copy.register }}</a>
                    </template>
                    <div class="flex items-center gap-2 px-3 pt-3 text-xs font-semibold">
                        <a :aria-current="page.props.locale === 'id' ? 'page' : undefined" :href="routes.localeId" class="rounded-full border border-[var(--public-border)] px-3 py-1.5" :class="page.props.locale === 'id' ? 'bg-[var(--public-ink)] text-[var(--public-surface)]' : ''">ID</a>
                        <a :aria-current="page.props.locale === 'en' ? 'page' : undefined" :href="routes.localeEn" class="rounded-full border border-[var(--public-border)] px-3 py-1.5" :class="page.props.locale === 'en' ? 'bg-[var(--public-ink)] text-[var(--public-surface)]' : ''">EN</a>
                    </div>
                </div>
                </div>
            </div>
        </Teleport>
    </nav>
</template>
