# Phase 5.2A — Certification Consolidation

## Autorités uniques

| Autorité | Owner | Écriture propriétaire |
|---|---|---|
| PropertyAuthoring | RealEstateCatalog | `real_estate_catalog_authoring.property_authoring` |
| ListingAuthoringDraft | ListingLifecycle | drafts et revisions |
| ListingOwnership | ListingLifecycle | ownerships et delegations |
| AuthoringPortfolio | ListingLifecycle | portfolio_items |

Listing, Property Lifecycle, Listing Publication et Public Projection
conservent leurs owners historiques.

## Résultats certifiés

- création Listing publique, fermée et idempotente ;
- transactions Listing locales avec savepoints ;
- aucune transaction ACID multi-owner ;
- aucune FK cross-domain ni cascade ;
- aucune dépendance Application vers Infrastructure ;
- aucune dépendance directe inter-module ;
- auto-scope IAM et permissions owner/délégation ;
- handoff F-01 exclusivement par `ListingPublicationOrchestrator` ;
- UI/API sans AccountId client et sans PII dans le rate limiting.

## Amendement

`A-5.2A-LISTING-CREATION-BOUNDARY-01` est GO CERTIFIÉ et FERMÉ. Il a produit la
frontière `CreateListingDraftV1` sans modifier la sémantique de F-01.

## Événements et delivery

Aucun nouvel Event V1, transport, routeur, consumer, Delivery ou Outbox n’est
nécessaire à cette tranche. Cette non-introduction est une décision explicite,
pas une étape omise.
