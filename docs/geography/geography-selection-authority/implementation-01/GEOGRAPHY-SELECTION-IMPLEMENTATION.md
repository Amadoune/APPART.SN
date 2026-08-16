# F1 — Geography Selection Foundation Implementation 01 — Reopening

## Qualification historique

L'ouverture initiale s'est conclue par un **NO GO fail-fast** : Geography ne disposait alors d'aucune persistance autoritative de `Place`. Cette conclusion reste une preuve historique valide ; aucune lecture de Projection, fake ou catalogue parallèle n'avait été acceptée.

## Cause de réouverture

F0 Geography Place Persistence Authority est désormais certifiée. La table autoritative `geography.places`, son repository et son index de sélection existent. F1 est donc rouverte sans nouvelle migration.

## Capacité matérialisée

`GeographySelectionReaderV1` est une frontière applicative read-only dédiée. Elle n'étend pas `PlaceRegistry` et ne dépend ni de Property Authoring, ni de Search, ni de Public Projection.

Entrée fermée :

- `type` : valeur exacte de `PlaceType` ;
- `parentPlaceId` : absent uniquement pour `Country`, obligatoire sinon ;
- `cursor` : curseur opaque versionné, lié à `type + parentPlaceId` et protégé par checksum ;
- `limit` : entier compris entre 1 et 100.

Sortie fermée : `Available`, `Empty`, `Missing`, `Corrupted`, `DependencyUnavailable`.

Chaque item expose exactement `placeId`, `label`, `type`, `parentPlaceId`.

## Lecture autoritative

L'adapter PostgreSQL sélectionne uniquement les lignes `enabled = true` et `merged_into_place_id IS NULL`, avec égalité stricte sur `type` et `parent_place_id`. La pagination keyset est ordonnée par `lower(official_name), id`. Le type du parent est relu dans la même source afin de fermer une hiérarchie incompatible.

## Frontières préservées

- aucune route ou surface HTTP ;
- aucune modification de `PlaceRegistry` ;
- aucun write model ni SQL applicatif ;
- aucune migration F1 ;
- aucune ouverture de F2, F3, F4 ou RC2 Iteration 11.
