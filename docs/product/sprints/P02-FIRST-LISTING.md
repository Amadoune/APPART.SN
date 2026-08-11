# APPART.SN Product Sprint P02 — First Listing

## Objectif utilisateur

Permettre à un propriétaire particulier de publier une première annonce immobilière locale afin qu'un visiteur puisse la voir sur `http://appart.test` et ouvrir sa fiche publique.

## Objectif métier

Démontrer un parcours réel, owner-scoped et sans contournement : Property, MediaCollection et Listing persistés, transitions `Submit`, `BeginReview` et `ApproveAndPublish`, convergence Registry/Workflow, projection publique active, lecture par `PublicSearchResultsReaderV1`, puis fiche canonique HTTP 200.

## Démonstration

La commande locale suivante orchestre exclusivement les frontières applicatives certifiées :

```text
php artisan appart:local:first-listing
```

Elle refuse de s'exécuter hors `APP_ENV=local` ou si la base PostgreSQL n'est pas `appart_test`. Elle crée ou retrouve une donnée de développement identifiable et converge idempotemment vers :

```text
Property réel
→ MediaCollection réelle avec média principal
→ Listing draft réel
→ Submit
→ BeginReview
→ ApproveAndPublish
→ ListingRegistry Published
→ Workflow Published
→ génération candidate
→ rebuild et manifeste non vide
→ génération active
→ projection current
→ PublicSearchResultsReaderV1
→ accueil
→ annonces/p02-premiere-annonce-dakar
```

## Critères GO

- Property, Listing et MediaCollection réels dans PostgreSQL local.
- Les trois transitions ciblées sont appliquées sans Aggregate forcé.
- Les révisions proviennent de `ListingRevisionAllocatorV1`.
- Registry et Workflow convergent vers `published`.
- L'expiration est calculée mécaniquement à `publishedAt + 90 days`.
- La première génération publique peut être créée, reconstruite, validée et activée sans génération active préalable.
- L'API retourne `available` et au moins un item réel.
- L'accueil expose la vraie annonce et la fiche canonique répond HTTP 200.
- Aucun SQL manuel, Seeder de projection, mock UI ou injection directe de génération active.

## Hors périmètre

Recherche et filtres avancés, favoris, contact, réservation, paiement, SEO avancé, import Legacy, Phase 5.10, modification de R5 et refonte du Design System.

## Décisions produit utilisées

- Publication initiale : 90 jours.
- Renouvellement : +90 jours, hors implémentation P02.
- Republication : même annonce, hors implémentation P02.
- Brouillon sans expiration.
- Annonce expirée invisible mais renouvelable.
- Aucune compensation automatique après suspension.
- `TransitionReason` nullable uniquement pour Submit, BeginReview et ApproveAndPublish.
- Migration additive 092 conservée ; aucune migration historique modifiée.
