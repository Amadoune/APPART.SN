# Property Lifecycle Failure & Rollback Matrix

| Défaillance | Résultat observable | Journal durable | Message durable |
|---|---|---:|---:|
| lecture/persistance 4.2D | résultat exact 4.2D | non ajouté | non |
| transition refusée | `Denied` exact | non ajouté | non |
| conflit de version/état | `ConcurrencyConflict` exact | non ajouté | non |
| catalogue ou payload | `PersistenceFailure / InfrastructureFailure` | rollback | non |
| fabrique Delivery | `PersistenceFailure / InfrastructureFailure` | rollback | non |
| append Outbox refusé | `PersistenceFailure / InfrastructureFailure` | rollback | non |
| exception Outbox | `PersistenceFailure / InfrastructureFailure` | rollback | rollback |

Il n'existe aucun succès partiel ni diagnostic technique exposé comme décision métier.
