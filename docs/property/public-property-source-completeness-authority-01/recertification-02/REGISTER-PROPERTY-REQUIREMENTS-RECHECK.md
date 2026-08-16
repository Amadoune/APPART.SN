# RegisterProperty Requirements Recheck

La signature actuelle exige :

| Entrée | Type | Nullabilité |
|---|---|---|
| id | `PropertyId` | non |
| reference | `PropertyReference` | non |
| type | `PropertyType` | non |
| surface | `SurfaceArea` | oui |
| rooms | `RoomCount` | non |
| bathrooms | `BathroomCount` | non |
| year | `ConstructionYear` | oui |
| address | `Address` | oui selon `PropertyTypePolicy` |
| address.id | `AddressId` | si Address |
| address.placeId | `GeographicPlaceId` | si Address |
| address.line | `AddressLine` | si Address |
| businessYear | `BusinessYear` | non |
| at | `DateTimeImmutable` | non |

Avant construction de Property, une Address présente est revalidée par `GeographicPlaceCatalog`; seul `Usable` poursuit. `PropertyTypePolicy` conserve l’autorité sur les invariants et la présence de l’Address. Aucun use case n’a été modifié.
