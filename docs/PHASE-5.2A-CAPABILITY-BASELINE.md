# Phase 5.2A — Property & Listing Authoring Baseline

## Baseline fonctionnelle

- initiation et édition de PropertyAuthoring ;
- création initiale complète d’un Listing ;
- contenu privé de brouillon et révisions append-only ;
- ownership titulaire unique ;
- délégations VIEW, EDIT et SUBMIT ;
- complétude déterministe ;
- portfolio privé et checkpoint monotone ;
- soumission contrôlée vers F-01 ;
- parcours UI/API versionné et sécurisé.

## Baseline technique

- PHP 8.5, Laravel 13, PostgreSQL 18.x et Vite 8 ;
- migrations 055, 056 et 057 ;
- schémas `listing_lifecycle`, `listing_authoring` et
  `real_estate_catalog_authoring` dans leurs slices autorisées ;
- `CreateListingDraftV1` ;
- `PropertyListingAuthoringRuntimeV1` ;
- `PropertyListingAuthoringOperations` ;
- `PublicAuthoringJourney` ;
- providers owner-scoped et additifs ;
- `package-lock.json` comme verrou frontend.

## Invariants gelables

- ListingId ↔ PropertyId immuable ;
- owner Property et titulaire Listing stables en V1 ;
- writes confinés à leur owner ;
- idempotence intentId + checksum ;
- optimistic locking ;
- rollback local intégral ;
- AccountId issu exclusivement de la session ;
- F-01 consommé uniquement par son contrat public.

## Exclusions

Media, anonymisation, Professional Profile, modération, paiement, nouvel Event
V1 et modification des capacités F-01/F-02/F-11/F-14/F-15/F-17/F-18.
