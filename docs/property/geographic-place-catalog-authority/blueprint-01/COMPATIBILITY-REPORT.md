# Compatibility Report

## Préservé

- F0 reste owner de la persistance `Place` et de son binding.
- F1 reste une lecture de sélection distincte.
- F4-A/F4 conservent la sélection et le replay owner-scoped.
- `GeographicPlaceCatalog`, `GeographicPlaceStatus`, `RegisterProperty` et `ChangeAddress` restent inchangés.
- Aucun Aggregate, snapshot, migration ou backfill n’est ajouté.
- Projection et Search restent aval et absents de la frontière.
- Les labels legacy ne deviennent pas des identités.

## Compatibilité temporelle

Les snapshots Authoring historiques restent lisibles. L’absence de PlaceId continue de relever de la complétude F4 ; un PlaceId présent est revalidé à la Promotion. Aucun état Geography n’est copié dans Authoring.

## Risque bloquant

Le mapping activation/fusion est compatible, mais l’adressabilité n’est portée par aucun owner observé. Une implémentation qui omet `NotAddressable` modifierait silencieusement la sémantique du contrat ; une implémentation qui choisit des types introduirait une règle. Les deux sont incompatibles avec le mandat.

## Changements de ce chantier

Documentation uniquement. Aucun PHP, Provider, binding, migration, test produit, staging, commit ou tag.
