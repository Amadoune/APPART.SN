# F3 — Implementation Evidence

## Composants

- `ProjectPublishedListingV1`, résultat et statut fermés.
- `DeterministicProjectPublishedListing`, composition sans logique Projection.
- `PublicListingProjectionActivation`, port interne minimal.
- `PublicListingProjectionActivationAdapter`, unique adaptateur vers `PublicListingProjectionUpdater`.
- `PublicationReviewProjectionStore`, synchronisation owner-scoped de la file et du ledger.
- Bindings singleton/lazy dans `PublicationReviewQueueServiceProvider`.

## Preuves

- La commande transmet mécaniquement ses quatre valeurs au store.
- L'activation n'est appelée qu'après qualification du QueueItem terminal.
- Un replay identique n'appelle pas une seconde fois Projection.
- Une commande divergente retourne `Conflict`.
- Une publication absente ou non terminale retourne `NotReady` sans appeler Projection.
- La file reste terminale et son historique est conservé.
- Le Projection Updater existant construit réellement le Public Listing Read Model et alimente mécaniquement la projection Search dérivée.

Les migrations 096 et 097 et leurs rollbacks restent inchangés.
