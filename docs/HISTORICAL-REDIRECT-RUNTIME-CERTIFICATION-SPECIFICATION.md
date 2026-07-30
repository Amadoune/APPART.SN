# Historical Redirect Runtime Certification Specification

## Environnement certifié

- Runtime Laravel et conteneur de production.
- PostgreSQL 18 réel.
- `PublicListingQuery`, `HistoricalCanonicalQualifier` et `HistoricalRedirectResolver` liés à leurs adaptateurs certifiés.
- Runtime Health `Healthy`.

## Preuves obligatoires

Le scénario nominal doit exécuter HTTP → projection Current absente → qualification Historical → résolution PostgreSQL Resolved → HTTP 301. La valeur `Location` doit être identique à la destination persistée et certifiée.

Chaque statut fermé doit produire son HTTP documenté et, hors succès, son code d'observabilité typé. Aucun résultat non résolu ne peut contenir `Location`. Une erreur PostgreSQL doit rester 503 et ne jamais devenir 404.

Le bootstrap reste structurel : aucune qualification, résolution ou requête avant la requête HTTP.
