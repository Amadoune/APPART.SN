# Historical Redirect Contract Analysis

## Contexte

`PublicListingQuery` adresse exclusivement une canonical publique courante. Une URL historique est une identité de lecture différente, dont la décision appartient à `ContentSeo`. La résolution historique doit donc être un port applicatif autonome et ne doit ni interroger le modèle métier depuis le Web, ni recalculer une décision SEO.

## Frontière retenue

`HistoricalRedirectResolver::resolve(HistoricalCanonical)` retourne un `HistoricalRedirectResolution`. Le type d'entrée atteste que l'appelant a déjà qualifié l'identité comme historique ; une chaîne libre ou une canonical courante ne peut pas être passée au contrat. Le resolver ne reçoit aucun `ListingId`.

La sortie expose au plus un `HistoricalRedirectTarget`. Ce type encapsule une `CanonicalUrl` publique normalisée par `ContentSeo`. Aucun identifiant interne, Aggregate ou Repository ne traverse la frontière.

## Invariants structurels

- `Resolved` possède exactement une destination publique et aucun diagnostic.
- Tout autre statut possède exactement son diagnostic typé et aucune destination.
- La fabrique `resolved` refuse une destination identique à la source.
- Une destination historique produit `ChainDetected`, jamais une seconde résolution.
- Plusieurs destinations produisent `Ambiguous`, jamais un choix arbitraire.
- Le contrat consomme une décision persistée future ; il ne contient aucune politique de canonical ou de SEO.

## Hors périmètre 3.10A

Persistance, migration, PostgreSQL, mapper, binding, Service Provider, HTTP, Laravel, Runtime, reconstruction SEO, lecture d'Aggregate et Repository métier.
