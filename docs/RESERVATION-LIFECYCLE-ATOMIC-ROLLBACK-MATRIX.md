# Reservation Lifecycle Atomic Rollback Matrix

| Issue | Journal | Outbox | Résultat |
|---|---|---|---|
| transition appliquée et Outbox appliquée | commit | commit | `Applied` |
| transition déjà appliquée et message identique | inchangé | idempotent | `AlreadyApplied` |
| réservation absente | inchangé | aucune écriture | `Missing` |
| version ou état en conflit | inchangé | aucune écriture | conflit original |
| workflow refusé | inchangé | aucune écriture | `Denied` |
| persistance corrompue | rollback | rollback | `PersistenceCorrupted` |
| Outbox refusée ou en échec | rollback | rollback | `PersistenceCorrupted` |
| deux requêtes identiques concurrentes | une transition | un message | `Applied` + `AlreadyApplied` |
