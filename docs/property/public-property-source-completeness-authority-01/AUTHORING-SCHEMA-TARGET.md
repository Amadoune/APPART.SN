# Property Authoring Schema Target

Modèle documentaire seulement ; aucune migration n'est autorisée.

| Champ | Type | Nullable | Owner/source | Validation | Version/confidentialité | Destination |
|---|---|---:|---|---|---|---|
| propertyId | UUID/string | Non | Authoring identity | PropertyId | snapshot / interne | PropertyId |
| ownerAccountId | AccountId | Non | session IAM | égalité session | snapshot / confidentiel | scope seulement |
| propertyReference | string | Non | propriétaire | PropertyReference + unicité Registry | snapshot / interne avant publication | reference |
| propertyType | enum fermé | Non à complétude | propriétaire | PropertyType | snapshot / public après projection | type |
| surfaceSquareMeters | int | Selon type | propriétaire | SurfaceArea + policy | snapshot / public après projection | surface |
| rooms | int | Non | propriétaire | RoomCount + policy | snapshot / public après projection | rooms |
| bathrooms | int | Non | propriétaire | BathroomCount + policy | snapshot / public si projeté | bathrooms |
| constructionYear | int | Oui | propriétaire | ConstructionYear + policy | snapshot / public si projeté | constructionYear |
| city | string | Oui | propriétaire/libellé UX | règles Authoring actuelles | snapshot / public selon projection | aucun invariant Address |
| neighborhood | string | Oui | propriétaire/libellé UX | règles Authoring actuelles | snapshot / public selon projection | aucun invariant Address |
| geographicPlaceId | PlaceId | Selon type | sélection Geography | existence/statut Usable | snapshot / interne puis public dérivé | Address.placeId |
| addressLine | string | Selon type | propriétaire | AddressLine | snapshot / donnée sensible avant projection | Address.line |
| addressId | AddressId | Selon type | future autorité d'identité | AddressId | snapshot ou émission promotion / interne | Address.id |
| version | int | Non | Authoring Store | optimistic locking | interne | expectedAuthoringVersion |
| intentId/checksum | identités | Non | Authoring | replay | interne | orchestration seulement |

`businessYear` n'est pas un champ owner-authored cible ; il vient d'une autorité applicative séparée.
