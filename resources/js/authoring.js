const form = document.querySelector('[data-authoring-journey]');

if (form instanceof HTMLFormElement) {
    const output = document.querySelector('[data-authoring-result]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const values = new FormData(form);
        const operation = String(values.get('operation') ?? '');
        const fields = {
            'initiate-property': ['propertyId'],
            'update-property': ['propertyId'],
            'create-listing': ['propertyId', 'listingId', 'revisionId', 'title', 'description', 'transactionKind', 'priceMinor', 'currency', 'chargesMinor', 'availabilityDate', 'contactPreference'],
            'update-draft': ['listingId', 'title', 'description', 'transactionKind', 'priceMinor', 'currency', 'chargesMinor', 'availabilityDate', 'contactPreference'],
            'grant-delegation': ['listingId', 'delegateAccountId', 'permissions'],
            'revoke-delegation': ['listingId', 'delegateAccountId', 'permissions'],
            'submit-listing': ['listingId'],
        }[operation] ?? [];
        const payload = Object.fromEntries(
            [...values.entries()].filter(([key, value]) => fields.includes(key) && value !== ''),
        );
        payload.expectedVersion = Number(payload.expectedVersion ?? 0);
        payload.requestedAt = new Date().toISOString().replace(/(\.\d{3})Z$/, '$1000Z');

        const response = await fetch(`/api/public-authoring/v1/${encodeURIComponent(operation)}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'Idempotency-Key': crypto.randomUUID(),
                'X-CSRF-TOKEN': String(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''),
            },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (output instanceof HTMLOutputElement) {
            output.value = result.status ?? 'unavailable';
            output.dataset.state = response.ok ? 'success' : 'error';
        }
    });
}
