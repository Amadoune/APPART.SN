# Media Item Lifecycle Orchestration Result Matrix

| Situation | Résultat |
|---|---|
| transition et contexte persistés | `Applied` |
| snapshot exact, action et contexte identiques | `AlreadyApplied` |
| source Lifecycle absente | `Missing` |
| version courante incompatible ou inspection de rejeu absente | `VersionConflict` |
| workflow refusé | `Denied` avec diagnostic inchangé |
| action inspectée incompatible ou conflit du store | `StateConflict` |
| action identique mais contexte exact divergent | `ContextDivergence` |
| lecture, inspection ou append corrompu | `PersistenceCorrupted` |

Les huit résultats sont fermés. Aucune exception technique n'est convertie en nouvelle décision métier.
