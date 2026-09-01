<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

import type { SharedPageProps } from '../../types/inertia';
import type { PublicShellCopy, PublicShellRoutes } from '../../types/public';

defineOptions({ name: 'PublicFooter' });

defineProps<{
    copy: PublicShellCopy;
    routes: PublicShellRoutes;
}>();

const page = usePage<SharedPageProps>();
const user = computed(() => page.props.auth.user);
const currentYear = new Date().getFullYear();
</script>

<template>
    <footer data-public-footer class="public-footer relative mt-16 shrink-0 border-t">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-10 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div class="lg:col-span-2">
                <a :href="routes.home" class="inline-flex items-center gap-2 text-lg font-semibold text-[var(--public-ink)]">
                    <span aria-hidden="true" class="brand-mark">{{ page.props.app.mark }}</span>
                    <span>{{ page.props.app.name }}</span>
                </a>
                <p class="mt-4 max-w-md text-sm leading-6 text-[var(--public-muted)]">{{ copy.footerDescription }}</p>
            </div>
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-[var(--public-muted)]">{{ copy.navigation }}</h2>
                <ul class="mt-4 space-y-2 text-sm text-[var(--public-muted)]">
                    <li><a :href="routes.places" class="hover:text-[var(--public-accent)]">{{ copy.destinations }}</a></li>
                    <li><a :href="routes.shop" class="hover:text-[var(--public-accent)]">{{ copy.souvenirs }}</a></li>
                    <li><a :href="user ? routes.dashboard : routes.login" class="hover:text-[var(--public-accent)]">{{ user ? copy.dashboard : copy.login }}</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider text-[var(--public-muted)]">{{ copy.contact }}</h2>
                <p class="mt-4 text-sm leading-6 text-[var(--public-muted)]">{{ copy.contactDescription }}</p>
            </div>
        </div>
        <div class="mx-auto flex max-w-7xl flex-col items-center gap-2 border-t border-[var(--public-border)] px-4 py-5 text-xs text-[var(--public-muted)] sm:px-6 lg:px-8">
            <span>© {{ currentYear }} {{ copy.footerProject }}</span>
            <span>{{ copy.footerTechnology }}</span>
        </div>
    </footer>
</template>
