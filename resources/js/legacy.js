import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();
const initializeAdminMobileMenu = () => {
    const dialog = document.querySelector('[data-admin-menu-dialog]');
    const openButton = document.querySelector('[data-admin-menu-open]');

    if (!(dialog instanceof HTMLDialogElement) || !(openButton instanceof HTMLButtonElement)) {
        return;
    }

    const closeButtons = dialog.querySelectorAll('[data-admin-menu-close]');
    const desktopMediaQuery = window.matchMedia('(min-width: 1024px)');
    let previousBodyOverflow = '';

    const setOpenState = (open) => {
        openButton.setAttribute('aria-expanded', String(open));
    };

    const closeMenu = () => {
        if (dialog.open) {
            dialog.close();
        }
    };

    const openMenu = () => {
        if (dialog.open) {
            return;
        }

        previousBodyOverflow = document.body.style.overflow;
        dialog.showModal();
        document.body.style.overflow = 'hidden';
        setOpenState(true);
    };

    openButton.addEventListener('click', openMenu);
    closeButtons.forEach((button) => button.addEventListener('click', closeMenu));

    dialog.addEventListener('close', () => {
        document.body.style.overflow = previousBodyOverflow;
        setOpenState(false);
        openButton.focus();
    });

    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) {
            return;
        }

        const bounds = dialog.getBoundingClientRect();
        const insideDialog = event.clientX >= bounds.left
            && event.clientX <= bounds.right
            && event.clientY >= bounds.top
            && event.clientY <= bounds.bottom;

        if (!insideDialog) {
            closeMenu();
        }
    });

    desktopMediaQuery.addEventListener('change', (event) => {
        if (event.matches) {
            closeMenu();
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAdminMobileMenu, { once: true });
} else {
    initializeAdminMobileMenu();
}
