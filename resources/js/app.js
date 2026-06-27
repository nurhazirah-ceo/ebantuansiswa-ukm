import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');

    if (!toggle) {
        return;
    }

    const field = toggle.closest('[data-password-field]');
    const input = field?.querySelector('input[type="password"], input[type="text"]');

    if (!input) {
        return;
    }

    const shouldShowPassword = input.type === 'password';
    input.type = shouldShowPassword ? 'text' : 'password';

    toggle.setAttribute('aria-pressed', shouldShowPassword ? 'true' : 'false');
    toggle.setAttribute(
        'aria-label',
        shouldShowPassword ? 'Sembunyi kata laluan' : 'Tunjuk kata laluan'
    );

    toggle.querySelector('[data-password-icon-visible]')?.classList.toggle('hidden', !shouldShowPassword);
    toggle.querySelector('[data-password-icon-hidden]')?.classList.toggle('hidden', shouldShowPassword);
});
