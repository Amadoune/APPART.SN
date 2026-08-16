# Test Matrix

## Unit futurs

- Published éligible → rang 0 et facettes vides ;
- bornes 0 et 10000 acceptées par le Value Object, hors bornes rejeté ;
- direction normative documentée : valeur supérieure prioritaire ;
- mêmes inputs → même sortie ;
- facettes v1 vides et ordre canonique stable ;
- Visible/Hidden/Removed conservent la sortie de policy sans confondre visibilité.

## Architecture futurs

- owner SearchDiscovery ;
- aucune dépendance Projection ou UI ;
- aucune clock, random ou donnée de trafic ;
- aucun fait commercial implicite.

## Intégration future

Faits Listing versionnés → policy v1 → `SearchRank(0)` + `[]` reproductibles ; visibilité évaluée séparément.
