# Adapter Boundary

## Ownership

Le futur adaptateur appartient à `RealEstateCatalog/Infrastructure/Geography`. Il implémente un port RealEstateCatalog et consomme une autorité Geography. Geography ne dépend jamais de RealEstateCatalog.

## Responsabilité autorisée

- convertir `GeographicPlaceId` en `PlaceId` sans changer la valeur d’identité ;
- appeler `PlaceRegistry::find` ;
- observer les faits de l’Aggregate ;
- appliquer un mapping déjà autorisé ;
- retourner un `GeographicPlaceStatus`.

## Responsabilités interdites

- SQL direct ;
- mutation via `PlaceRegistry::add` ou `save` ;
- activation, désactivation ou merge ;
- résolution automatique de `mergedInto` ;
- appel de F1/F4-A HTTP ;
- Search ou Projection ;
- création d’un PlaceId depuis un label ;
- invention de la règle d’adressabilité.

L’injection de `PlaceRegistry` expose techniquement des méthodes de mutation, mais l’adaptateur reste read-only par construction et tests d’architecture. Une future aliasation du port étroit `PlaceLookup` serait préférable, mais n’est pas requise par F0 aujourd’hui et ne doit pas être inventée dans ce blueprint.

## Séparation F1

`GeographySelectionReaderV1` découvre des choix owner-facing paginés. `GeographicPlaceCatalog` revalide une identité connue au moment d’une décision Domain. Aucun des deux ne remplace l’autre.
