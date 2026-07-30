# Account Status — Atomic Event Integration

## Chemin atomique

```text
PostgreSqlAggregateOutboxTransaction
→ AccountStatusOrchestrator 4.9E
→ lifecycle persistence 4.9C
→ AccountStatusEvent V1 4.9F
→ transport message 4.9G
→ Router 4.9H
→ Routed Delivery V1 4.9J
→ generic appendRouted
→ commit unique
```

Toute exception ou tout rejet Outbox produit un rollback unique :

```text
lifecycle transition
+ routed Outbox record
→ tous validés
ou
→ tous annulés
```

## Matrice fermée

| Résultat 4.9E | Écriture Outbox | Résultat 4.9K |
|---|---|---|
| `Applied` | `Applied` ou `AlreadyApplied` | résultat 4.9E conservé |
| `Applied` | autre résultat | rollback, `PersistenceCorrupted` |
| autre résultat fermé | aucune | résultat 4.9E conservé |
| routage rejeté | aucune validation | rollback, `PersistenceCorrupted` |
| exception durable | aucune validation | rollback, `PersistenceCorrupted` |

## Propriétés

- une seule transaction PDO externe ;
- la transaction 4.9E participe sans commit intermédiaire ;
- le Router 4.9H reste seul propriétaire de la destination ;
- la routing proof est produite avant l'écriture Outbox ;
- `eventId`, message transport, idempotency key et message générique restent
  distincts ;
- aucun publish, dispatch, Worker supplémentaire, Consumer spécialisé ou HTTP.
