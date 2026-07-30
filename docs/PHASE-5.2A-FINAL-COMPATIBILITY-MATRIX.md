# Phase 5.2A — Final Compatibility Matrix

| Frontière | Lecture autorisée | Écriture autorisée | Garantie |
|---|---|---|---|
| F-01 Listing Publication | contrat public et handoff Submit | aucune écriture directe | lifecycle inchangé |
| F-02 Property Lifecycle | contrats publics uniquement | aucune | lifecycle inchangé |
| F-11 Public Projection | aucune autorité Authoring | aucune | projection inchangée |
| F-14 Runtime Health | inspection historique | aucune extension | 58 capacités |
| F-15 Delivery / Outbox | aucune | aucune | inchangé |
| F-17 IAM | session et Account Availability publics | aucune | auto-scope, fail-closed |
| F-18 IAM Migrations | aucune | aucune | 044–054 inchangées |
| PropertyAuthoring | reader propriétaire | store propriétaire | owner unique |
| ListingAuthoringDraft | reader propriétaire | store propriétaire | révisions append-only |
| ListingOwnership | authorization reader | ownership/delegations | titulaire unique |
| AuthoringPortfolio | query privée | projection propriétaire | reconstruisible |
| Listing Creation | `CreateListingDraftV1` | transaction Listing locale | intent déterministe |
| Public Authoring | Operations uniquement | aucune SQL directe | façade additive |

Il n’existe aucune dépendance circulaire, FK cross-domain, cascade, transaction
ACID multi-owner ou écriture SQL dans une capacité gelée.
