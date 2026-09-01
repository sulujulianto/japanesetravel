import { onMounted, ref, watch } from 'vue';

export type Theme = 'dark' | 'light';

export const useTheme = () => {
    const theme = ref<Theme>('light');
    const mounted = ref(false);

    onMounted(() => {
        theme.value = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        mounted.value = true;
    });

    watch(
        theme,
        (value) => {
            if (! mounted.value || typeof document === 'undefined') {
                return;
            }

            document.documentElement.classList.toggle('dark', value === 'dark');
            document.documentElement.style.colorScheme = value;
        },
    );

    const toggleTheme = (): void => {
        theme.value = theme.value === 'dark' ? 'light' : 'dark';

        try {
            window.localStorage.setItem('theme', theme.value);
        } catch {
            // Storage may be unavailable in privacy-restricted browser contexts.
        }
    };

    return { theme, toggleTheme };
};
