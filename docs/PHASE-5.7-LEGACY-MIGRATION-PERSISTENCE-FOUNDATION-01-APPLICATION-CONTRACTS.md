# Application Contracts — Legacy Migration Owner Source

## Port

`LegacyMigrationOwnerSource` expose, pour chacun des cinq streams, une opération `append*` et une lecture temporelle `read*`.

## États de révision persistables

| Stream | États |
|---|---|
| Inventory | Available |
| Wave | Ready, Blocked, Completed |
| Reconciliation | Matched, Divergent, Pending |
| Quarantine | Empty, ContainsItems |
| Cutover | Ready, Blocked, Completed |

Chaque Revision State porte une clé sujet typée, une révision strictement positive, un statut fermé, `effectiveAt` et `recordedAt` en UTC canonique.

## Résultats

- Read Results : `Found`, `Missing`, `Corrupted`, `DependencyUnavailable` ;
- Write Results : `Applied`, `AlreadyApplied`, `DivergentRevision`, `VersionConflict`, `Corrupted`, `DependencyUnavailable`.

Les contrats Application ne dépendent d'aucun composant Infrastructure.
