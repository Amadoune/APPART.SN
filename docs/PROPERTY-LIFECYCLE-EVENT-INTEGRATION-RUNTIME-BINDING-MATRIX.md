# Property Lifecycle Event Integration Runtime Binding Matrix

| Contrat/composant | Implémentation | Cycle |
|---|---|---|
| `PropertyLifecycleAtomicTransaction` | `PostgreSqlAggregateOutboxTransaction` existante | alias singleton |
| `PropertyLifecycleEventOrchestrator` | `AtomicPropertyLifecycleEventOrchestrator` | alias singleton |
| catalogue événementiel | `PropertyLifecycleEventCatalog` | singleton |
| fabrique Delivery | composant 4.2H existant | singleton |
| Outbox writer | adaptateur PostgreSQL existant | binding existant |

Tous les bindings sont paresseux. Runtime Health inspecte uniquement la présence, la compatibilité et la constructibilité du port événementiel ; aucune transition n'est appelée au bootstrap.
