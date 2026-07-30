# Phase 5.2A — Persistence Foundation

## Statut

**GO CERTIFIÉ — FERMÉ.**

## Périmètre livré

La fondation matérialise quatre autorités additives sans modifier les
lifecycles certifiés :

| Autorité | Owner physique | Persistance |
|---|---|---|
| PropertyAuthoring | RealEstateCatalog | `real_estate_catalog_authoring.property_authoring` |
| ListingAuthoringDraft | ListingLifecycle | `listing_authoring.drafts`, `listing_authoring.draft_revisions` |
| ListingOwnership | ListingLifecycle | `listing_authoring.ownerships`, `listing_authoring.delegations` |
| AuthoringPortfolio | ListingLifecycle | `listing_authoring.portfolio_items` |

Chaque autorité dispose d’un état Application, d’un port propriétaire, d’un
mapper déterministe et d’un store PostgreSQL owner-scoped. Aucun port
Application ne dépend d’Infrastructure ou d’un autre owner.

## Garanties

- migrations 056 et 057 exclusivement additives ;
- aucune modification des migrations 001–055 ;
- aucune FK cross-domain, aucune cascade ;
- aucune écriture dans les schémas historiques ou gelés ;
- optimistic locking sur les racines mutables ;
- idempotence par `intent_id` et checksum canonique ;
- association Property/Listing et ownership initial immuables ;
- révisions de brouillon append-only ;
- checkpoints de portfolio monotones ;
- transactions locales et participation sûre à une transaction englobante.

## Frontières préservées

F-01, F-02, F-11, F-14, F-15, F-17 et F-18 restent intangibles. Aucun Runtime,
HTTP, Event V1, Delivery, Outbox ou provider n’est introduit ou modifié.
