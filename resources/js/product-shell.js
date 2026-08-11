const menuButton = document.querySelector('[data-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

if (menuButton instanceof HTMLButtonElement && mobileMenu instanceof HTMLElement) {
    menuButton.addEventListener('click', () => {
        const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', String(!isOpen));
        mobileMenu.hidden = isOpen;
    });
}

const shellNotice = document.querySelector('[data-shell-notice]');

document.querySelectorAll('[data-shell-action]').forEach((action) => {
    action.addEventListener('click', () => {
        if (!(shellNotice instanceof HTMLElement)) {
            return;
        }

        shellNotice.hidden = false;
        window.setTimeout(() => {
            shellNotice.hidden = true;
        }, 3600);
    });
});
