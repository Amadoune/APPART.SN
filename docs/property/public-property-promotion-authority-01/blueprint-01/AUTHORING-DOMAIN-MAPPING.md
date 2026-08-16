# Authoring → Domain Mapping

| Invariant / donnée | Source autoritative V1 | Disponible aujourd'hui | Transformation / validation | Si absent |
|---|---|---:|---|---|
| Property ID | `PropertyAuthoringState.propertyId` | Oui | `PropertyId::fromString` | `AuthoringMissing` |
| Owner | session IAM + `ownerAccountId` Authoring | Oui | égalité stricte ; non persisté dans l'Aggregate actuel | `OwnershipMismatch` |
| Référence | future autorité RealEstateCatalog de référence, ou champ Authoring certifié | Non | `PropertyReference`; unicité Registry | `IncompleteAuthoring` |
| Property Type | `PropertyAuthoringState.propertyType` | Oui | mapping fermé vers `PropertyType` | `IncompleteAuthoring` ou `DomainRejected` |
| Surface | future extension Authoring | Non | `SurfaceArea`; policy selon type | `IncompleteAuthoring` lorsque requise |
| Rooms | future extension Authoring | Non | `RoomCount`; policy selon type | `IncompleteAuthoring` |
| Bathrooms | future extension Authoring | Non | `BathroomCount`; cohérence avec rooms | `IncompleteAuthoring` |
| Construction year | future extension Authoring | Non | optionnel Domain, `ConstructionYear` si fourni | omission permise seulement comme `null` certifié |
| Business year | autorité temporelle applicative au moment de commande | Oui conceptuellement, non contractée | année de `occurredAt`, passée explicitement au Domain | `DependencyUnavailable` tant que non qualifiée |
| City | `PropertyAuthoringState.city` | Oui | donnée préparatoire, ne suffit pas à `Address` | `IncompleteAuthoring` |
| Neighborhood | `PropertyAuthoringState.neighborhood` | Oui | donnée préparatoire, ne suffit pas à `Address` | `IncompleteAuthoring` |
| Structured address | future extension Authoring | Non | construction `Address` | `IncompleteAuthoring` lorsque requise |
| Geographic place | future sélection d'un ID issu de Geography/`GeographicPlaceCatalog` | Non | statut `Usable` vérifié par `RegisterProperty` | `IncompleteAuthoring`/`DomainRejected` |
| Aggregate version | RealEstateCatalog Domain | Oui | initialisée à 0, jamais fournie par client | `DomainRejected` |
| Timestamps | `occurredAt` stable | Oui | DateTimeImmutable ; contrôlé au replay | `DivergentCommand` |

Ville/quartier libres ne peuvent pas être convertis silencieusement en `GeographicPlaceId` ou `AddressLine`.
