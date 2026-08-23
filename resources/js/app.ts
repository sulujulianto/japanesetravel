import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    pages: {
        path: './Pages',
        extension: '.vue',
        lazy: true,
    },
});
