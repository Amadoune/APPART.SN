# Place Lifecycle Replay Attempt Identity Matrix

## Identité complète

| Élément | Demandé | Historique | Comparateur autoritaire |
|---|---:|---:|---|
| source | contexte V1 | ligne persistée | checksum Persistance |
| intention | contexte V1 | ligne persistée | checksum et contrainte unique |
| action | transition candidate | colonne `action` | checksum Persistance |
| contexte V1 | contexte demandé | champs persistés | checksum Persistance |
| version résultante | version attendue + 1 | clé `(place_id, version)` | Persistance |

## Cas déterminants

| Action historique | Action demandée | Contexte | Verdict autorisé |
|---|---|---|---|
| `Merge` | `Merge` | identique | `AlreadyApplied` après confirmation persistante |
| `Disable` | `Merge` | identique | jamais `AlreadyApplied`; `ReplayConflict` |
| `Enable` | `Disable` | identique | jamais `AlreadyApplied`; `ReplayConflict` |
| identique | identique | divergent | `ContextDivergence` |

## Invariant

```text
Inspection::Found
≠ AlreadyApplied

checksum persistant exact
= AlreadyApplied
```
