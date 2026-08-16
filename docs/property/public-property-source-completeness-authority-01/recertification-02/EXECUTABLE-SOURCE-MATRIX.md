# Executable Source Matrix

| Entrée | Source | Owner | Représentation/exécution | Revalidation | Verdict |
|---|---|---|---|---|---|
| PropertyId | snapshot F4 | Authoring/serveur | `PropertyId::fromString` | Domain VO | EXECUTABLE |
| PropertyReference | snapshot F4 | owner-authored, RealEstateCatalog | `PropertyReference::fromString` | Domain/Registry unicité | EXECUTABLE |
| PropertyType | snapshot F4 | owner-authored, RealEstateCatalog | `PropertyType::from` | `PropertyTypePolicy` | EXECUTABLE |
| SurfaceArea | snapshot F4 | owner-authored | nullable, `fromSquareMeters` | Domain VO/policy | EXECUTABLE |
| RoomCount | snapshot F4 | owner-authored | `RoomCount::fromInt` | Domain VO/policy | EXECUTABLE |
| BathroomCount | snapshot F4 | owner-authored | `BathroomCount::fromInt` | Domain VO/policy | EXECUTABLE |
| ConstructionYear | snapshot F4 | owner-authored | nullable, `fromInt` | Domain VO/policy | EXECUTABLE |
| AddressId | PropertyId + addressIntentId F4 | F2 RealEstateCatalog | `AddressIdentityIssuerV1` UUIDv5 | issuer déterministe | EXECUTABLE |
| GeographicPlaceId | sélection/replay F1/F4 | Geography + Authoring | `GeographicPlaceId::fromString` | Catalog F5-A courant | EXECUTABLE |
| AddressLine | snapshot F4 | owner-authored | `AddressLine::fromString` | Domain VO | EXECUTABLE |
| BusinessYear | occurredAt stable | F3 RealEstateCatalog | `BusinessYearAuthorityV1` | UTC, sans clock | EXECUTABLE |
| occurredAt | future commande F6 | command caller selon Blueprint | instant absolu stable | `PropertyDecisionOccurredAt` | REPRESENTABLE/EXECUTABLE |

`ownerAccountId` ne fait pas partie de la signature RegisterProperty mais reste dans le snapshot et garantit la lecture owner-scoped de la future commande. Aucune source ne dépend de P02, Projection ou Search.
