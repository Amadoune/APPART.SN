# Geography Selection Authority — Blueprint 01

## Décision

Geography Application possède un reader unique, read-only et framework-agnostic : `GeographySelectionReaderV1`.

Il expose uniquement des Places existantes, enabled et non fusionnées, sous forme de DTO minimaux. Property Authoring sélectionne et conserve le `PlaceId`; RealEstateCatalog revalide ultérieurement l'identité avec `GeographicPlaceCatalog::statusOf`.

Le reader ne recherche pas par texte libre, ne fabrique aucune identité et ne réutilise pas la source aval de Projection.

## Chaîne V1

`Geography source → GeographySelectionReaderV1 → SelectionItem(placeId) → choix humain → PropertyAuthoringState.geographicPlaceId → RegisterProperty revalidation`.

Tous les choix requis pour l'implémentation documentaire sont clos. **GO PROPOSÉ.**
