# Selection Item

DTO minimal immutable :

| Champ | Type | Source |
|---|---|---|
| `placeId` | chaîne canonique UUID de PlaceId | Aggregate Geography |
| `label` | nom officiel certifié | `Place::officialName` |
| `type` | valeur fermée PlaceType | `Place::type` |
| `parentPlaceId` | PlaceId nullable | `AdministrativeDivision` |

Tous les items retournés sont sélectionnables ; aucun booléen redondant n'est nécessaire. Code, coordonnées, aliases, country internals, Aggregate version et Aggregate complet ne sont pas exposés en V1.

Le label est une représentation. Seul `placeId` est transporté comme identité par Property Authoring.
