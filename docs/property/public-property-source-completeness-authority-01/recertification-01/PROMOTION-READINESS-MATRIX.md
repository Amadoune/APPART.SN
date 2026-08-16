# Promotion Readiness Matrix

| Entrée RegisterProperty | Source | Owner | Exécutable | Revalidation |
|---|---|---|---:|---|
| PropertyId | PropertyAuthoringState | Authoring | Oui | PropertyId |
| PropertyReference | PropertyAuthoringState | propriétaire / Authoring | Oui | Domain + unicité Registry |
| PropertyType | PropertyAuthoringState | propriétaire / Authoring | Oui | Domain |
| Surface | PropertyAuthoringState | propriétaire / Authoring | Oui | Domain policy |
| Rooms | PropertyAuthoringState | propriétaire / Authoring | Oui | Domain policy |
| Bathrooms | PropertyAuthoringState | propriétaire / Authoring | Oui | Domain policy |
| ConstructionYear | PropertyAuthoringState | propriétaire / Authoring | Oui | Domain policy + BusinessYear |
| GeographicPlaceId | F1/F4-A/F4 | Geography / Authoring | Oui | **Non exécutable : GeographicPlaceCatalog sans implémentation** |
| AddressLine | PropertyAuthoringState | propriétaire / Authoring | Oui | AddressLine |
| AddressId | F2 depuis AddressIntentId | RealEstateCatalog | Oui | UUIDv5 déterministe |
| BusinessYear | F3 depuis occurredAt | RealEstateCatalog | Oui | UTC |
| occurredAt | future commande stable | orchestration future | Représentable | PropertyDecisionOccurredAt |

La ligne GeographicPlaceId interdit la readiness Promotion.
