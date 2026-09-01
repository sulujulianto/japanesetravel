<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

import AdminAccountCard from '../Components/Admin/AdminAccountCard.vue';
import AdminNavigation from '../Components/Admin/AdminNavigation.vue';
import ThemeToggle from '../Components/ThemeToggle.vue';
import { useTheme, type Theme } from '../composables/useTheme';
import type { AdminShellCopy, AdminShellRoutes, NavigationItem, NavigationKey } from '../types/admin';
import type { SharedPageProps } from '../types/inertia';

defineOptions({ name: 'AdminLayout' });

defineSlots<{
    default(props: { theme: Theme }): unknown;
}>();

const props = defineProps<{
    activeNavigation: NavigationKey;
    copy: AdminShellCopy;
    routes: AdminShellRoutes;
}>();

const page = usePage<SharedPageProps>();
const logoutForm = useForm({});
const mobileOpen = ref(false);
const { theme, toggleTheme } = useTheme();

const navigationItems = computed<NavigationItem[]>(() => [
    { href: props.routes.dashboard, key: 'dashboard', label: props.copy.dashboard },
    { href: props.routes.orders, key: 'orders', label: props.copy.orders },
    { href: props.routes.places, key: 'places', label: props.copy.places },
    { href: props.routes.souvenirs, key: 'souvenirs', label: props.copy.souvenirs },
    { href: props.routes.lowStock, key: 'low-stock', label: props.copy.lowStock },
]);

const admin = computed(() => page.props.auth.admin);

const closeMobileMenu = (): void => {
    mobileOpen.value = false;
};

const handleEscape = (event: KeyboardEvent): void => {
    if (event.key === 'Escape') closeMobileMenu();
};

const logout = (): void => {
    logoutForm.post(props.routes.logout);
};

watch(mobileOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

onMounted(() => window.addEventListener('keydown', handleEscape));

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="min-h-screen bg-[var(--admin-canvas)] text-[var(--admin-ink)]">
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 border-r border-[var(--admin-border)] bg-[var(--admin-surface)] lg:flex lg:flex-col">
            <div class="border-b border-[var(--admin-border)] px-6 py-6">
                <a :href="routes.dashboard" class="inline-flex items-center gap-3">
                    <span aria-hidden="true" class="brand-mark h-10 w-10">{{ page.props.app.mark }}</span>
                    <span>
                        <strong class="block text-sm">{{ page.props.app.name }}</strong>
                        <span class="mt-0.5 block text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-muted)]">Admin</span>
                    </span>
                </a>
            </div>

            <div class="flex-1 px-4 py-6">
                <AdminNavigation :active="activeNavigation" :items="navigationItems" :navigation-label="copy.navigation" />
            </div>

            <div v-if="admin" class="border-t border-[var(--admin-border)] p-4">
                <AdminAccountCard
                    :email="admin.email"
                    :logout-label="copy.logout"
                    :processing="logoutForm.processing"
                    :username="admin.username"
                    @logout="logout"
                />
            </div>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-[var(--admin-border)] bg-[var(--admin-surface)]/95 backdrop-blur-sm">
                <div class="mx-auto flex min-h-16 max-w-[96rem] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            :aria-expanded="mobileOpen"
                            :aria-label="copy.menu"
                            aria-controls="admin-mobile-navigation"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[var(--admin-border)] text-lg lg:hidden"
                            type="button"
                            @click="mobileOpen = true"
                        >
                            <span aria-hidden="true" class="space-y-1">
                                <span class="block h-0.5 w-4 bg-current" />
                                <span class="block h-0.5 w-4 bg-current" />
                                <span class="block h-0.5 w-4 bg-current" />
                            </span>
                        </button>
                        <div class="min-w-0">
                            <p class="truncate text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--admin-muted)]">{{ copy.workspace }}</p>
                            <p class="truncate text-sm font-semibold">{{ copy.workspaceDescription }}</p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2 text-xs font-semibold">
                        <div class="hidden rounded-full border border-[var(--admin-border)] bg-[var(--admin-muted-surface)] p-1 sm:flex">
                            <a :aria-current="page.props.locale === 'id' ? 'page' : undefined" :class="page.props.locale === 'id' ? 'bg-[var(--admin-ink)] text-[var(--admin-surface)]' : 'text-[var(--admin-muted)]'" :href="routes.localeId" class="rounded-full px-2.5 py-1">ID</a>
                            <a :aria-current="page.props.locale === 'en' ? 'page' : undefined" :class="page.props.locale === 'en' ? 'bg-[var(--admin-ink)] text-[var(--admin-surface)]' : 'text-[var(--admin-muted)]'" :href="routes.localeEn" class="rounded-full px-2.5 py-1">EN</a>
                        </div>
                        <ThemeToggle
                            :dark-label="copy.themeDark"
                            :light-label="copy.themeLight"
                            :theme="theme"
                            :use-dark-label="copy.useDarkTheme"
                            :use-light-label="copy.useLightTheme"
                            @toggle="toggleTheme"
                        />
                        <a :href="routes.home" class="hidden rounded-full border border-[var(--admin-border)] px-3 py-2 text-[var(--admin-muted)] transition-colors hover:text-[var(--admin-accent)] sm:inline-flex">{{ copy.viewSite }}</a>
                    </div>
                </div>
            </header>

            <main class="ui-reveal mx-auto w-full max-w-[96rem] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <slot :theme="theme" />
            </main>
        </div>

        <div v-if="mobileOpen" class="fixed inset-0 z-50 lg:hidden">
            <button :aria-label="copy.closeMenu" class="absolute inset-0 bg-black/45" type="button" @click="closeMobileMenu" />
            <aside id="admin-mobile-navigation" class="relative flex h-full w-[min(22rem,88vw)] flex-col border-r border-[var(--admin-border)] bg-[var(--admin-surface)] shadow-2xl">
                <div class="flex items-center justify-between border-b border-[var(--admin-border)] px-5 py-5">
                    <strong class="text-sm">{{ page.props.app.name }} · Admin</strong>
                    <button :aria-label="copy.closeMenu" class="h-10 w-10 rounded-lg border border-[var(--admin-border)]" type="button" @click="closeMobileMenu">×</button>
                </div>
                <div class="flex-1 overflow-y-auto px-4 py-5">
                    <AdminNavigation :active="activeNavigation" :items="navigationItems" :navigation-label="copy.navigation" />
                    <div class="mt-5 flex items-center gap-2 border-t border-[var(--admin-border)] pt-5 text-xs font-semibold">
                        <a :href="routes.localeId" class="rounded-full border border-[var(--admin-border)] px-3 py-2">ID</a>
                        <a :href="routes.localeEn" class="rounded-full border border-[var(--admin-border)] px-3 py-2">EN</a>
                        <a :href="routes.home" class="ml-auto text-[var(--admin-accent)]">{{ copy.viewSite }}</a>
                    </div>
                </div>
                <div v-if="admin" class="border-t border-[var(--admin-border)] p-4">
                    <AdminAccountCard :email="admin.email" :logout-label="copy.logout" :processing="logoutForm.processing" :username="admin.username" @logout="logout" />
                </div>
            </aside>
        </div>
    </div>
</template>
