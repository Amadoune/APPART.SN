# Listing Publication Failure and Rollback Matrix

| Point d'échec | Transition durable | Message Outbox durable | Résultat observable |
|---|---:|---:|---|
| décision refusée | non | non | `Denied` avec diagnostic exact |
| version attendue obsolète | non | non | `ConcurrencyConflict` |
| persistance métier refusée | non | non | `PersistenceFailure` |
| construction événement/message invalide | rollback | non | `PersistenceFailure / InfrastructureFailure` |
| écriture Outbox divergente ou refusée | rollback | rollback | `PersistenceFailure / InfrastructureFailure` |
| exception PostgreSQL | rollback | rollback | `PersistenceFailure / InfrastructureFailure` |
| rejeu strictement identique | déjà présente | déjà présent, sans doublon | `AlreadyApplied` |

Il n'existe aucun succès partiel : une transition et ses messages sont tous validés ou tous annulés.
