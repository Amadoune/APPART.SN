# Phase 5.2A — Persistence Migration Plan

## Migration 056 — Property Authoring

Owner : **RealEstateCatalog / PropertyAuthoring**.

La migration crée le schéma `real_estate_catalog_authoring` et la table
`property_authoring`. La clé `property_id` est propriétaire ; `owner_account_id`,
`version`, `last_intent_id` et `last_checksum` portent les invariants
d’ownership, de concurrence et de replay.

Le rollback supprime uniquement la table puis le schéma propriétaire.

## Migration 057 — Listing Authoring

Owner : **ListingLifecycle**, avec séparation logique des autorités.

La migration crée le schéma `listing_authoring` et les tables :

- `drafts` et `draft_revisions` pour ListingAuthoringDraft ;
- `ownerships` et `delegations` pour ListingOwnership ;
- `portfolio_items` pour AuthoringPortfolio.

Le rollback retire ces tables dans l’ordre inverse de création puis supprime le
schéma. Il ne touche ni la migration 055, ni les tables Listing Publication,
Property Lifecycle, IAM, Projection ou Outbox.

## Règles communes

Les migrations ne contiennent ni `ALTER` d’une structure existante, ni FK
cross-domain, ni `ON DELETE CASCADE`. Leur application et leur rollback sont
déterministes et confinés à leur owner.
