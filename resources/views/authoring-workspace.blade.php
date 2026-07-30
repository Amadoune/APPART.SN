<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>APPART — Espace d’édition</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-950 text-stone-100">
<main class="mx-auto max-w-3xl px-6 py-16">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-400">Property & Listing Authoring</p>
    <h1 class="mt-3 text-4xl font-semibold">Construire et soumettre une annonce</h1>
    <p class="mt-4 max-w-2xl text-stone-300">
        Ce parcours privé utilise votre session active. L’identité du compte n’est jamais saisie ni transmise par le formulaire.
    </p>

    <form data-authoring-journey class="mt-10 grid gap-5 rounded-2xl border border-stone-800 bg-stone-900 p-6">
        <label class="grid gap-2">
            <span>Étape</span>
            <select name="operation" class="rounded-lg bg-stone-950 p-3">
                <option value="initiate-property">Initier le bien</option>
                <option value="create-listing">Créer le brouillon</option>
                <option value="update-draft">Modifier le brouillon</option>
                <option value="submit-listing">Soumettre à publication</option>
            </select>
        </label>
        <label class="grid gap-2">
            <span>Property ID</span>
            <input name="propertyId" type="text" inputmode="text" autocomplete="off" class="rounded-lg bg-stone-950 p-3">
        </label>
        <label class="grid gap-2">
            <span>Listing ID</span>
            <input name="listingId" type="text" inputmode="text" autocomplete="off" class="rounded-lg bg-stone-950 p-3">
        </label>
        <label class="grid gap-2">
            <span>Revision ID</span>
            <input name="revisionId" type="text" inputmode="text" autocomplete="off" class="rounded-lg bg-stone-950 p-3">
        </label>
        <label class="grid gap-2">
            <span>Version attendue</span>
            <input name="expectedVersion" type="number" min="0" value="0" class="rounded-lg bg-stone-950 p-3">
        </label>
        <label class="grid gap-2">
            <span>Titre</span>
            <input name="title" type="text" maxlength="180" class="rounded-lg bg-stone-950 p-3">
        </label>
        <label class="grid gap-2">
            <span>Description</span>
            <textarea name="description" maxlength="20000" class="min-h-32 rounded-lg bg-stone-950 p-3"></textarea>
        </label>
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="grid gap-2">
                <span>Transaction</span>
                <select name="transactionKind" class="rounded-lg bg-stone-950 p-3">
                    <option value="">—</option>
                    <option value="sale">Vente</option>
                    <option value="rent">Location</option>
                </select>
            </label>
            <label class="grid gap-2">
                <span>Prix (unité mineure)</span>
                <input name="priceMinor" type="number" min="0" class="rounded-lg bg-stone-950 p-3">
            </label>
        </div>
        <input name="currency" type="hidden" value="XOF">
        <input name="contactPreference" type="hidden" value="platform">
        <button class="rounded-lg bg-amber-400 px-5 py-3 font-semibold text-stone-950 hover:bg-amber-300" type="submit">
            Exécuter l’étape
        </button>
        <output data-authoring-result aria-live="polite" class="text-sm text-stone-300"></output>
    </form>
</main>
</body>
</html>
