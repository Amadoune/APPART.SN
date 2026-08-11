# P06 — Owner Dashboard (Read Experience)

## Objectif

Créer une première expérience propriétaire strictement read-only, indépendante du Runtime IAM encore indisponible et prête à recevoir ultérieurement une source owner-scoped.

## Architecture de lecture

`OwnerDashboardController` dépend exclusivement de `OwnerDashboardReadSourceV1`. L'adapter courant `PublicProjectionOwnerDashboardReadSource` compose :

- `PublicSearchResultsReaderV1` pour la collection ordonnée ;
- `PublicListingQuery` pour les dates et le statut public certifiés.

Le Controller ne dépend ni de PostgreSQL, ni d'IAM, ni d'Authoring, ni d'un Aggregate. Le remplacement futur de l'adapter par une source owner-scoped ne modifiera pas le design ou le Controller.

## Expérience

- route : `GET /espace-proprietaire` ;
- hiérarchie H1/H2 et statut « Mode lecture » ;
- cartes avec miniature, titre, transaction, type, ville, statut, visibilité, publication et expiration ;
- fiche publique accessible ;
- action de gestion explicitement désactivée ;
- états Empty et Unavailable dédiés ;
- accès depuis les navigations desktop et mobile.

## Limite observée

La projection PostgreSQL locale retourne actuellement `Empty`. Le Dashboard affiche donc l'état vide réel. Aucune donnée fictive ou écriture locale n'a été introduite pour fabriquer une démonstration.
