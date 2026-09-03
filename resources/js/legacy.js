import './bootstrap';

import Chart from 'chart.js/auto';

window.Chart = Chart;

const syncModalBodyState = () => {
    const hasOpenModal = Array.from(document.querySelectorAll('[data-modal]'))
        .some((modal) => modal instanceof HTMLDialogElement && modal.open);

    document.body.classList.toggle('overflow-y-hidden', hasOpenModal);
};

const initializeModals = () => {
    document.querySelectorAll('[data-modal]').forEach((modal) => {
        if (!(modal instanceof HTMLDialogElement)) {
            return;
        }

        const name = modal.dataset.modalName;
        let returnFocus = null;

        const closeModal = () => {
            if (modal.open) {
                modal.close();
            }
        };

        const openModal = (trigger = null) => {
            if (modal.open) {
                return;
            }

            returnFocus = trigger instanceof HTMLElement
                ? trigger
                : document.activeElement instanceof HTMLElement
                    ? document.activeElement
                    : null;

            modal.showModal();
            syncModalBodyState();

            if (modal.hasAttribute('data-modal-focusable')) {
                window.requestAnimationFrame(() => {
                    modal.querySelector('input:not([type="hidden"]), button, select, textarea, a[href], [tabindex]:not([tabindex="-1"])')?.focus();
                });
            }
        };

        document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
            if (trigger instanceof HTMLElement && trigger.dataset.modalOpen === name) {
                trigger.addEventListener('click', () => openModal(trigger));
            }
        });

        modal.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });

        window.addEventListener('open-modal', (event) => {
            if (event instanceof CustomEvent && event.detail === name) {
                openModal();
            }
        });

        window.addEventListener('close-modal', (event) => {
            if (event instanceof CustomEvent && event.detail === name) {
                closeModal();
            }
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        modal.addEventListener('close', () => {
            syncModalBodyState();
            returnFocus?.focus();
            returnFocus = null;
        });

        if (modal.hasAttribute('data-modal-initially-open')) {
            openModal();
        }
    });
};

const initializeAutoDismiss = () => {
    document.querySelectorAll('[data-auto-dismiss]').forEach((element) => {
        const delay = Number.parseInt(element.getAttribute('data-auto-dismiss') ?? '', 10);

        window.setTimeout(() => {
            element.classList.add('opacity-0');
            element.addEventListener('transitionend', () => element.remove(), { once: true });
        }, Number.isNaN(delay) ? 2000 : delay);
    });
};

const initializeDropdowns = () => {
    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');

        if (!(trigger instanceof HTMLElement) || !(menu instanceof HTMLElement)) {
            return;
        }

        const triggerControl = trigger.matches('button, a[href], [tabindex]')
            ? trigger
            : trigger.querySelector('button, a[href], [tabindex]');

        triggerControl?.setAttribute('aria-expanded', 'false');

        const closeDropdown = () => {
            menu.hidden = true;
            triggerControl?.setAttribute('aria-expanded', 'false');
        };

        trigger.addEventListener('click', () => {
            const shouldOpen = menu.hidden;
            menu.hidden = !shouldOpen;
            triggerControl?.setAttribute('aria-expanded', String(shouldOpen));
        });

        menu.addEventListener('click', closeDropdown);
        document.addEventListener('click', (event) => {
            if (event.target instanceof Node && !dropdown.contains(event.target)) {
                closeDropdown();
            }
        });

        dropdown.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeDropdown();
                triggerControl?.focus();
            }
        });
    });
};

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

const initializeLegacyUi = () => {
    initializeModals();
    initializeAutoDismiss();
    initializeDropdowns();
    initializeAdminMobileMenu();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLegacyUi, { once: true });
} else {
    initializeLegacyUi();
}
