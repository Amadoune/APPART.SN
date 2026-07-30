# Property Lifecycle Atomic Transaction Specification

La transaction encadre intégralement : lecture du journal, décision 4.2D, append du journal, création événementielle, construction Delivery et insertion Outbox.

`PostgreSqlAggregateOutboxTransaction` ouvre la transaction seulement si aucune transaction n'existe, interdit explicitement l'imbrication, commit après toutes les écritures et rollback sur toute exception.

| Situation | Journal | Outbox | Résultat |
|---|---|---|---|
| transition et append Outbox réussis | commit | commit | résultat 4.2D |
| décision sans émission | inchangé | inchangé | résultat 4.2D |
| construction événement/message échouée | rollback | absent | `PersistenceFailure` |
| append Outbox refusé ou échoué | rollback | rollback | `PersistenceFailure` |

Aucune compensation et aucune écriture après commit ne sont autorisées.
