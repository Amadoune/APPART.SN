# Compatibility Report

- F1–F4 restent fermées et inchangées ;
- anciens snapshots Authoring restent lisibles et incomplets ;
- aucune migration 100, aucun backfill et aucune mutation de 099 ;
- F2 reste pure et ne persiste rien ;
- F3 ne lit aucune clock ;
- Geography conserve l’ownership de Place et de son lifecycle ;
- RealEstateCatalog conserve `GeographicPlaceCatalog`, `PropertyTypePolicy`, `RegisterProperty` et `PropertyRegistry` ;
- Projection et Search restent strictement aval ;
- aucun Aggregate Property n’est construit par Authoring ou F5.

Le changement requis pour reprendre n’est pas documentaire : une implémentation productive owner-correcte de `GeographicPlaceCatalog` doit être qualifiée puis matérialisée hors F5.
