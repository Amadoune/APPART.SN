# Reservation Lifecycle Orchestration Result Matrix

| Situation | Statut | Transition | Diagnostic workflow |
|---|---|---|---|
| append appliqué | `Applied` | exacte | aucun |
| réservation absente | `Missing` | aucune | aucun |
| version lue ou écrite incompatible | `VersionConflict` | aucune | aucun |
| état refusé par le store | `StateConflict` | aucune | aucun |
| append idempotent | `AlreadyApplied` | exacte | aucun |
| décision workflow refusée | `Denied` | aucune | exact |
| lecture corrompue, transition rejetée ou exception | `PersistenceCorrupted` | aucune | aucun |

Ces sept statuts constituent l'ensemble fermé. Aucun résultat ne transporte une exception, du SQL ou un détail PostgreSQL.
