<script>
(() => {
    const root = document.documentElement;
    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initial = stored ?? (prefersDark ? 'dark' : 'light');
    const apply = (theme) => {
        root.classList.toggle('dark', theme === 'dark');
        root.style.colorScheme = theme;
    };
    apply(initial);
    window.toggleTheme = () => {
        const next = root.classList.contains('dark') ? 'light' : 'dark';
        apply(next);
        localStorage.setItem('theme', next);
    };
})();
</script>
