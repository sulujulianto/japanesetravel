<script>
(() => {
    const root = document.documentElement;
    let stored = null;
    try {
        stored = localStorage.getItem('theme');
    } catch (error) {
        stored = null;
    }
    const initial = stored === 'dark' || stored === 'light'
        ? stored
        : (window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    const updateControls = (theme) => {
        document.querySelectorAll('[data-theme-toggle]').forEach((control) => {
            const targetsDark = theme === 'light';
            const label = targetsDark ? control.dataset.darkLabel : control.dataset.lightLabel;
            const actionLabel = targetsDark ? control.dataset.useDarkLabel : control.dataset.useLightLabel;
            const visibleLabel = control.querySelector('[data-theme-toggle-label]');
            const darkIcon = control.querySelector('[data-theme-dark-icon]');
            const lightIcon = control.querySelector('[data-theme-light-icon]');

            control.setAttribute('aria-pressed', String(theme === 'dark'));
            if (actionLabel) {
                control.setAttribute('aria-label', actionLabel);
                control.setAttribute('title', actionLabel);
            }
            if (visibleLabel && label) visibleLabel.textContent = label;
            darkIcon?.classList.toggle('hidden', ! targetsDark);
            lightIcon?.classList.toggle('hidden', targetsDark);
        });
    };
    const apply = (theme) => {
        root.classList.toggle('dark', theme === 'dark');
        root.style.colorScheme = theme;
        updateControls(theme);
    };
    apply(initial);
    window.addEventListener('DOMContentLoaded', () => updateControls(initial), { once: true });
    window.toggleTheme = () => {
        const next = root.classList.contains('dark') ? 'light' : 'dark';
        apply(next);
        try {
            localStorage.setItem('theme', next);
        } catch (error) {
            // Ignore storage errors (private mode, quota, etc).
        }
    };
})();
</script>
