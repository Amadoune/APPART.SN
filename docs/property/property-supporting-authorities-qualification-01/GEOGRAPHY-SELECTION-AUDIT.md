# Geography Selection Audit

## Surfaces observées

| Surface | Capacité | Limite pour Authoring |
|---|---|---|
| `PlaceRegistry extends PlaceLookup` | `find(PlaceId): ?Place`, add/save | Nécessite déjà l'identité ; aucun lookup paginé ou par critères |
| Geography use cases | créer, renommer, activer, désactiver, fusionner | Surfaces de mutation Domain, pas un catalogue de sélection owner-facing |
| `PlaceHierarchy` | vérifie la hiérarchie d'Aggregates connus | Ne découvre pas une identité depuis une saisie |
| Place Lifecycle | états et transitions par PlaceId | Nécessite déjà l'identité |
| `GeographicPlaceCatalog::statusOf` | valide un `GeographicPlaceId` connu pour RealEstateCatalog | Validation, pas acquisition |
| `PublicGeographyDecisionReader::read(string $placeId)` | retourne Found/Missing/Corrupted pour un ID connu | Read model Projection, lookup par clé uniquement, pas owner de sélection Authoring |
| Search public | recherche de Listings | Search n'est pas autorité Geography |

Aucune route, Request, Provider ou binding audité ne compose une liste/recherche read-only Geography pour Property Authoring. Les libellés `city` et `neighborhood` ne peuvent pas être convertis en PlaceId.
