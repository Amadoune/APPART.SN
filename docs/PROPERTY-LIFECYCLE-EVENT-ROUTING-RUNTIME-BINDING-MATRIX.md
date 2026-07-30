# Property Lifecycle Event Routing Runtime Binding Matrix

| Contrat | Implémentation | Cycle de vie |
|---|---|---|
| `PropertyLifecycleEventDestination` | `PostgreSqlPropertyLifecycleEventInbox` | singleton paresseux, alias unique |
| `PropertyLifecycleEventRouter` | `DurablePropertyLifecycleEventRouter` | singleton paresseux, alias unique |
| serializer 4.2E | `PropertyLifecycleEventSerializer` | singleton paresseux |

L'Inbox réutilise le PDO PostgreSQL Runtime existant. Runtime Health inspecte la destination et le routeur comme capacités obligatoires sans appeler `transfer` ou `route`.
