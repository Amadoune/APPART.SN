const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const requestedAt = () => new Date().toISOString().replace(/\.([0-9]{3})Z$/, '.$1000Z');

const localDestination = (candidate, fallback) => {
    if (typeof candidate !== 'string' || !candidate.startsWith('/') || candidate.startsWith('//')) {
        return fallback;
    }

    return candidate;
};

const loginForm = document.querySelector('[data-iam-login-form]');

if (loginForm instanceof HTMLFormElement) {
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = loginForm.querySelector('[data-iam-login-submit]');
        const label = loginForm.querySelector('[data-iam-login-label]');
        const status = loginForm.querySelector('[data-iam-login-status]');
        const data = new FormData(loginForm);

        if (!(submit instanceof HTMLButtonElement) || !(status instanceof HTMLElement) || !loginForm.reportValidity()) return;

        submit.disabled = true;
        status.classList.remove('iam-entry__status--error');
        status.textContent = 'Connexion sécurisée en cours…';
        if (label instanceof HTMLElement) label.textContent = 'Connexion…';

        try {
            const response = await fetch('/api/identity-access/login', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'Idempotency-Key': crypto.randomUUID(),
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({
                    identifier: String(data.get('identifier') ?? ''),
                    credential: String(data.get('credential') ?? ''),
                    requestedAt: requestedAt(),
                }),
            });
            const result = await response.json();
            if (!response.ok || result.status !== 'succeeded') {
                throw new Error(response.status === 401 ? 'authentication_failed' : 'unavailable');
            }

            status.textContent = 'Connexion réussie. Ouverture de votre espace…';
            window.location.assign(localDestination(loginForm.dataset.next, '/authoring/workspace'));
        } catch (error) {
            status.classList.add('iam-entry__status--error');
            status.textContent = error instanceof Error && error.message === 'authentication_failed'
                ? 'Identifiant ou mot de passe incorrect.'
                : 'Connexion momentanément indisponible. Réessayez.';
            submit.disabled = false;
            if (label instanceof HTMLElement) label.textContent = 'Se connecter';
        }
    });
}

const logout = document.querySelector('[data-iam-logout]');

if (logout instanceof HTMLButtonElement) {
    logout.addEventListener('click', async () => {
        const status = document.querySelector('[data-iam-logout-status]');
        logout.disabled = true;
        if (status instanceof HTMLElement) status.textContent = 'Déconnexion en cours…';

        try {
            const response = await fetch('/api/identity-access/logout', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'Idempotency-Key': crypto.randomUUID(),
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },
                body: JSON.stringify({ requestedAt: requestedAt() }),
            });
            const result = await response.json();
            if (!response.ok || result.status !== 'succeeded') throw new Error('logout_failed');
            window.location.assign('/connexion?logged_out=1');
        } catch {
            logout.disabled = false;
            if (status instanceof HTMLElement) status.textContent = 'Déconnexion impossible. Réessayez.';
        }
    });
}
