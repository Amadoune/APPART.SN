# HTTP Boundary

## Ownership et accès

La route retenue est `GET /api/authoring/geography/selections`.

- middleware : `RequireIdentityAccessSession` ;
- throttling : groupe authoring existant `throttle:property-listing-authoring` ;
- CSRF : non requis pour ce GET sans mutation ;
- aucune capacité IAM supplémentaire : les Places sont une référence commune ;
- aucun `ownerAccountId` en query ;
- aucune donnée owner n’est transmise à Geography.

L’authentification protège l’expérience Authoring. Elle ne crée aucun ownership individuel sur Geography.

## Query contract

| Paramètre | Règle |
|---|---|
| `type` | requis, valeur exacte du catalogue `PlaceType` |
| `parentPlaceId` | absent uniquement pour `country`, UUID requis autrement |
| `cursor` | optionnel, opaque, chaîne bornée à 2048 caractères |
| `limit` | optionnel, défaut 50, entier 1..100 |

Tout paramètre inconnu produit 422. Aucun texte libre, slug, city, neighborhood ou owner n’est accepté.

Le Controller dépend uniquement de `GeographySelectionReaderV1`, construit `GeographySelectionQuery` et mappe son résultat. Aucun SQL, repository, Projection ou Search.

## Unicité

Aucune seconde route `/api/geography/v1/selections` n’est ouverte. Si un futur produit public requiert la même lecture, une décision séparée devra qualifier son exposition, sans dupliquer le Runtime.
