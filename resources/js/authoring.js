const wizard = document.querySelector('[data-listing-wizard]');

if (wizard instanceof HTMLElement) {
    const form = wizard.querySelector('[data-authoring-journey]');
    const steps = [...wizard.querySelectorAll('[data-wizard-step]')];
    const next = wizard.querySelector('[data-wizard-next]');
    const previous = wizard.querySelector('[data-wizard-previous]');
    const submit = wizard.querySelector('[data-submit-listing]');
    const output = wizard.querySelector('[data-authoring-result]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const state = { step: 1, propertyId: crypto.randomUUID(), listingId: crypto.randomUUID(), revisionId: crypto.randomUUID(), propertyVersion: 0, listingVersion: 0, media: [] };

    const values = () => new FormData(form);
    const timestamp = () => new Date().toISOString().replace(/\.([0-9]{3})Z$/, '.$1000Z');
    const tell = (message, error = false) => {
        if (!(output instanceof HTMLElement)) return;
        output.textContent = message;
        output.dataset.state = error ? 'error' : 'success';
    };
    const request = async (operation, payload) => {
        const response = await fetch(`/api/public-authoring/v1/${operation}`, {
            method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'Idempotency-Key': crypto.randomUUID(), 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ ...payload, requestedAt: timestamp() }),
        });
        const result = await response.json();
        if (!response.ok || !['succeeded', 'accepted_replay'].includes(result.status)) throw new Error(result.status ?? 'unavailable');
        return result;
    };
    const chosen = (name) => String(values().get(name) ?? '');
    const fieldsForStep = () => [...(steps[state.step - 1]?.querySelectorAll('input, textarea') ?? [])];
    const valid = () => fieldsForStep().every((field) => !(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) || field.reportValidity());
    const show = (step) => {
        state.step = step;
        steps.forEach((section, index) => { section.hidden = index + 1 !== step; });
        wizard.querySelector('[data-step-current]').textContent = String(step);
        wizard.querySelector('[data-step-progress]').style.width = `${(step / steps.length) * 100}%`;
        wizard.querySelectorAll('[data-progress-step]').forEach((item, index) => item.toggleAttribute('data-complete', index + 1 <= step));
        previous.hidden = step === 1;
        next.hidden = step >= 7;
        tell('');
        steps[step - 1]?.querySelector('input, textarea, button')?.focus();
    };
    const persistProperty = async () => {
        tell('Enregistrement du bien…');
        const result = await request('initiate-property', { propertyId: state.propertyId, expectedVersion: 0, propertyType: chosen('propertyType'), city: chosen('city').trim(), neighborhood: chosen('neighborhood').trim() });
        state.propertyVersion = Number(result.version ?? 1);
    };
    const createListing = async () => {
        tell('Création du brouillon…');
        const data = values();
        const payload = { propertyId: state.propertyId, listingId: state.listingId, revisionId: state.revisionId, expectedVersion: 0, title: chosen('title').trim(), description: chosen('description').trim(), transactionKind: chosen('transactionKind'), priceMinor: Number(chosen('priceMinor')), currency: 'XOF', contactPreference: 'platform' };
        if (chosen('chargesMinor') !== '') payload.chargesMinor = Number(chosen('chargesMinor'));
        if (chosen('availabilityDate') !== '') payload.availabilityDate = chosen('availabilityDate');
        const result = await request('create-listing', payload);
        state.listingVersion = Number(result.version ?? 1);
    };
    const uploadFiles = async () => {
        const input = form.querySelector('input[name="images"]');
        if (!(input instanceof HTMLInputElement) || input.files === null || input.files.length === 0) return;
        tell('Téléversement des photos…');
        for (const [index, file] of [...input.files].entries()) {
            const body = new FormData(); body.append('image', file); body.append('order', String(index + 1));
            const response = await fetch(`/api/authoring/properties/${state.propertyId}/media`, { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'Idempotency-Key': crypto.randomUUID(), 'X-CSRF-TOKEN': csrf }, body });
            const result = await response.json();
            if (!response.ok || !['created', 'available'].includes(result.status)) throw new Error(result.status ?? 'unavailable');
            state.media.push({ mediaId: result.mediaId, name: file.name, url: URL.createObjectURL(file) });
        }
        renderMedia();
    };
    const renderMedia = () => {
        const target = wizard.querySelector('[data-media-preview]');
        if (!(target instanceof HTMLElement)) return;
        target.replaceChildren(...state.media.map((media) => { const figure = document.createElement('figure'); const image = document.createElement('img'); const caption = document.createElement('figcaption'); image.src = media.url; image.alt = ''; caption.textContent = media.name; figure.append(image, caption); return figure; }));
    };
    const renderPreview = () => {
        const transaction = chosen('transactionKind') === 'sale' ? 'À vendre' : 'À louer';
        wizard.querySelector('[data-preview-transaction]').textContent = transaction;
        wizard.querySelector('[data-preview-title]').textContent = chosen('title');
        wizard.querySelector('[data-preview-location]').textContent = `${chosen('neighborhood')}, ${chosen('city')}`;
        wizard.querySelector('[data-preview-description]').textContent = chosen('description');
        wizard.querySelector('[data-preview-price]').textContent = `${new Intl.NumberFormat('fr-SN').format(Number(chosen('priceMinor')))} FCFA`;
        wizard.querySelector('[data-submit-title]').textContent = chosen('title');
        wizard.querySelector('[data-submit-location]').textContent = `${chosen('neighborhood')}, ${chosen('city')}`;
        wizard.querySelector('[data-submit-media]').textContent = `${state.media.length} photo${state.media.length > 1 ? 's' : ''}`;
        const media = wizard.querySelector('[data-preview-media]');
        if (media instanceof HTMLElement && state.media[0]) media.innerHTML = `<img src="${state.media[0].url}" alt="">`;
    };

    next?.addEventListener('click', async () => {
        if (!valid()) return;
        next.disabled = true;
        try {
            if (state.step === 3) await persistProperty();
            if (state.step === 4) await createListing();
            if (state.step === 5) await uploadFiles();
            if (state.step === 5) renderPreview();
            show(state.step + 1);
        } catch { tell('Cette étape n’a pas pu être enregistrée. Vérifiez vos informations et réessayez.', true); }
        finally { next.disabled = false; }
    });
    previous?.addEventListener('click', () => show(Math.max(1, state.step - 1)));
    submit?.addEventListener('click', async () => {
        submit.disabled = true; tell('Soumission au pipeline certifié…');
        try { const result = await request('submit-listing', { listingId: state.listingId, expectedVersion: state.listingVersion }); state.listingVersion = Number(result.version ?? state.listingVersion + 1); tell('Annonce soumise avec succès. Elle entre maintenant en revue avant publication.'); submit.textContent = 'Annonce soumise'; }
        catch { tell('La soumission n’a pas abouti. Réessayez sans quitter cette page.', true); submit.disabled = false; }
    });
    show(1);
}
