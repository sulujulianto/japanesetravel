import { ref, watch } from 'vue';

export type Theme = 'dark' | 'light';

const readStoredTheme = (): Theme => {
    if (typeof window === 'undefined') {
        return 'light';
    }

    try {
        return window.localStorage.getItem('theme') === 'dark' ? 'dark' : 'light';
    } catch {
        return 'light';
    }
};

export const useTheme = () => {
    const theme = ref<Theme>(readStoredTheme());

    watch(
        theme,
        (value) => {
            if (typeof document === 'undefined') {
                return;
            }

            document.documentElement.classList.toggle('dark', value === 'dark');
            document.documentElement.style.colorScheme = value;
        },
        { immediate: true },
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
