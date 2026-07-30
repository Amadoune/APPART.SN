# Media Item Lifecycle Routing Runtime Binding Specification

| Contrat ou composant | Résolution |
|---|---|
| `MediaItemLifecycleTransportSerializer` | singleton |
| `PostgreSqlMediaItemLifecycleInboxRepository` | singleton |
| `MediaItemLifecycleInboxStore` | alias exact du repository |
| `DurableMediaItemLifecycleEventRouter` | singleton |
| `MediaItemLifecycleEventRouter` | alias exact du routeur durable |
| `MediaItemLifecycleDeliveryConsumptionPolicy` | singleton |

Le repository réutilise exclusivement le PDO Runtime existant. Tous les bindings sont paresseux, uniques et sans Fake, Null Object ou fallback.
