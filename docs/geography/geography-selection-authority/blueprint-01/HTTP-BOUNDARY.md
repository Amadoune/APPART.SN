# HTTP Boundary

Surface future documentaire :

`GET /api/geography/v1/selections`

Query structurée : `type`, `parentPlaceId` conditionnel, `cursor` optionnel, `limit`. Aucun paramètre texte libre, slug, city ou neighborhood.

| Résultat | HTTP futur |
|---|---:|
| Available / Empty | 200 |
| Missing | 404 |
| Corrupted / DependencyUnavailable | 503 |
| Requête ou cursor invalide | 422 avant reader |

La réponse expose status, items et nextCursor. Validation : enum exact, UUID PlaceId, compatibilité parent/type, limit borné, champs inconnus refusés. Aucun contrôleur ou route n'est créé par ce Blueprint.
