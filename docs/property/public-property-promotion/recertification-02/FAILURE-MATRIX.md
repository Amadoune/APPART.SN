# Matrice des échecs

| Failure | Statut attendu | Property/ledger | Listing/handoffs | Retry |
|---|---|---|---|---|
| AuthoringMissing | AuthoringMissing | aucune mutation | aucune | après source |
| OwnershipMismatch | OwnershipMismatch | aucune mutation | aucune | owner correct |
| IncompleteAuthoring | IncompleteAuthoring | aucune mutation | aucune | après complétion |
| VersionConflict | VersionConflict | aucune mutation | aucune | nouvelle version |
| DomainRejected | DomainRejected | aucune mutation | aucune | après admissibilité |
| DependencyUnavailable | DependencyUnavailable | rollback F6 | aucune | permis |
| commandId/checksum divergent | DivergentCommand | état initial intact | aucune | non automatique |
| Workflow closed failure | réduction existante | Property/ledger durables | rollback complet | déterministe, certifié F7-A |
| Property existante avec AddressId divergent | **DivergentCommand requis ; AlreadyApplied possible** | état initial intact | Submit pourrait poursuivre à tort | **non certifié** |
