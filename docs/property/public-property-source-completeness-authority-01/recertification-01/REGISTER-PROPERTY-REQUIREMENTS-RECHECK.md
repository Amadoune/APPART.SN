# RegisterProperty Requirements Recheck

Le contrat actuel est inchangé. `RegisterProperty::execute` exige :

| Entrée | Forme Domain |
|---|---|
| identité | `PropertyId` |
| référence | `PropertyReference` |
| type | `PropertyType` |
| surface | `?SurfaceArea` |
| pièces | `RoomCount` |
| salles de bain | `BathroomCount` |
| construction | `?ConstructionYear` |
| adresse | `?Address(AddressId, GeographicPlaceId, AddressLine)` |
| année de décision | `BusinessYear` |
| instant | `DateTimeImmutable` |

Avant `Property::register`, le use case appelle obligatoirement `GeographicPlaceCatalog::statusOf` lorsque l’adresse existe, puis exige `Usable`. `PropertyTypePolicy` et `PropertyRegistry::add` restent ses autorités internes. Aucun besoin n’a été supprimé ou déplacé depuis l’Authority initiale.
