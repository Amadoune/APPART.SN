# Final Catch-up Model

Le catch-up sélectionne des ListingIds déjà Published puis appelle exactement le même `MaterializePublicSearchDecisionV1`.

Il utilise les mêmes readers owners, `SearchVisibilityPolicy`, ranking policy v1, modèle de révisions, identité, version et Writer. Il n'existe aucune valeur, branche ou écriture spéciale RC2.

Chaque Listing est indépendant. Un échec fermé n'empêche pas la reprise idempotente d'un autre Listing.
