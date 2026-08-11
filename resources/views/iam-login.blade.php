<x-layouts.public
    title="Connexion — APPART.SN"
    description="Connectez-vous à votre espace propriétaire APPART.SN."
    :canonical="route('iam-web-entry.login')"
    robots="noindex, nofollow"
    og-type="website"
>
    <section class="iam-entry">
        <div class="shell-container iam-entry__grid">
            <div class="iam-entry__intro">
                <p class="shell-kicker">Espace propriétaire</p>
                <h1>Ravi de vous revoir.</h1>
                <p>Connectez-vous pour retrouver votre espace et poursuivre la création de votre annonce.</p>
                <ul aria-label="Garanties de connexion">
                    <li>Session sécurisée</li>
                    <li>Accès strictement personnel</li>
                    <li>Vos identifiants ne sont jamais affichés</li>
                </ul>
            </div>

            <div class="iam-entry__card">
                @if (request()->boolean('logged_out'))
                    <p class="iam-entry__notice" role="status">Vous êtes maintenant déconnecté.</p>
                @endif
                <form data-iam-login-form data-next="{{ request()->query('next', route('public-authoring.workspace', absolute: false)) }}" novalidate>
                    <div>
                        <label for="iam-identifier">Identifiant</label>
                        <input id="iam-identifier" name="identifier" type="text" autocomplete="username" required aria-describedby="iam-identifier-hint">
                        <small id="iam-identifier-hint">Votre adresse e-mail ou votre téléphone.</small>
                    </div>
                    <div>
                        <label for="iam-credential">Mot de passe</label>
                        <input id="iam-credential" name="credential" type="password" autocomplete="current-password" minlength="8" required>
                    </div>
                    <button class="shell-button shell-button--primary" type="submit" data-iam-login-submit>
                        <span data-iam-login-label>Se connecter</span>
                    </button>
                    <p class="iam-entry__status" data-iam-login-status role="status" aria-live="polite"></p>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>
