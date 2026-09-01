<script setup lang="ts">
import PublicFooter from '../Components/Public/PublicFooter.vue';
import PublicNavigation from '../Components/Public/PublicNavigation.vue';
import type { PublicShellCopy, PublicShellRoutes } from '../types/public';

defineOptions({ name: 'PublicLayout' });

defineProps<{
    copy: PublicShellCopy;
    routes: PublicShellRoutes;
}>();
</script>

<template>
    <div data-public-shell class="public-shell relative isolate flex min-h-dvh flex-col overflow-x-clip font-sans">
        <PublicNavigation :copy="copy" :routes="routes" />
        <main class="relative flex-1">
            <slot />
        </main>
        <PublicFooter :copy="copy" :routes="routes" />
    </div>
</template>
