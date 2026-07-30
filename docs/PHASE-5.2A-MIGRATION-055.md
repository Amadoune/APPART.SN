# Phase 5.2A — Migration 055

## Owner

`ListingLifecycle`, table `listing_lifecycle.listing_creation_intents`.

## Contraintes

- clé primaire `(operation, intent_id)` ;
- opération fermée `CreateListingDraftV1` ;
- checksum SHA-256 canonique ;
- résultats persistés `pending` ou `applied` ;
- version obligatoire uniquement pour `applied` ;
- index local sur ListingId ;
- aucun FK, aucune cascade, aucune PII.

## Rollback

Le down supprime uniquement la nouvelle table. Les tables Listing, révisions,
workflow, inbox et Outbox existantes ne sont jamais modifiées.
