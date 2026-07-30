# Property Lifecycle Outbox Production Matrix

| Résultat 4.2D | Catalogue | Événement | Append Outbox |
|---|---:|---:|---:|
| `Applied` | oui | exactement 1 | oui |
| `AlreadyApplied` | oui | exactement 1, déterministe | oui/idempotent |
| `Denied` | non | 0 | non |
| `ConcurrencyConflict` | non | 0 | non |
| `PersistenceFailure` | non | 0 | non |

Le message utilise `RealEstateCatalog`, `Property`, la version persistée, l'index 1 et les instants fournis. L'`eventId` reste dans `canonicalEvent`; le `message_id` est dérivé par la convention Delivery existante.
