# Phase 5.2A — Implementation Foundation

## Périmètre réalisé

- port Application public `CreateListingDraftV1` ;
- `CreateListingDraftCommandV1` et résultat V1 fermé ;
- orchestrateur Application déterministe ;
- journal d'intents Listing owner-scoped ;
- migration additive 055 et rollback ;
- adapter PostgreSQL du journal ;
- transaction locale avec savepoints ;
- intégration au use case `CreateDraft` sans accès direct au repository depuis
  un consommateur ;
- tests Unit, Architecture, PostgreSQL, rollback et concurrence.

## Frontières

Le Domain Listing, ses états, transitions et événements sont inchangés.
`ListingTransaction` conserve son contrat existant. L'adapter PostgreSQL
implémente séparément le nouveau contrat Application de transaction.

La migration 055 ajoute uniquement
`listing_lifecycle.listing_creation_intents`. Elle ne contient aucune FK
cross-domain, cascade ou PII et ne modifie aucune migration 001–054.

## Résultats

- replay identique : `AlreadyApplied` ;
- même intent divergent : `DivergentIntent` ;
- ListingId concurrent indépendant : `ListingIdConflict` ;
- Property indisponible : rollback intent, racine et révision ;
- transaction englobante : savepoint et rollback intégral.
