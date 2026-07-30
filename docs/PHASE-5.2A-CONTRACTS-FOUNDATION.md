# Phase 5.2A — Contracts Foundation

## 1. Statut

Ce dossier définit les contrats normatifs documentaires de Property & Listing
Authoring. Il ne crée aucune interface PHP ni implémentation.

## 2. Conventions communes

- tous les identifiants sont opaques, stables et non dérivés d'une PII ;
- toute mutation porte un `intentId` UUID, un `requestedAt` et, hors création,
  une `expectedVersion` ;
- même intent et même checksum : `AlreadyApplied` ;
- même intent et checksum différent : `DivergentIntent` ;
- les résultats sont fermés et ne transportent aucun Aggregate ;
- les lectures sont owner-scoped et fail-closed ;
- aucun contrat ne promet une transaction ACID cross-domain ;
- version initiale de tous les contrats : V1.

## 3. Ports propriétaires

| Port normatif | Owner | Nature |
|---|---|---|
| `PropertyAuthoringCommandPortV1` | PropertyAuthoring | commandes |
| `PropertyAuthoringQueryPortV1` | PropertyAuthoring | lectures privées |
| `ListingDraftCommandPortV1` | ListingAuthoringDraft | commandes |
| `ListingDraftQueryPortV1` | ListingAuthoringDraft | lectures privées |
| `ListingOwnershipCommandPortV1` | ListingOwnership | commandes |
| `ListingOwnershipQueryPortV1` | ListingOwnership | autorisation |
| `AuthoringPortfolioQueryPortV1` | AuthoringPortfolio | projection privée |
| `AuthoringCompletenessPolicyV1` | ListingAuthoringDraft | politique pure |
| `ListingPublicationHandoffPortV1` | Listing Publication F-01 | commande externe |

## 4. Ports consommés

- IAM `AccountAvailability` et identité de session F-17, lecture seule ;
- Geography Place availability, lecture seule ;
- Property registry/commands publics, sans SQL ;
- Property Lifecycle availability F-02, lecture seule ;
- Listing lookup et Listing Publication F-01 par contrat public ;
- Media availability, lecture seule et optionnelle avant 5.2B.

## 5. Règles de confidentialité

Les diagnostics internes, checksums, permissions détaillées et causes de refus
ne sont jamais des diagnostics HTTP publics. Les contrats Event excluent PII,
adresse, description, prix privé, session et token.
