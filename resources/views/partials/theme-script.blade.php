<script>
(() => {
    const root = document.documentElement;
    let stored = null;
    try {
        stored = localStorage.getItem('theme');
    } catch (error) {
        stored = null;
    }
    const initial = stored === 'dark' ? 'dark' : 'light';
    const apply = (theme) => {
        root.classList.toggle('dark', theme === 'dark');
        root.style.colorScheme = theme;
    };
    apply(initial);
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
