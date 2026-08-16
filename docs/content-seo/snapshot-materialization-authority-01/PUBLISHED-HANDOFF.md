# Published Handoff

Chemin normal futur : `ListingPublished` → matérialiseur ContentSeo → readers owner → writer snapshot → reader Found → DecisionTime Found → Projection readiness.

L’événement transporte ListingId, révision Published, expiration et temps. Le matérialiseur doit relire titre/description, Property/Geography et Search ; il ne doit pas gonfler l’événement.

Le consumer Listing Published existant peut composer une seconde destination logique indépendante du handoff Search. Une défaillance ContentSeo doit rester retryable/fail-closed selon son résultat fermé.
