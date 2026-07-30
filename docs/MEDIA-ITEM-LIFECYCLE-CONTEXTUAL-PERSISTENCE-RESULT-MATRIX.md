# Media Item Lifecycle Contextual Persistence Result Matrix

| Situation | Résultat |
|---|---|
| transition et contexte insérés atomiquement | `Applied` |
| transition et contexte strictement identiques déjà présents | `AlreadyApplied` |
| même transition/version, checksum contextuel différent | `ContextDivergence` |
| source absente, version obsolète ou saut de version | `VersionConflict` |
| état courant ou transition déjà persistée incompatible | `StateConflict` |
| transition refusée par les contraintes certifiées 031 | `TransitionRejected` |
| transition sans contexte correspondant | `Corrupted` |

L'inspection expose séparément `Found`, `Missing` et `Corrupted`. `Found` contient la transition exacte et le contexte V1 restauré après validation des deux checksums.
