const wizard = document.querySelector('[data-listing-wizard]');

if (wizard instanceof HTMLElement) {
    const form = wizard.querySelector('[data-authoring-journey]');
    const steps = [...wizard.querySelectorAll('[data-wizard-step]')];
    const next = wizard.querySelector('[data-wizard-next]');
    const previous = wizard.querySelector('[data-wizard-previous]');
    const submit = wizard.querySelector('[data-submit-listing]');
    const output = wizard.querySelector('[data-authoring-result]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const resumeNode = document.querySelector('#authoring-resume-bootstrap');
    let resume = null;
    if (resumeNode instanceof HTMLScriptElement) {
        try { resume = JSON.parse(resumeNode.textContent ?? 'null'); } catch { resume = null; }
    }
    const state = resume?.mode === 'resume'
        ? {
            step: Number(resume.step), propertyId: String(resume.propertyId), listingId: String(resume.listingId), revisionId: null,
            propertyVersion: Number(resume.expectedAuthoringVersion), listingVersion: Number(resume.expectedVersion),
            media: Array.isArray(resume.media?.items) ? resume.media.items.map((item) => ({ ...item, name: item.caption || item.mediaId, url: null })) : [],
            geography: resume.geography, geographyUnavailable: false, resumed: true,
        }
        : { step: 1, propertyId: crypto.randomUUID(), listingId: crypto.randomUUID(), revisionId: crypto.randomUUID(), propertyVersion: 0, listingVersion: 0, media: [], geography: null, geographyUnavailable: false, resumed: false };

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
    const valid = () => {
        if (state.step === 3 && (state.geography === null || state.geographyUnavailable)) {
            tell(state.geographyUnavailable ? 'La géographie est indisponible. Réessayez avant de continuer.' : 'Sélectionnez une localisation proposée.', true);
            return false;
        }
        return fieldsForStep().every((field) => {
            if (state.step === 5 && field instanceof HTMLInputElement && field.type === 'file' && state.media.length > 0) return true;
            return !(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) || field.reportValidity();
        });
    };
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
        const payload = {
            propertyId: state.propertyId, expectedVersion: state.propertyVersion,
            propertyType: chosen('propertyType'), propertyReference: chosen('propertyReference').trim(),
            surfaceSquareMeters: Number(chosen('surfaceSquareMeters')), rooms: Number(chosen('rooms')), bathrooms: Number(chosen('bathrooms')),
            geographicPlaceId: chosen('geographicPlaceId'), geographicPlaceType: chosen('geographicPlaceType'),
            geographicParentPlaceId: chosen('geographicParentPlaceId') || null,
            geographicSelectionCursor: chosen('geographicSelectionCursor') || null,
            geographicSelectionLimit: Number(chosen('geographicSelectionLimit')), addressLine: chosen('addressLine').trim(),
            city: chosen('city').trim(), neighborhood: chosen('neighborhood').trim(),
        };
        if (chosen('constructionYear') !== '') payload.constructionYear = Number(chosen('constructionYear'));
        const result = await request(state.propertyVersion > 0 ? 'update-property' : 'initiate-property', payload);
        state.propertyVersion = Number(result.version ?? state.propertyVersion + 1);
    };

    const geography = wizard.querySelector('[data-geography-selection]');
    const geographyLevels = geography?.querySelector('[data-geography-levels]');
    const geographyStatus = geography?.querySelector('[data-geography-status]');
    const geographyChoice = geography?.querySelector('[data-geography-choice]');
    const geographyChoiceLabel = geography?.querySelector('[data-geography-choice-label]');
    const geographyControllers = new Map();
    const childTypes = {
        country: ['region'], region: ['department', 'city'], department: ['city', 'district'],
        city: ['district', 'neighborhood'], district: ['neighborhood'], neighborhood: [],
    };
    const typeLabels = { country: 'Pays', region: 'Région', department: 'Département', city: 'Ville', district: 'Arrondissement', neighborhood: 'Quartier' };
    const setHidden = (name, value) => {
        const input = form.querySelector(`input[name="${name}"]`);
        if (input instanceof HTMLInputElement) input.value = value ?? '';
    };
    const announceGeography = (message, unavailable = false) => {
        state.geographyUnavailable = unavailable;
        if (geographyStatus instanceof HTMLElement) geographyStatus.textContent = message;
    };
    const clearGeographyAfter = (depth) => {
        geographyControllers.forEach((controller, key) => { if (key > depth) { controller.abort(); geographyControllers.delete(key); } });
        geographyLevels?.querySelectorAll('[data-geography-depth]').forEach((level) => {
            if (Number(level.getAttribute('data-geography-depth')) > depth) level.remove();
        });
    };
    const rememberSelection = (item, proof, path) => {
        if (!['city', 'district', 'neighborhood'].includes(item.type)) {
            state.geography = null; setHidden('geographicPlaceId', '');
            if (geographyChoice instanceof HTMLElement) geographyChoice.hidden = true;
            announceGeography('Continuez vers une ville, un arrondissement ou un quartier.');
            return;
        }
        state.geography = { geographicPlaceId: item.placeId, type: item.type, parentPlaceId: item.parentPlaceId, cursor: proof.cursor, limit: proof.limit, label: item.label, path };
        setHidden('geographicPlaceId', item.placeId); setHidden('geographicPlaceType', item.type);
        setHidden('geographicParentPlaceId', item.parentPlaceId); setHidden('geographicSelectionCursor', proof.cursor);
        setHidden('geographicSelectionLimit', String(proof.limit));
        const city = [...path].reverse().find((entry) => entry.type === 'city');
        const neighborhood = [...path].reverse().find((entry) => entry.type === 'neighborhood');
        setHidden('city', city?.label ?? item.label); setHidden('neighborhood', neighborhood?.label ?? item.label);
        if (geographyChoice instanceof HTMLElement && geographyChoiceLabel instanceof HTMLElement) {
            geographyChoice.hidden = false; geographyChoiceLabel.textContent = path.map((entry) => entry.label).join(' › ');
        }
        announceGeography('Localisation autoritative sélectionnée.');
    };
    const geographyPage = async (type, parentPlaceId, cursor, limit, signal) => {
        const endpoint = geography?.getAttribute('data-endpoint') ?? '/api/authoring/geography/selections';
        const query = new URLSearchParams({ type, limit: String(limit) });
        if (parentPlaceId !== null) query.set('parentPlaceId', parentPlaceId);
        if (cursor !== null) query.set('cursor', cursor);
        const response = await fetch(`${endpoint}?${query}`, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal });
        const body = await response.json();
        if (!response.ok) throw new Error(response.status === 404 ? 'missing' : response.status === 503 ? 'unavailable' : response.status === 500 ? 'corrupted' : 'invalid');
        return body;
    };
    const loadGeographyLevel = async (type, parent, depth, path) => {
        const controller = new AbortController(); geographyControllers.set(depth, controller);
        const limit = 50; let cursor = null; let level = null; let select = null; let more = null;
        announceGeography(`Chargement : ${typeLabels[type]}…`);
        const append = async () => {
            const inputCursor = cursor;
            const page = await geographyPage(type, parent?.placeId ?? null, inputCursor, limit, controller.signal);
            if (!Array.isArray(page.items) || page.items.length === 0) return false;
            if (!(level instanceof HTMLElement)) {
                level = document.createElement('div'); level.dataset.geographyDepth = String(depth); level.className = 'authoring-geography-level';
                const label = document.createElement('label'); label.textContent = typeLabels[type]; select = document.createElement('select'); select.required = depth === 0;
                const prompt = document.createElement('option'); prompt.value = ''; prompt.textContent = `Choisir : ${typeLabels[type]}`; select.append(prompt); label.append(select); level.append(label);
                more = document.createElement('button'); more.type = 'button'; more.className = 'shell-button shell-button--quiet'; more.textContent = 'Charger plus'; level.append(more); geographyLevels?.append(level);
                select.addEventListener('change', async () => {
                    const option = select.selectedOptions[0]; if (!option?.dataset.item) return;
                    const selected = JSON.parse(option.dataset.item); const proof = JSON.parse(option.dataset.proof); const selectedPath = [...path, selected];
                    clearGeographyAfter(depth); rememberSelection(selected, proof, selectedPath);
                    for (const childType of childTypes[selected.type] ?? []) {
                        try { await loadGeographyLevel(childType, selected, depth + 1, selectedPath); }
                        catch (error) { if (error.name !== 'AbortError') announceGeography(error.message === 'unavailable' ? 'Géographie indisponible.' : 'La branche géographique ne peut pas être chargée.', true); }
                    }
                });
                more.addEventListener('click', () => append().catch(() => announceGeography('La page suivante ne peut pas être chargée.', true)));
            }
            page.items.forEach((item) => { const option = document.createElement('option'); option.value = item.placeId; option.textContent = item.label; option.dataset.item = JSON.stringify(item); option.dataset.proof = JSON.stringify({ cursor: inputCursor, limit }); select.append(option); });
            cursor = typeof page.nextCursor === 'string' ? page.nextCursor : null; if (more instanceof HTMLButtonElement) more.hidden = cursor === null;
            return true;
        };
        const available = await append(); if (!available) { level?.remove(); return; }
        announceGeography('Choisissez la localisation la plus précise disponible.');
    };
    if (geography instanceof HTMLElement) {
        loadGeographyLevel('country', null, 0, []).catch((error) => {
            if (error.name !== 'AbortError') announceGeography(error.message === 'unavailable' ? 'Géographie indisponible.' : 'La géographie ne peut pas être chargée.', true);
        });
    }
    const createListing = async () => {
        tell('Création du brouillon…');
        const data = values();
        const payload = { propertyId: state.propertyId, listingId: state.listingId, revisionId: state.revisionId, expectedVersion: state.listingVersion, title: chosen('title').trim(), description: chosen('description').trim(), transactionKind: chosen('transactionKind'), priceMinor: Number(chosen('priceMinor')), currency: 'XOF', contactPreference: 'platform' };
        if (chosen('chargesMinor') !== '') payload.chargesMinor = Number(chosen('chargesMinor'));
        if (chosen('availabilityDate') !== '') payload.availabilityDate = chosen('availabilityDate');
        const operation = state.listingVersion > 0 ? 'update-draft' : 'create-listing';
        if (operation === 'update-draft') {
            delete payload.propertyId; delete payload.listingId; delete payload.revisionId;
        }
        const result = await request(operation, payload);
        state.listingVersion = Number(result.version ?? state.listingVersion + 1);
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
        target.replaceChildren(...state.media.map((media) => { const figure = document.createElement('figure'); const caption = document.createElement('figcaption'); caption.textContent = media.name; if (media.url) { const image = document.createElement('img'); image.src = media.url; image.alt = ''; figure.append(image); } figure.append(caption); return figure; }));
    };
    const renderPreview = () => {
        const transaction = chosen('transactionKind') === 'sale' ? 'À vendre' : 'À louer';
        wizard.querySelector('[data-preview-transaction]').textContent = transaction;
        wizard.querySelector('[data-preview-title]').textContent = chosen('title');
        wizard.querySelector('[data-preview-location]').textContent = state.geography?.path.map((entry) => entry.label).join(' › ') ?? '';
        wizard.querySelector('[data-preview-description]').textContent = chosen('description');
        wizard.querySelector('[data-preview-price]').textContent = `${new Intl.NumberFormat('fr-SN').format(Number(chosen('priceMinor')))} FCFA`;
        wizard.querySelector('[data-submit-title]').textContent = chosen('title');
        wizard.querySelector('[data-submit-location]').textContent = state.geography?.path.map((entry) => entry.label).join(' › ') ?? '';
        wizard.querySelector('[data-submit-media]').textContent = `${state.media.length} photo${state.media.length > 1 ? 's' : ''}`;
        const media = wizard.querySelector('[data-preview-media]');
        if (media instanceof HTMLElement && state.media[0]) media.innerHTML = state.media[0].url ? `<img src="${state.media[0].url}" alt="">` : `${state.media.length} photo${state.media.length > 1 ? 's' : ''} existante${state.media.length > 1 ? 's' : ''}`;
    };

    const hydrate = () => {
        if (resume?.mode !== 'resume') return;
        const fields = { ...(resume.property ?? {}), ...(resume.draft ?? {}) };
        Object.entries(fields).forEach(([name, value]) => {
            if (value === null || value === undefined) return;
            const candidates = form.querySelectorAll(`[name="${name}"]`);
            candidates.forEach((field) => {
                if (field instanceof HTMLInputElement && field.type === 'radio') field.checked = field.value === String(value);
                else if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) field.value = String(value);
            });
        });
        setHidden('geographicPlaceId', resume.geography?.geographicPlaceId ?? '');
        setHidden('geographicPlaceType', resume.geography?.type ?? '');
        setHidden('geographicParentPlaceId', resume.geography?.parentPlaceId ?? '');
        setHidden('geographicSelectionCursor', '');
        setHidden('geographicSelectionLimit', '');
        if (geographyChoice instanceof HTMLElement && geographyChoiceLabel instanceof HTMLElement) {
            geographyChoice.hidden = false;
            geographyChoiceLabel.textContent = (resume.geography?.path ?? []).map((entry) => entry.label).join(' › ');
        }
        renderMedia();
        if (state.step >= 6) renderPreview();
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
        try { const result = await request('submit-listing', { listingId: state.listingId, expectedVersion: state.listingVersion, expectedAuthoringVersion: state.propertyVersion }); state.listingVersion = Number(result.version ?? state.listingVersion + 1); tell('Annonce soumise avec succès. Elle entre maintenant en revue avant publication.'); submit.textContent = 'Annonce soumise'; }
        catch { tell('La soumission n’a pas abouti. Réessayez sans quitter cette page.', true); submit.disabled = false; }
    });
    hydrate();
    show(state.step);
}
