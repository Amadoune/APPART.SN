<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Créer une annonce — APPART.SN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="authoring-experience">
@if (is_array($resumeSnapshot ?? null))
    <script id="authoring-resume-bootstrap" type="application/json">{!! json_encode($resumeSnapshot, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif
<header class="authoring-header">
    <a class="brand" href="{{ route('home') }}" aria-label="APPART.SN — Accueil"><span class="brand__mark" aria-hidden="true">A</span><span>APPART<span class="brand__dot">.</span>SN</span></a>
    <button class="shell-button shell-button--quiet" type="button" data-iam-logout>Se déconnecter</button>
</header>

<main class="authoring-shell" data-listing-wizard>
    @if (is_string($resumeError ?? null))
        <p class="authoring-status" data-state="error" role="alert">Ce brouillon ne peut pas être repris dans son état actuel.</p>
    @endif
    <div class="authoring-progress" aria-label="Progression de création">
        <p><span data-step-current>1</span> sur 7</p>
        <div class="authoring-progress__track"><span data-step-progress></span></div>
        <ol>
            <li data-progress-step="1">Projet</li><li data-progress-step="2">Type</li><li data-progress-step="3">Adresse</li><li data-progress-step="4">Détails</li><li data-progress-step="5">Photos</li><li data-progress-step="6">Aperçu</li><li data-progress-step="7">Envoi</li>
        </ol>
    </div>

    <form data-authoring-journey class="authoring-wizard" novalidate>
        <section data-wizard-step="1" class="authoring-step">
            <p class="shell-kicker">Votre projet</p><h1>Que souhaitez-vous faire ?</h1><p>Choisissez l’intention de votre annonce.</p>
            <div class="authoring-choice-grid">
                <label class="authoring-choice"><input type="radio" name="transactionKind" value="sale" required><strong>Vendre</strong><span>Je propose ce bien à l’achat.</span></label>
                <label class="authoring-choice"><input type="radio" name="transactionKind" value="rent" required><strong>Louer</strong><span>Je propose ce bien à la location.</span></label>
            </div>
        </section>

        <section data-wizard-step="2" class="authoring-step" hidden>
            <p class="shell-kicker">Type de bien</p><h1>Quel bien proposez-vous ?</h1><p>Sélectionnez la catégorie qui correspond le mieux.</p>
            <div class="authoring-type-grid">
                @foreach (['apartment' => 'Appartement', 'house' => 'Maison', 'villa' => 'Villa', 'land' => 'Terrain', 'office' => 'Bureau', 'commercial' => 'Commerce'] as $value => $label)
                    <label class="authoring-choice"><input type="radio" name="propertyType" value="{{ $value }}" required><strong>{{ $label }}</strong></label>
                @endforeach
            </div>
            <div class="authoring-fields">
                <label>Référence du bien<input name="propertyReference" type="text" minlength="4" maxlength="64" pattern="[A-Za-z0-9][A-Za-z0-9._/-]{3,63}" required placeholder="APPART-ALM-001"></label>
                <div class="authoring-fields__row">
                    <label>Surface en m²<input name="surfaceSquareMeters" type="number" min="1" max="10000000" required inputmode="numeric"></label>
                    <label>Pièces<input name="rooms" type="number" min="0" max="1000" required inputmode="numeric"></label>
                    <label>Salles de bain<input name="bathrooms" type="number" min="0" max="1000" required inputmode="numeric"></label>
                </div>
                <label>Année de construction <span>(facultatif)</span><input name="constructionYear" type="number" min="1800" max="9999" inputmode="numeric"></label>
            </div>
        </section>

        <section data-wizard-step="3" class="authoring-step" hidden>
            <p class="shell-kicker">Localisation</p><h1>Où se trouve le bien ?</h1><p>Ces informations permettent de situer clairement l’annonce.</p>
            <div class="authoring-fields" data-geography-selection data-endpoint="{{ route('property-authoring.geography-selections') }}">
                <div data-geography-levels aria-live="polite"></div>
                <p data-geography-status role="status">Chargement du référentiel géographique…</p>
                <p data-geography-choice hidden>Localisation sélectionnée : <strong data-geography-choice-label></strong></p>
                <input name="geographicPlaceId" type="hidden" required>
                <input name="geographicPlaceType" type="hidden">
                <input name="geographicParentPlaceId" type="hidden">
                <input name="geographicSelectionCursor" type="hidden">
                <input name="geographicSelectionLimit" type="hidden" value="50">
                <input name="city" type="hidden" data-legacy-city>
                <input name="neighborhood" type="hidden" data-legacy-neighborhood>
                <label>Adresse du bien<input name="addressLine" type="text" minlength="3" maxlength="255" required autocomplete="street-address" placeholder="12 avenue Cheikh Anta Diop"></label>
            </div>
        </section>

        <section data-wizard-step="4" class="authoring-step" hidden>
            <p class="shell-kicker">Informations</p><h1>Présentez votre bien.</h1><p>Renseignez uniquement les informations prises en charge par votre brouillon.</p>
            <div class="authoring-fields">
                <label>Titre<input name="title" type="text" minlength="1" maxlength="180" required placeholder="Appartement lumineux aux Almadies"></label>
                <label>Description<textarea name="description" maxlength="20000" required placeholder="Décrivez les espaces, l’environnement et les atouts du bien."></textarea></label>
                <div class="authoring-fields__row">
                    <label>Prix en FCFA<input name="priceMinor" type="number" min="0" required inputmode="numeric"></label>
                    <label>Charges en FCFA <span>(facultatif)</span><input name="chargesMinor" type="number" min="0" inputmode="numeric"></label>
                </div>
                <label>Disponible à partir du <input name="availabilityDate" type="date"></label>
                <input name="currency" type="hidden" value="XOF"><input name="contactPreference" type="hidden" value="platform">
            </div>
        </section>

        <section data-wizard-step="5" class="authoring-step" hidden>
            <p class="shell-kicker">Photos</p><h1>Montrez le bien sous son meilleur jour.</h1><p>JPEG, PNG ou WebP, 10 Mo maximum par image.</p>
            <label class="authoring-upload"><input name="images" type="file" accept="image/jpeg,image/png,image/webp" multiple required><span>Choisir des photos</span><small>Les images sont stockées et rattachées par le Runtime Media certifié.</small></label>
            <div class="authoring-media-grid" data-media-preview aria-live="polite"></div>
        </section>

        <section data-wizard-step="6" class="authoring-step" hidden>
            <p class="shell-kicker">Prévisualisation</p><h1>Voici votre future annonce.</h1><p>Relisez les informations réellement saisies avant l’envoi.</p>
            <article class="authoring-preview">
                <div class="authoring-preview__media" data-preview-media>Vos photos apparaîtront ici.</div>
                <div><span data-preview-transaction></span><h2 data-preview-title></h2><p data-preview-location></p><p data-preview-description></p><strong data-preview-price></strong></div>
            </article>
        </section>

        <section data-wizard-step="7" class="authoring-step" hidden>
            <p class="shell-kicker">Soumission</p><h1>Votre annonce est prête.</h1><p>En la soumettant, vous l’envoyez dans le pipeline de publication certifié.</p>
            <div class="authoring-submit-summary"><strong data-submit-title></strong><span data-submit-location></span><span data-submit-media></span></div>
            <button class="shell-button shell-button--primary authoring-submit" type="button" data-submit-listing>Soumettre l’annonce</button>
        </section>

        <div class="authoring-navigation">
            <button class="shell-button shell-button--quiet" type="button" data-wizard-previous hidden>Retour</button>
            <button class="shell-button shell-button--primary" type="button" data-wizard-next>Continuer</button>
        </div>
        <p class="authoring-status" data-authoring-result role="status" aria-live="polite"></p>
    </form>
</main>
</body>
</html>
