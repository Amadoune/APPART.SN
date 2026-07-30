# Reservation Lifecycle Persistence Result Matrix

## Lecture

| Situation | Résultat |
|---|---|
| dernière ligne valide | `Found` avec snapshot |
| aucune ligne | `Missing` |
| état, version ou checksum invalide | `Corrupted` |

## Écriture

| Situation | Résultat |
|---|---|
| nouvelle écriture valide | `Applied` |
| même version et même checksum | `AlreadyApplied` |
| version manquante ou discontinue | `RejectedVersion` |
| état source ou écriture divergente | `StateConflict` |
| contrainte des onze transitions refusée | `TransitionRejected` |

Les résultats sont fermés. Aucune exception technique n'est convertie en décision du workflow.
