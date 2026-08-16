# Public Search Decision Materialization — Implementation 01

## Périmètre livré

La capacité productive matérialise une décision Search à partir des sources autoritatives Listing, Property et Media, sans lire Public Projection et sans écrire directement dans Search depuis HTTP.

Le chemin est : source owner-scoped en lecture seule → politique de ranking v1 → `SearchProjectionPolicy` existante → identité UUIDv5 → `SearchDecisionWriter` existant.

Les contrats `MaterializePublicSearchDecisionV1` et `CatchUpPublicSearchDecisionV1`, le handoff `ListingPublished`, la commande terminale de catch-up et leurs bindings productifs sont exécutables.

## Bornes

- aucune migration ;
- aucune modification du Search Runtime ou de l’UX Search ;
- aucune lecture de Public Projection ;
- aucune activation de Projection pendant la preuve terminale.
