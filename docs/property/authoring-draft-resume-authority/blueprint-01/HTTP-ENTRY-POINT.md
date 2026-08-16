# HTTP Entry Point

Surface retenue :

`GET /authoring/workspace/{listingId}`

Cette route exprime explicitement la ressource sélectionnée et évite de surcharger le workspace « nouveau parcours ». Elle exige le middleware IAM existant. Le contrôleur appelle uniquement `AuthoringDraftResumeReaderV1` et rend la même expérience avec un bootstrap read-only.

Mappings :

| Résultat | HTTP |
|---|---:|
| Available | 200 |
| session absente/expirée | 401 |
| NotFoundOrForbidden | 404 |
| Incomplete / StateConflict | 409 |
| UUID mal formé | 422 |
| Corrupted | 500 |
| DependencyUnavailable | 503 |

Le workspace sans `listingId` reste l’entrée du nouveau parcours et de la sélection Portfolio. L’URL ne transporte aucune preuve d’ownership.
